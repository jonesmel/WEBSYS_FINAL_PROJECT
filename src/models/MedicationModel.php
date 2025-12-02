<?php
require_once __DIR__ . '/../../config/db.php';

class MedicationModel {
  public static function create($data) {
    $pdo = getDB();
    $sql = "INSERT INTO medications (patient_id, drugs, start_date, end_date, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $data['patient_id'],
      $data['drugs'],
      $data['start_date'],
      $data['end_date'],
      $data['notes'],
      $data['created_by']
    ]);
    return $pdo->lastInsertId();
  }

  public static function getAll() {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT m.*, p.patient_code, p.name,
               m.compliance_status, m.compliance_date, m.compliance_notes, m.compliance_marked_by,
               m.compliance_deadline, m.scheduled_for_date
        FROM medications m
        LEFT JOIN patients p ON p.patient_id = m.patient_id
        ORDER BY m.created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public static function getByBarangay($barangay) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT m.*, p.patient_code, p.name,
               m.compliance_status, m.compliance_date, m.compliance_notes, m.compliance_marked_by,
               m.compliance_deadline, m.scheduled_for_date
        FROM medications m
        LEFT JOIN patients p ON p.patient_id = m.patient_id
        WHERE p.barangay = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$barangay]);
    return $stmt->fetchAll();
  }

  public static function getByPatient($patientId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE patient_id = ?");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
  }

  public static function getById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE medication_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function delete($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM medications WHERE medication_id=?");
    return $stmt->execute([$id]);
  }

  public static function update($id, $data) {
      $pdo = getDB();
      $stmt = $pdo->prepare("
          UPDATE medications
          SET drugs=?, start_date=?, end_date=?, notes=?,
              scheduled_for_date=?, compliance_deadline=?
          WHERE medication_id=?
      ");
      return $stmt->execute([
          $data['drugs'], $data['start_date'], $data['end_date'], $data['notes'],
          $data['scheduled_for_date'], $data['compliance_deadline'], $id
      ]);
  }

  public static function updateCompliance($id, $data) {
      $pdo = getDB();
      $stmt = $pdo->prepare("
          UPDATE medications
          SET compliance_status=?, compliance_date=?, compliance_marked_by=?, compliance_notes=?
          WHERE medication_id=?
      ");
      return $stmt->execute([
          $data['compliance_status'],
          $data['compliance_date'] ?: date('Y-m-d'),
          $data['compliance_marked_by'],
          $data['compliance_notes'] ?? null,
          $id
      ]);
  }

  public static function getPendingCompliance($barangay = null) {
      $pdo = getDB();
      if ($barangay) {
          $stmt = $pdo->prepare("
              SELECT m.*, p.patient_code, p.name, p.barangay
              FROM medications m
              LEFT JOIN patients p ON p.patient_id = m.patient_id
              WHERE m.compliance_status = 'pending'
              AND m.compliance_deadline <= CURDATE()
              AND p.barangay = ?
              ORDER BY m.compliance_deadline ASC
          ");
          $stmt->execute([$barangay]);
      } else {
          $stmt = $pdo->query("
              SELECT m.*, p.patient_code, p.name, p.barangay
              FROM medications m
              LEFT JOIN patients p ON p.patient_id = m.patient_id
              WHERE m.compliance_status = 'pending'
              AND m.compliance_deadline <= CURDATE()
              ORDER BY m.compliance_deadline ASC
          ");
      }
      return $stmt->fetchAll();
  }

  public static function autoMarkMissedMedications() {
      $pdo = getDB();

      // Auto-mark medications as missed if they've been overdue for 3+ days
      // This prevents infinite pending status and ensures follow-up
      $overdueGracePeriod = 3; // Days after deadline to auto-mark as missed

      $stmt = $pdo->prepare("
          SELECT m.*
          FROM medications m
          WHERE m.compliance_status = 'pending'
          AND m.compliance_deadline < DATE_SUB(CURDATE(), INTERVAL ? DAY)
      ");
      $stmt->execute([$overdueGracePeriod]);
      $overdueMeds = $stmt->fetchAll();

      foreach ($overdueMeds as $med) {
          // Auto-mark as missed
          self::updateCompliance($med['medication_id'], [
              'compliance_status' => 'missed',
              'compliance_date' => date('Y-m-d'),
              'compliance_marked_by' => 1, // System admin user (ID 1)
              'compliance_notes' => 'Auto-marked as missed: Medication overdue for ' . $overdueGracePeriod . ' days without verification.'
          ]);

          // Get health workers for the patient's barangay to notify them
          $pdo2 = getDB();
          $workerStmt = $pdo2->prepare("
              SELECT user_id FROM users
              WHERE role = 'health_worker' AND barangay_assigned = (
                  SELECT barangay FROM patients WHERE patient_id = ?
              )
          ");
          $workerStmt->execute([$med['patient_id']]);
          $healthWorkers = $workerStmt->fetchAll();

          // DEBUG: Temporary logging for health workers
          LogModel::insertLog(
              null,
              'debug_health_workers',
              'medications',
              $med['medication_id'],
              null,
              json_encode(['patient_id' => $med['patient_id'], 'health_workers_found' => count($healthWorkers)]),
              'SYSTEM',
              'auto-missed-detection'
          );

          // Create staff follow-up notification for admins
          NotificationModel::create([
              'user_id' => 1, // Super admin user ID
              'patient_id' => $med['patient_id'], // Patient reference
              'type' => 'staff_follow_up',
              'title' => 'Auto-Detected Missed Medication - Urgent Follow-up Required',
              'message' => "Medication '{$med['drugs']}' (ID: {$med['medication_id']}) has been auto-marked as missed after being overdue for {$overdueGracePeriod} days. Immediate staff intervention needed.",
              'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $med['patient_id']
          ]);

          // Notify health workers individually via email (but use separate type to avoid duplicate follow-up entries)
          foreach ($healthWorkers as $worker) {
              NotificationModel::create([
                  'user_id' => $worker['user_id'],
                  'type' => 'health_worker_alert', // Separate type to avoid duplicate follow-up entries
                  'title' => 'Medication Follow-up Required in Your Area',
                  'message' => "Patient medication '{$med['drugs']}' (ID: {$med['medication_id']}) has been auto-detected as missed. Please follow up immediately.",
                  'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $med['patient_id']
              ]);
          }

          // Create notification for the patient about missed medication
          NotificationModel::create([
              'patient_id' => $med['patient_id'],
              'type' => 'missed_medication_patient_notification',
              'title' => 'Important: Medication Follow-up Required',
              'message' => "Your medication '{$med['drugs']}' has been identified as missed and requires follow-up. Please contact your healthcare provider.",
              'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/notifications"
          ]);
      }

      return count($overdueMeds); // Return count of medications auto-marked
  }
}

<?php
require_once __DIR__ . '/../../config/db.php';

class ContactModel {
  public static function create($data) {
    $pdo = getDB();
    $code = $data['contact_code'] ?? self::generateContactCode();

    $sql = "INSERT INTO contacts (patient_id, contact_code, age, sex, relationship, contact_number, barangay, screening_result, status, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?)";

    $pdo->prepare($sql)->execute([
      $data['patient_id'],
      $code,
      $data['age'],
      $data['sex'] ?? 'Unknown',
      $data['relationship'],
      $data['contact_number'],
      $data['barangay'],
      $data['screening_result'] ?? 'pending',
      $data['status'] ?? 'monitoring',
      $data['created_by']
    ]);

    return $pdo->lastInsertId();
  }

  public static function generateContactCode() {
    $pdo = getDB();
    do {
      $code = 'C-'.date('Y').'-'.str_pad(random_int(1,99999),5,'0',STR_PAD_LEFT);
      $chk = $pdo->prepare('SELECT 1 FROM contacts WHERE contact_code=?');
      $chk->execute([$code]);
    } while ($chk->fetch());
    return $code;
  }

  public static function getById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM contacts WHERE contact_id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function getByPatient($patientId) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM contacts WHERE patient_id = ?");
      $stmt->execute([$patientId]);
      return $stmt->fetchAll();
  }

  public static function archive($id, $newPatientId = null) {
      $pdo = getDB();
      $stmt = $pdo->prepare("
          UPDATE contacts 
          SET 
              is_archived = 1,
              status = 'converted_patient',
              converted_patient_id = ?
          WHERE contact_id = ?
      ");
      return $stmt->execute([$newPatientId, $id]);
  }

  public static function getAll() {
      $pdo = getDB();
      $sql = "
          SELECT c.*, p.patient_code, p.barangay AS patient_barangay
          FROM contacts c
          LEFT JOIN patients p ON p.patient_id = c.patient_id
          WHERE c.is_archived = 0
          ORDER BY c.created_at DESC
      ";
      return $pdo->query($sql)->fetchAll();
  }

  public static function getAllFiltered($q = '', $barangay = '') {
      $pdo = getDB();
      $sql = "
          SELECT c.*, p.patient_code, p.barangay AS patient_barangay
          FROM contacts c
          LEFT JOIN patients p ON p.patient_id = c.patient_id
          WHERE c.is_archived = 0
      ";
      $params = [];
      if (!empty($q)) {
          $sql .= " AND (c.contact_code LIKE ? OR p.patient_code LIKE ? OR c.relationship LIKE ?)";
          $like = '%' . $q . '%';
          $params[] = $like; $params[] = $like; $params[] = $like;
      }
      if (!empty($barangay)) {
          $sql .= " AND c.barangay = ?";
          $params[] = $barangay;
      }
      $sql .= " ORDER BY c.created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }

  public static function getByBarangay($barangay) {
      $pdo = getDB();
      $stmt = $pdo->prepare("
          SELECT c.*, p.patient_code
          FROM contacts c
          JOIN patients p ON p.patient_id = c.patient_id
          WHERE p.barangay = ? AND c.is_archived = 0
          ORDER BY c.created_at DESC
      ");
      $stmt->execute([$barangay]);
      return $stmt->fetchAll();
  }

  public static function getByBarangayFiltered($barangay, $q = '') {
      $pdo = getDB();
      $sql = "
          SELECT c.*, p.patient_code
          FROM contacts c
          JOIN patients p ON p.patient_id = c.patient_id
          WHERE p.barangay = ? AND c.is_archived = 0
      ";
      $params = [$barangay];
      if (!empty($q)) {
          $sql .= " AND (c.contact_code LIKE ? OR p.patient_code LIKE ? OR c.relationship LIKE ?)";
          $like = '%' . $q . '%';
          $params[] = $like; $params[] = $like; $params[] = $like;
      }
      $sql .= " ORDER BY c.created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }
}
?>
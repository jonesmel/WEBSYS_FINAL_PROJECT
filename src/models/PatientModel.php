<?php
require_once __DIR__ . '/../../config/db.php';

class PatientModel {
  public static function generatePatientCode() {
      $pdo = getDB();

      $year = date("Y");
      $prefix = "PAT-$year-";

      // Get highest numbered code for this year (not just last inserted)
      $stmt = $pdo->prepare("
          SELECT patient_code
          FROM patients
          WHERE patient_code LIKE ?
          ORDER BY CAST(SUBSTRING_INDEX(patient_code, '-', -1) AS UNSIGNED) DESC
          LIMIT 1
      ");
      $stmt->execute([$prefix . "%"]);
      $last = $stmt->fetchColumn();

      if ($last) {
          $lastNum = (int)substr($last, -5);
          $nextNum = $lastNum + 1;
      } else {
          $nextNum = 1;
      }

      return $prefix . str_pad($nextNum, 5, "0", STR_PAD_LEFT);
  }

  public static function generateTbCaseNumber() {
      $pdo = getDB();

      $year = date("Y");
      $prefix = "TBCASE-$year-";

      $stmt = $pdo->prepare("
          SELECT tb_case_number
          FROM patients
          WHERE tb_case_number LIKE ?
          ORDER BY CAST(SUBSTRING_INDEX(tb_case_number, '-', -1) AS UNSIGNED) DESC
          LIMIT 1
      ");
      $stmt->execute([$prefix . "%"]);
      $last = $stmt->fetchColumn();

      if ($last) {
          $lastNum = (int)substr($last, -5);
          $nextNum = $lastNum + 1;
      } else {
          $nextNum = 1;
      }

      return $prefix . str_pad($nextNum, 5, "0", STR_PAD_LEFT);
  }

  public static function create($data) {
      if (empty(trim($data['name'] ?? ''))) {
          throw new PDOException("Patient name is required.");
      }
      if (empty(trim($data['barangay'] ?? ''))) {
          throw new PDOException("Barangay is required.");
      }

      $pdo = getDB();
      $userId = (!empty($data['user_id']) && is_numeric($data['user_id'])) ? $data['user_id'] : null;

      // Auto-generate patient code
      if (empty($data['patient_code'])) {
          $data['patient_code'] = self::generatePatientCode();
      }

      // Auto-generate TB case number (unless explicitly provided)
      if (empty($data['tb_case_number'])) {
          $data['tb_case_number'] = self::generateTbCaseNumber();
      }

      $sql = "INSERT INTO patients (
                patient_code, name, age, sex, barangay, contact_number, philhealth_id,
                tb_case_number, bacteriological_status, anatomical_site,
                drug_susceptibility, treatment_history, treatment_outcome, outcome_notes, created_by, user_id
              )
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

      $stmt = $pdo->prepare($sql);

      $stmt->execute([
        $data['patient_code'],
        $data['name'],
        $data['age'] ?? null,
        $data['sex'] ?? 'Unknown',
        $data['barangay'],
        $data['contact_number'] ?? null,
        $data['philhealth_id'] ?? null,
        $data['tb_case_number'],
        $data['bacteriological_status'] ?? 'Unknown',
        $data['anatomical_site'] ?? 'Unknown',
        $data['drug_susceptibility'] ?? 'Unknown',
        $data['treatment_history'] ?? 'Unknown',
        $data['treatment_outcome'] ?? 'active',
        $data['outcome_notes'] ?? null,
        $data['created_by'] ?? null,
        $userId
      ]);

      return $pdo->lastInsertId();
  }

  public static function getAllWithoutUser() {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT *
        FROM patients
        WHERE user_id IS NULL
        ORDER BY patient_code ASC
    ");
    return $stmt->fetchAll();
  }

  public static function getAllByBarangay($b) {
    if (empty($b)) return [];
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE barangay=? ORDER BY created_at DESC");
    $stmt->execute([$b]);
    return $stmt->fetchAll();
  }

  public static function getById($id) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
      $stmt->execute([$id]);
      return $stmt->fetch();
  }

  public static function getAllFiltered($q = '', $barangay = '', $outcome = '') {
      $pdo = getDB();
      $sql = "SELECT * FROM patients WHERE 1=1 ";
      $params = [];

      if (!empty($q)) {
          $sql .= "AND (patient_code LIKE ? OR name LIKE ? OR tb_case_number LIKE ? OR philhealth_id LIKE ?) ";
          $like = '%' . $q . '%';
          $params[] = $like;
          $params[] = $like;
          $params[] = $like;
          $params[] = $like; // philhealth_id search
      }
      if (!empty($barangay)) {
          $sql .= "AND barangay = ? ";
          $params[] = $barangay;
      }
      if (!empty($outcome)) {
          $sql .= "AND treatment_outcome = ? ";
          $params[] = $outcome;
      }

      $sql .= "ORDER BY created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }

  public static function getAllByBarangayFiltered($b, $q = '', $outcome = '') {
      if (empty($b)) return [];
      $pdo = getDB();
      $sql = "SELECT * FROM patients WHERE barangay = ? ";
      $params = [$b];

      if (!empty($q)) {
          $like = '%' . $q . '%';
          $sql .= "AND (patient_code LIKE ? OR name LIKE ? OR tb_case_number LIKE ? OR philhealth_id LIKE ?) ";
          $params[] = $like;
          $params[] = $like;
          $params[] = $like;
          $params[] = $like; // philhealth_id search
      }
      if (!empty($outcome)) {
          $sql .= "AND treatment_outcome = ? ";
          $params[] = $outcome;
      }

      $sql .= "ORDER BY created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }

  public static function delete($id) {
      $pdo = getDB();
      $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id=?");
      return $stmt->execute([$id]);
  }

  public static function update($id, $data) {
      $pdo = getDB();
      $sql = "UPDATE patients SET
                name=?, age=?, sex=?, barangay=?, contact_number=?, philhealth_id=?,
                tb_case_number=?, bacteriological_status=?, anatomical_site=?,
                drug_susceptibility=?, treatment_history=?, treatment_outcome=?, outcome_notes=?
              WHERE patient_id=?";

      return $pdo->prepare($sql)->execute([
        $data['name'],
        $data['age'] ?? null,
        $data['sex'] ?? 'Unknown',
        $data['barangay'],
        $data['contact_number'] ?? null,
        $data['philhealth_id'] ?? null,
        $data['tb_case_number'],
        $data['bacteriological_status'] ?? 'Unknown',
        $data['anatomical_site'] ?? 'Unknown',
        $data['drug_susceptibility'] ?? 'Unknown',
        $data['treatment_history'] ?? 'Unknown',
        $data['treatment_outcome'] ?? 'active',
        $data['outcome_notes'] ?? null,
        $id
      ]);
  }

    public static function createFromImport($data, $uid = null) {
    $data['user_id'] = (!empty($uid) && is_numeric($uid)) ? $uid : null;
    return self::create($data);
  }

  public static function getAll() {
    return getDB()->query("SELECT * FROM patients ORDER BY created_at DESC")->fetchAll();
  }

  public static function existsByCode($code) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM patients WHERE patient_code=?");
    $stmt->execute([$code]);
    return $stmt->fetch()['c'] > 0;
  }

  public static function existsByTbCaseNumber($tbCaseNumber) {
    if (empty($tbCaseNumber)) return false;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM patients WHERE tb_case_number=?");
    $stmt->execute([$tbCaseNumber]);
    return $stmt->fetch()['c'] > 0;
  }

  public static function exportAll() {
    return self::getAll();
  }

  // Analytics methods for admin dashboard
  public static function getAgeGroupStats() {
    $pdo = getDB();
    $stmt = $pdo->query("
      SELECT
        COUNT(CASE WHEN age BETWEEN 0 AND 18 THEN 1 END) as age_0_18,
        COUNT(CASE WHEN age BETWEEN 19 AND 35 THEN 1 END) as age_19_35,
        COUNT(CASE WHEN age BETWEEN 36 AND 55 THEN 1 END) as age_36_55,
        COUNT(CASE WHEN age >= 56 THEN 1 END) as age_56_plus,
        COUNT(CASE WHEN age IS NULL OR age = 0 THEN 1 END) as age_unknown
      FROM patients
    ");
    return $stmt->fetch();
  }

  public static function getGenderStats() {
    $pdo = getDB();
    $stmt = $pdo->query("
      SELECT
        COUNT(CASE WHEN sex = 'M' THEN 1 END) as male,
        COUNT(CASE WHEN sex = 'F' THEN 1 END) as female,
        COUNT(CASE WHEN sex NOT IN ('M', 'F') OR sex IS NULL THEN 1 END) as unknown
      FROM patients
    ");
    return $stmt->fetch();
  }

  public static function getTreatmentOutcomeStats() {
    $pdo = getDB();
    $stmt = $pdo->query("
      SELECT
        COUNT(CASE WHEN treatment_outcome = 'active' THEN 1 END) as active,
        COUNT(CASE WHEN treatment_outcome = 'cured' THEN 1 END) as cured,
        COUNT(CASE WHEN treatment_outcome = 'treatment_completed' THEN 1 END) as treatment_completed,
        COUNT(CASE WHEN treatment_outcome = 'died' THEN 1 END) as died,
        COUNT(CASE WHEN treatment_outcome = 'lost_to_followup' THEN 1 END) as lost_to_followup,
        COUNT(CASE WHEN treatment_outcome = 'failed' THEN 1 END) as failed,
        COUNT(CASE WHEN treatment_outcome = 'transferred_out' THEN 1 END) as transferred_out
      FROM patients
    ");
    return $stmt->fetch();
  }

  public static function getMonthlyStats($months = 6) {
    $pdo = getDB();
    $months = intval($months); // Ensure it's an integer
    $stmt = $pdo->prepare("
      SELECT
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as patients
      FROM patients
      WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . $months . " MONTH)
      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
      ORDER BY month DESC
      LIMIT " . $months . "
    ");
    $stmt->execute();
    $results = $stmt->fetchAll();

    // Format for display
    $formatted = [];
    foreach ($results as $result) {
      $date = DateTime::createFromFormat('Y-m', $result['month']);
      $formatted[] = [
        'month' => $date ? $date->format('M Y') : $result['month'],
        'patients' => $result['patients']
      ];
    }
    return $formatted;
  }

  public static function getBarangayStats() {
    $pdo = getDB();
    $stmt = $pdo->query("
      SELECT barangay, COUNT(*) as count
      FROM patients
      GROUP BY barangay
      ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

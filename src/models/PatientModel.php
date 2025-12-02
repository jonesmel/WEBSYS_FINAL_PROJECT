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

  public static function exportAll() {
    return self::getAll();
  }
}

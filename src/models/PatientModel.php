<?php
require_once __DIR__ . '/../../config/db.php';

class PatientModel {

  public static function create($data) {
    $pdo = getDB();
    $sql = "INSERT INTO patients (patient_code, age, sex, barangay, contact_number, tb_case_number, bacteriological_status, anatomical_site, drug_susceptibility, treatment_history, created_by, user_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
      $data['patient_code'] ?? self::generatePatientCode(),
      $data['age'] ?? null,
      $data['sex'] ?? 'Unknown',
      $data['barangay'],
      $data['contact_number'] ?? null,
      $data['tb_case_number'] ?? null,
      $data['bacteriological_status'] ?? 'Unknown',
      $data['anatomical_site'] ?? 'Unknown',
      $data['drug_susceptibility'] ?? 'Unknown',
      $data['treatment_history'] ?? 'Unknown',
      $data['created_by'] ?? null,
      $data['user_id'] ?? null
    ]);
    return $pdo->lastInsertId();
  }

  public static function createFromImport($data,$uid=null) {
    $data['user_id'] = $uid;
    return self::create($data);
  }

  public static function getById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM patients WHERE patient_id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function getAll() {
    return getDB()->query('SELECT * FROM patients ORDER BY created_at DESC')->fetchAll();
  }

  public static function getAllWithoutUser() {
      $pdo = getDB();
      $stmt = $pdo->query("
          SELECT * FROM patients
          WHERE user_id IS NULL
          ORDER BY patient_code ASC
      ");
      return $stmt->fetchAll();
  }

  public static function getAllByBarangay($b) {
    if (empty($b)) return [];
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM patients WHERE barangay=? ORDER BY created_at DESC');
    $stmt->execute([$b]);
    return $stmt->fetchAll();
  }

  public static function existsByCode($code) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM patients WHERE patient_code=?');
    $stmt->execute([$code]);
    return $stmt->fetch()['c'] > 0;
  }

  public static function generatePatientCode() {
    $pdo = getDB();
    $prefix = 'TB-'.date('Y').'-';
    do {
      $rand = str_pad(random_int(1,99999),5,'0',STR_PAD_LEFT);
      $code = $prefix.$rand;
      $check = $pdo->prepare('SELECT 1 FROM patients WHERE patient_code=?');
      $check->execute([$code]);
    } while ($check->fetch());
    return $code;
  }

  public static function update($id,$data) {
    $pdo = getDB();
    $sql = "UPDATE patients
            SET age=?, sex=?, barangay=?, contact_number=?, tb_case_number=?, bacteriological_status=?, anatomical_site=?, drug_susceptibility=?, treatment_history=?
            WHERE patient_id=?";

    return $pdo->prepare($sql)->execute([
      $data['age'] ?? null,
      $data['sex'] ?? 'Unknown',
      $data['barangay'],
      $data['contact_number'] ?? null,
      $data['tb_case_number'] ?? null,
      $data['bacteriological_status'] ?? 'Unknown',
      $data['anatomical_site'] ?? 'Unknown',
      $data['drug_susceptibility'] ?? 'Unknown',
      $data['treatment_history'] ?? 'Unknown',
      $id
    ]);
  }

  public static function exportAll() {
    return self::getAll();
  }

  public static function delete($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id=?");
    return $stmt->execute([$id]);
  }
}
?>
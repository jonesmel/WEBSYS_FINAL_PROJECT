<?php
require_once __DIR__ . '/../../config/db.php';

class ReferralModel {
  private static function generateCodePDO() {
    return 'REF-' . date('Ymd') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
  }

  public static function create($data) {
    $pdo = getDB();

    $code = $data['referral_code'] ?? self::generateCodePDO();

    $sql = "INSERT INTO referrals (
      referral_code, patient_id, tb_case_number, referral_date,
      referring_unit, referring_tel, referring_email, referring_address,
      reason_for_referral, details,
      receiving_barangay,
      created_by, created_at, updated_at
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?, ?, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $code,
      $data['patient_id'],
      $data['tb_case_number'] ?? null,
      $data['referral_date'] ?? null,
      $data['referring_unit'] ?? null,
      $data['referring_tel'] ?? null,
      $data['referring_email'] ?? null,
      $data['referring_address'] ?? null,
      $data['reason_for_referral'] ?? null,
      $data['details'] ?? null,
      $data['receiving_barangay'] ?? null,
      $data['created_by'] ?? null
    ]);

    return $pdo->lastInsertId();
  }

  public static function getById($id) {
    $stmt = getDB()->prepare('
      SELECT r.*, p.patient_code, p.name, p.tb_case_number AS patient_tb_case, p.barangay AS patient_barangay
      FROM referrals r
      LEFT JOIN patients p ON p.patient_id = r.patient_id
      WHERE r.referral_id = ?
    ');
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function getAll() {
    return getDB()
      ->query('SELECT r.*, p.patient_code, p.name
               FROM referrals r
               LEFT JOIN patients p ON p.patient_id = r.patient_id
               ORDER BY r.created_at DESC')
      ->fetchAll();
  }

  public static function getAllFiltered($q = '', $receiving_barangay = '', $status = '') {
    $pdo = getDB();
    $sql = "SELECT r.*, p.patient_code, p.name FROM referrals r LEFT JOIN patients p ON p.patient_id = r.patient_id WHERE 1=1 ";
    $params = [];

    if (!empty($q)) {
        $sql .= "AND (r.referral_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR r.details LIKE ?) ";
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if (!empty($receiving_barangay)) {
        $sql .= "AND r.receiving_barangay = ? ";
        $params[] = $receiving_barangay;
    }
    if (!empty($status)) {
        $sql .= "AND r.referral_status = ? ";
        $params[] = $status;
    }

    $sql .= "ORDER BY r.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public static function getByPatient($patient_id) {
    $stmt = getDB()->prepare("SELECT * FROM referrals WHERE patient_id = ? ORDER BY created_at DESC");
    $stmt->execute([$patient_id]);
    return $stmt->fetchAll();
  }

  public static function getAllBySender($userId) {
    $stmt = getDB()->prepare('
        SELECT r.*, p.patient_code, p.name
        FROM referrals r
        LEFT JOIN patients p ON p.patient_id = r.patient_id
        WHERE r.created_by = ?
        ORDER BY r.created_at DESC
    ');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }

  // Health worker = referring_unit is their barangay
  public static function getSentByBarangay($barangay) {
    $stmt = getDB()->prepare('
      SELECT r.*, p.patient_code, p.name
      FROM referrals r
      LEFT JOIN patients p ON p.patient_id=r.patient_id
      WHERE r.referring_unit = ?
      ORDER BY r.created_at DESC
    ');
    $stmt->execute([$barangay]);
    return $stmt->fetchAll();
  }

  public static function getIncomingForBarangay($barangay) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT r.*, p.patient_code, p.name
        FROM referrals r
        JOIN patients p ON p.patient_id = r.patient_id
        WHERE r.receiving_barangay = ?
          AND r.referral_status = 'pending'
        ORDER BY r.referral_date DESC
    ");
    $stmt->execute([$barangay]);
    return $stmt->fetchAll();
  }

  public static function updateReceiving($id, $data) {
    $pdo = getDB();
    $sql = "UPDATE referrals SET
      receiving_unit=?, receiving_officer=?, date_received=?,
      action_taken=?, remarks=?, referral_status=?, received_by=?,
      updated_at=NOW()
      WHERE referral_id=?";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
      $data['receiving_unit'] ?? null,
      $data['receiving_officer'] ?? null,
      $data['date_received'] ?? null,
      $data['action_taken'] ?? null,
      $data['remarks'] ?? null,
      $data['referral_status'] ?? 'received',
      $data['received_by'] ?? null,
      $id
    ]);
  }

  public static function update($id, $data) {
    $pdo = getDB();
    $sql = "UPDATE referrals SET
      patient_id=?, tb_case_number=?, referral_date=?,
      referring_unit=?, referring_tel=?, referring_email=?, referring_address=?,
      reason_for_referral=?, details=?, receiving_barangay=?,
      updated_at=NOW()
      WHERE referral_id=?";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
      $data['patient_id'],
      $data['tb_case_number'],
      $data['referral_date'],
      $data['referring_unit'],      
      $data['referring_tel'],
      $data['referring_email'],
      $data['referring_address'],
      $data['reason_for_referral'],
      $data['details'],
      $data['receiving_barangay'],
      $id
    ]);
  }

  public static function delete($id) {
    $stmt = getDB()->prepare("DELETE FROM referrals WHERE referral_id = ?");
    return $stmt->execute([$id]);
  }

  public static function getReceivedByBarangay($barangay) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT r.*, p.patient_code, p.name
        FROM referrals r
        JOIN patients p ON p.patient_id = r.patient_id
        WHERE r.receiving_barangay = ?
          AND r.referral_status = 'received'
        ORDER BY r.date_received DESC
    ");
    $stmt->execute([$barangay]);
    return $stmt->fetchAll();
  }

  public static function patientHasPending($patient_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as c
        FROM referrals
        WHERE patient_id = ?
        AND referral_status = 'pending'
    ");
    $stmt->execute([$patient_id]);
    return $stmt->fetch()['c'] > 0;
  }
}
?>

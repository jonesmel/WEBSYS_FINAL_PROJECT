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
        SELECT m.*, p.patient_code 
        FROM medications m
        LEFT JOIN patients p ON p.patient_id = m.patient_id
        ORDER BY m.created_at DESC
    ");
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
          SET drugs=?, start_date=?, end_date=?, notes=?
          WHERE medication_id=?
      ");
      return $stmt->execute([
          $data['drugs'], $data['start_date'], $data['end_date'], $data['notes'], $id
      ]);
  }
}

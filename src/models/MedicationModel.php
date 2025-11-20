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
    $stmt = $pdo->query("SELECT * FROM medications ORDER BY created_at DESC");
    return $stmt->fetchAll();
  }

  public static function getByPatient($patientId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE patient_id = ?");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
  }
}

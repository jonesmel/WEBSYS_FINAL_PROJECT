<?php
require_once __DIR__ . '/../../config/db.php';

class LogModel {
  public static function insertLog($userId, $action, $table, $recordId, $old=null, $new=null, $ip=null, $ua=null) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
      INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
      $userId,
      $action,
      $table,
      $recordId,
      $old,
      $new,
      $ip,
      $ua
    ]);
  }

  public static function getLogs($filters=[]) {
    $pdo = getDB();
    $query = "SELECT * FROM audit_logs WHERE 1=1";
    $params = [];

    if (!empty($filters['user_id'])) {
      $query .= " AND user_id = ?";
      $params[] = $filters['user_id'];
    }

    if (!empty($filters['action'])) {
      $query .= " AND action = ?";
      $params[] = $filters['action'];
    }

    if (!empty($filters['from'])) {
      $query .= " AND created_at >= ?";
      $params[] = $filters['from'];
    }

    if (!empty($filters['to'])) {
      $query .= " AND created_at <= ?";
      $params[] = $filters['to'];
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }
}

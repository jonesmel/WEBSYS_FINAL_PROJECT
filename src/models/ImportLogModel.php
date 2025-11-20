<?php
require_once __DIR__ . '/../../config/db.php';

class ImportLogModel {
  public static function logUpload($filename, $rowsImported, $importedBy) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
      INSERT INTO import_logs (filename, rows_imported, imported_by)
      VALUES (?, ?, ?)
    ");
    $stmt->execute([$filename, $rowsImported, $importedBy]);
    return $pdo->lastInsertId();
  }
}

<?php
require_once __DIR__ . '/../../config/db.php';

class NotificationModel {
    // Create notification (works for both patients and users)
    public static function create($data) {
        $pdo = getDB();

        $stmt = $pdo->prepare("
            INSERT INTO notifications 
                (user_id, patient_id, type, title, message, link, is_read, created_at)
            VALUES 
                (?, ?, ?, ?, ?, ?, 0, NOW())
        ");

        return $stmt->execute([
            $data['user_id'] ?? null,
            $data['patient_id'] ?? null,
            $data['type'] ?? null,
            $data['title'] ?? '',
            $data['message'] ?? '',
            $data['link'] ?? null
        ]);
    }

    // Get notifications for a user
    public static function getByUser($uid) {
        $pdo = getDB();

        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$uid]);

        return $stmt->fetchAll();
    }

    // Get notifications for a patient
    public static function getByPatient($pid) {
        $pdo = getDB();

        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE patient_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$pid]);

        return $stmt->fetchAll();
    }

    // For patients
    public static function getAllForPatient($user_id) {
        $stmt = getDB()->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // For health workers (barangay-wide)
    public static function getByBarangay($barangay) {
        $stmt = getDB()->prepare("
            SELECT n.*
            FROM notifications n
            JOIN users u ON u.user_id = n.user_id
            WHERE u.barangay_assigned = ?
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$barangay]);
        return $stmt->fetchAll();
    }
}

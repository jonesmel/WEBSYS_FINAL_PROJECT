<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';

class NotificationModel {

    public static function create(array $data) {
        $pdo = getDB();

        if (!empty($data['notification_type']) && empty($data['type'])) {
            $data['type'] = $data['notification_type'];
        }

        if (empty($data['user_id']) && !empty($data['patient_id'])) {
            $stmt = $pdo->prepare("SELECT user_id FROM patients WHERE patient_id = ?");
            $stmt->execute([$data['patient_id']]);
            $resolved = $stmt->fetchColumn();
            if (!empty($resolved)) $data['user_id'] = $resolved;
        }

        $stmt = $pdo->prepare("
            INSERT INTO notifications
                (user_id, patient_id, type, title, message, link,
                 is_read, scheduled_at, is_sent, sent_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, 0, NULL, NOW())
        ");

        $stmt->execute([
            $data['user_id'] ?? null,
            $data['patient_id'] ?? null,
            $data['type'] ?? null,
            $data['title'] ?? '',
            $data['message'] ?? '',
            $data['link'] ?? null,
            $data['scheduled_at'] ?? null
        ]);

        $id = $pdo->lastInsertId();

        if (empty($data['scheduled_at'])) {
            self::sendNow($id);
        }

        return $id;
    }

    public static function sendNow($notification_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT n.*, u.email, u.is_verified
                               FROM notifications n
                               LEFT JOIN users u ON u.user_id = n.user_id
                               WHERE n.notification_id = ?");
        $stmt->execute([$notification_id]);
        $n = $stmt->fetch();

        if (!$n) return false;

        // Do not send immediate emails to unverified users
        if (empty($n['email']) || (int)$n['is_verified'] !== 1) {
            return false;
        }

        $sent = EmailHelper::sendReminder(
            $n['email'],
            $n['title'],
            $n['message']
        );

        if ($sent) {
            $up = $pdo->prepare("UPDATE notifications SET is_sent = 1, sent_at = NOW() WHERE notification_id = ?");
            $up->execute([$notification_id]);
        }

        return $sent;
    }

    public static function createForPatientUser($patient_id, $type, $title, $message, $link = null, $scheduled_at = null) {
        return self::create([
            'patient_id' => $patient_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'scheduled_at' => $scheduled_at ?? null
        ]);
    }

    public static function getByUser($uid) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public static function getLatestForUser($uid, $limit = 10) {
        $pdo = getDB();
        $limit = intval($limit); // must be integer

        $stmt = $pdo->prepare("
            SELECT *
            FROM notifications 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT $limit
        ");

        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public static function countUnreadForUser($uid) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$uid]);
        $r = $stmt->fetch();
        return intval($r['c'] ?? 0);
    }

    public static function getByType($type) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE type = ? ORDER BY created_at DESC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public static function markRead($notification_id) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ?")
                   ->execute([$notification_id]);
    }

    public static function markUnread($notification_id) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE notification_id = ?")
                   ->execute([$notification_id]);
    }

    public static function markAllReadForUser($uid) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);
    }
}
?>

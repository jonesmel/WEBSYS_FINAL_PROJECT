<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';

class NotificationModel {

    public static function create(array $data) {
        $pdo = getDB();

        // Handle old notification_type field name for backward compatibility
        if (!empty($data['notification_type']) && empty($data['type'])) {
            $data['type'] = $data['notification_type'];
        }

        if (!empty($data['patient_id']) && (empty($data['user_id']) || $data['user_id'] === -999)) {
            // If temporary user_id -999, this is a system notification that should show patient_id in UI
            // but not auto-route to patient. Set user_id to null (system notification)
            if ($data['user_id'] === -999) {
                $data['user_id'] = null;
            } else {
                // Normal case: redirect to patient's user account
                $stmt = $pdo->prepare("SELECT user_id FROM patients WHERE patient_id = ?");
                $stmt->execute([$data['patient_id']]);
                $resolved = $stmt->fetchColumn();
                if (!empty($resolved)) $data['user_id'] = $resolved;
            }
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

    public static function getByUser($uid, $limit = null, $offset = 0) {
        $pdo = getDB();
        $query = "
            SELECT n.*, u.email, u.role
            FROM notifications n
            LEFT JOIN users u ON u.user_id = n.user_id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
        ";

        $params = [$uid];

        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getAllWithUserInfo($limit = null, $offset = 0) {
        $pdo = getDB();
        $query = "
            SELECT n.*, u.email, u.role
            FROM notifications n
            LEFT JOIN users u ON u.user_id = n.user_id
            ORDER BY n.created_at DESC
        ";

        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        return $pdo->query($query)->fetchAll();
    }

    public static function getTotalNotifications() {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM notifications");
        $result = $stmt->fetch();
        return $result['total'];
    }

    public static function getTotalNotificationsForUser($uid) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
        $stmt->execute([$uid]);
        $result = $stmt->fetch();
        return $result['total'];
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

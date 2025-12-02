<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class NotificationController {
    public function list() {
        AuthMiddleware::requireLogin();
        $uid = $_SESSION['user']['user_id'];
        $role = $_SESSION['user']['role'];

        if ($role === 'super_admin') {
            // super admin sees all notifications
            $rows = NotificationModel::getAllWithUserInfo();

            // For super admin: mark all notifications as read EXCEPT staff follow-up type
            // Get all notification IDs for this admin that are NOT staff follow-up type
            $pdo = getDB();
            $stmt = $pdo->prepare("
                SELECT notification_id FROM notifications
                WHERE user_id = ? AND type != 'staff_follow_up' AND is_read = 0
            ");
            $stmt->execute([$uid]);
            $notificationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Mark only non-staff-follow-up notifications as read
            foreach ($notificationIds as $nid) {
                NotificationModel::markRead($nid);
            }
        } else {
            // For regular users: mark all their notifications as read
            NotificationModel::markAllReadForUser($uid);
            $rows = NotificationModel::getByUser($uid);
        }

        include __DIR__ . '/../../public/notifications/list.php';
    }

    // JSON: unread count
    public function json_unread_count() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        $uid = $_SESSION['user']['user_id'];
        echo json_encode(['count' => NotificationModel::countUnreadForUser($uid)]);
    }

    // JSON: latest notifications (for dropdown)
    public function json_latest() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        $uid = $_SESSION['user']['user_id'];
        $rows = NotificationModel::getLatestForUser($uid, 10);
        echo json_encode($rows);
    }

    public function mark_read() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if ($id) NotificationModel::markRead($id);

        // Detect fetch() correctly
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
        exit;
    }

    public function mark_unread() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if ($id) {
            NotificationModel::markUnread($id);
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    public function mark_all_read() {
        AuthMiddleware::requireLogin();
        $uid = $_SESSION['user']['user_id'];
        NotificationModel::markAllReadForUser($uid);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
        exit;
    }
}
?>

<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class NotificationController {

    public function list() {
        AuthMiddleware::requireLogin();

        $uid = $_SESSION['user']['user_id'];
        $rows = NotificationModel::getByUser($uid);

        include __DIR__ . '/../../public/notifications/list.php';
    }
}

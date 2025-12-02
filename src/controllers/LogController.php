<?php
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class LogController {
    public function index() {
        AuthMiddleware::requireRole(['super_admin']);

        $filters = [];

        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action']))  $filters['action'] = $_GET['action'];

        $rows = LogModel::getLogs($filters);

        include __DIR__ . '/../../public/logs/index.php';
    }
}

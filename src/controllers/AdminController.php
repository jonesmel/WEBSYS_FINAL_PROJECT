<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AdminController {
    public function dashboard() {
        AuthMiddleware::requireRole(['super_admin']);
        $patients = PatientModel::getAll();
        include __DIR__ . '/../../public/admin/dashboard.php';
    }

    public function users() {
        AuthMiddleware::requireRole(['super_admin']);
        include __DIR__ . '/../../public/admin/users.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['super_admin']);
        include __DIR__ . '/../../public/admin/profile.php';
    }
}

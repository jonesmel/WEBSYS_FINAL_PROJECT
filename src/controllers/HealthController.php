<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class HealthController {

    public function dashboard() {
        AuthMiddleware::requireRole(['health_worker']);
        $barangay = $_SESSION['user']['barangay_assigned'];
        $patients = PatientModel::getAllByBarangay($barangay);
        include __DIR__ . '/../../public/health/dashboard.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['health_worker']);
        include __DIR__ . '/../../public/health/profile.php';
    }
}

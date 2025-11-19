<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PatientDashboardController {

    public function index() {
        AuthMiddleware::requireRole(['patient']);
        include __DIR__ . '/../../public/patient/dashboard.php';
    }

    public function notifications() {
        AuthMiddleware::requireRole(['patient']);
        include __DIR__ . '/../../public/patient/notifications.php';
    }

    public function referrals() {
        AuthMiddleware::requireRole(['patient']);
        include __DIR__ . '/../../public/patient/referrals.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['patient']);
        include __DIR__ . '/../../public/patient/profile.php';
    }
}

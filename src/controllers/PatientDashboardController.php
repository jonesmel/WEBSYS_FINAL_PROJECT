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

    public function medications() {
        AuthMiddleware::requireRole(['patient']);

        $uid = $_SESSION['user']['user_id'];

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
        $stmt->execute([$uid]);
        $patientRecord = $stmt->fetch();
        $pid = $patientRecord ? $patientRecord['patient_id'] : null;

        if ($pid) {
            // Get medications with compliance information - include all new compliance fields
            try {
                $stmt = $pdo->prepare("SELECT * FROM medications WHERE patient_id = ? ORDER BY created_at DESC");
                $stmt->execute([$pid]);
                $rows = $stmt->fetchAll();
            } catch (Exception $e) {
                // If query fails, return empty array
                $rows = [];
            }
        } else {
            $rows = [];
        }

        include __DIR__ . '/../../public/patient/medications.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['patient']);
        include __DIR__ . '/../../public/patient/profile.php';
    }
}

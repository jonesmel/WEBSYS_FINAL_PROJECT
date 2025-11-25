<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AjaxController {
    public function check_email() {
        header('Content-Type: application/json');

        $email = $_GET['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['valid' => false, 'message' => 'Invalid email format']);
            return;
        }

        $exists = UserModel::emailExists($email);

        if ($exists) {
            echo json_encode(['valid' => false, 'message' => 'Email already registered']);
        } else {
            echo json_encode(['valid' => true, 'message' => 'Email is available']);
        }
    }

    public function search_barangay() {
        header('Content-Type: application/json');

        require_once __DIR__ . '/../helpers/BarangayHelper.php';

        $q = strtolower(trim($_GET['q'] ?? ''));

        $all = BarangayHelper::getAll();

        // simple filter
        $filtered = array_filter($all, function($b) use ($q) {
            return $q === '' || strpos(strtolower($b), $q) !== false;
        });

        echo json_encode(array_values($filtered));
    }
}

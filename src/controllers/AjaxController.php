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

        $filtered = array_filter($all, function($b) use ($q) {
            return $q === '' || strpos(strtolower($b), $q) !== false;
        });

        echo json_encode(array_values($filtered));
    }

    public function fetch_health_workers() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        $sql = "SELECT * FROM users WHERE role='health_worker'";
        $params = [];

        if ($q !== '') {
            $sql .= " AND email LIKE ?";
            $params[] = "%$q%";
        }

        if ($barangay !== '') {
            $sql .= " AND barangay_assigned LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public function fetch_patient_users() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        $sql = "
            SELECT u.*, p.patient_code, p.barangay AS patient_barangay
            FROM users u
            JOIN patients p ON p.user_id = u.user_id
            WHERE u.role = 'patient'
        ";

        $params = [];

        if ($q !== '') {
            $sql .= " AND (u.email LIKE ? OR p.patient_code LIKE ?)";
            $like = "%$q%";
            $params[] = $like;
            $params[] = $like;
        }

        if ($barangay !== '') {
            $sql .= " AND p.barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode($stmt->fetchAll());
    }

    public function fetch_patients() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        $sql = "
            SELECT p.*,
                CASE WHEN u.user_id IS NULL THEN 0 ELSE 1 END AS has_user
            FROM patients p
            LEFT JOIN users u ON p.user_id = u.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($q !== '') {
            // search patient_code OR tb_case_number
            $sql .= " AND (p.patient_code LIKE ? OR p.tb_case_number LIKE ?)";
            $like = "%$q%";
            $params[] = $like;
            $params[] = $like;
        }

        if ($barangay !== '') {
            // partial match so typed fragments work
            $sql .= " AND p.barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // normalize output (consistent keys expected by app.js)
        $out = array_map(function($r){
            return [
                'patient_id' => $r['patient_id'],
                'patient_code' => $r['patient_code'],
                'barangay' => $r['barangay'],
                'age' => $r['age'],
                'sex' => $r['sex'],
                'tb_case_number' => $r['tb_case_number'],
                'has_user' => ($r['has_user'] ? 1 : 0)
            ];
        }, $rows);

        echo json_encode($out);
    }
}

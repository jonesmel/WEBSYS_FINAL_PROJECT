<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/LogModel.php';
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
        $outcome = trim($_GET['treatment_outcome'] ?? '');
        $userRole = $_SESSION['user']['role'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "
            SELECT p.*,
                CASE WHEN u.user_id IS NULL THEN 0 ELSE 1 END AS has_user
            FROM patients p
            LEFT JOIN users u ON p.user_id = u.user_id
            WHERE 1=1
        ";
        $params = [];

        // Auto-filter by health worker's assigned barangay
        if ($userRole === 'health_worker' && $userBarangay !== '') {
            $sql .= " AND p.barangay = ?";
            $params[] = $userBarangay;
        }

        if ($q !== '') {
            // search patient_code, name, tb_case_number, philhealth_id, age, sex, and barangay
            $sql .= " AND (
                p.patient_code LIKE ? OR
                p.name LIKE ? OR
                p.tb_case_number LIKE ? OR
                p.philhealth_id LIKE ? OR
                CAST(p.age AS CHAR) LIKE ? OR
                LOWER(p.sex) LIKE ? OR
                LOWER(p.barangay) LIKE ?
            )";
            $like = "%$q%";
            $params[] = $like;
            $params[] = $like; // name search
            $params[] = $like;
            $params[] = $like; // philhealth_id search
            $params[] = $like;
            $params[] = strtolower($like); // sex search case-insensitive
            $params[] = strtolower($like); // barangay search case-insensitive
        }

        if ($barangay !== '') {
            // partial match so typed fragments work
            $sql .= " AND p.barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        if ($outcome !== '') {
            $sql .= " AND p.treatment_outcome = ?";
            $params[] = $outcome;
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
                'name' => $r['name'] ?? '',
                'barangay' => $r['barangay'],
                'age' => $r['age'],
                'sex' => $r['sex'],
                'tb_case_number' => $r['tb_case_number'],
                'philhealth_id' => $r['philhealth_id'] ?? null,
                'treatment_outcome' => $r['treatment_outcome'],
                'has_user' => ($r['has_user'] ? 1 : 0)
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_contacts() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        $sql = "
            SELECT c.*, p.patient_code, p.name, p.barangay AS patient_barangay
            FROM contacts c
            LEFT JOIN patients p ON p.patient_id = c.patient_id
            WHERE c.is_archived = 0
        ";
        $params = [];

        if ($q !== '') {
            // search contact_code, patient_code, name, relationship, contact_number, and date fields
            $sql .= " AND (c.contact_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR c.relationship LIKE ? OR c.contact_number LIKE ? OR DATE_FORMAT(c.created_at, '%Y-%m-%d') LIKE ?)";
            $like = "%$q%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($barangay !== '') {
            $sql .= " AND c.barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // normalize output
        $out = array_map(function($r){
            return [
                'contact_id' => $r['contact_id'],
                'contact_code' => $r['contact_code'],
                'patient_id' => $r['patient_id'] ?? null,
                'patient_code' => $r['patient_code'] ?? '',
                'name' => $r['name'] ?? '',
                'patient_barangay' => $r['patient_barangay'] ?? '',
                'age' => $r['age'],
                'sex' => $r['sex'],
                'relationship' => $r['relationship'],
                'contact_number' => $r['contact_number'],
                'barangay' => $r['barangay'],
                'screening_result' => $r['screening_result'],
                'status' => $r['status'],
                'created_at' => $r['created_at']
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_medications() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $userRole = $_SESSION['user']['role'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "
            SELECT m.*, p.patient_code, p.name
            FROM medications m
            LEFT JOIN patients p ON p.patient_id = m.patient_id
            WHERE 1=1
        ";
        $params = [];

        // Auto-filter by health worker's assigned barangay
        if ($userRole === 'health_worker' && $userBarangay !== '') {
            $sql .= " AND p.barangay = ?";
            $params[] = $userBarangay;
        }

        if ($q !== '') {
            // search drugs, notes, patient_code, name, start_date, end_date for health workers
            if ($userRole === 'health_worker') {
                // Health workers can search patient info within their AOR (patient filter already applied)
                $sql .= " AND (
                    m.drugs LIKE ? OR m.notes LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR
                    DATE_FORMAT(m.start_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(m.end_date, '%Y-%m-%d') LIKE ?
                )";
                $like = "%$q%";
                $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
            } else {
                // Super admin can search patient_code and name too
                $sql .= " AND (
                    m.drugs LIKE ? OR m.notes LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR
                    DATE_FORMAT(m.start_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(m.end_date, '%Y-%m-%d') LIKE ?
                )";
                $like = "%$q%";
                $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
            }
        }

        if ($barangay !== '') {
            $sql .= " AND p.barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY m.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // normalize output
        $out = array_map(function($r){
            return [
                'medication_id' => $r['medication_id'] ?? $r['id'],
                'patient_code' => $r['patient_code'],
                'name' => $r['name'] ?? '',
                'drugs' => $r['drugs'] ?? $r['regimen'] ?? '',
                'start_date' => $r['start_date'],
                'end_date' => $r['end_date'],
                'notes' => $r['notes'],
                'created_at' => $r['created_at']
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_referrals() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $referring_barangay = trim($_GET['referring_barangay'] ?? '');

        $sql = "
            SELECT r.*, p.patient_code, p.name
            FROM referrals r
            LEFT JOIN patients p ON p.patient_id = r.patient_id
            WHERE 1=1
        ";
        $params = [];

        if ($q !== '') {
            // broad search: code, patient_code, patient_name, details, dates, status
            $sql .= " AND (
                r.referral_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR r.details LIKE ? OR r.reason_for_referral LIKE ? OR
                DATE_FORMAT(r.referral_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(r.created_at, '%Y-%m-%d') LIKE ? OR
                r.referral_status LIKE ?
            )";
            $like = "%$q%";
            $params = [$like, $like, $like, $like, $like, $like, $like, $like];
        }

        if ($barangay !== '') {
            $sql .= " AND r.receiving_barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        if ($referring_barangay !== '') {
            $sql .= " AND r.referring_unit LIKE ?";
            $params[] = "%$referring_barangay%";
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // normalize output
        $out = array_map(function($r){
            return [
                'referral_id' => $r['referral_id'],
                'referral_code' => $r['referral_code'],
                'patient_code' => $r['patient_code'],
                'name' => $r['name'] ?? '',
                'referral_date' => $r['referral_date'],
                'referring_unit' => $r['referring_unit'],
                'referring_tel' => $r['referring_tel'],
                'referring_email' => $r['referring_email'],
                'receiving_barangay' => $r['receiving_barangay'],
                'referral_status' => $r['referral_status'] ?? 'pending'
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_sent_referrals() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $userId = $_SESSION['user']['user_id'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "
            SELECT r.*, p.patient_code, p.name
            FROM referrals r
            LEFT JOIN patients p ON p.patient_id = r.patient_id
            WHERE r.created_by = ?
        ";
        $params = [$userId];

        if ($q !== '') {
            $sql .= " AND (
                r.referral_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR r.details LIKE ? OR r.reason_for_referral LIKE ? OR
                DATE_FORMAT(r.referral_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(r.created_at, '%Y-%m-%d') LIKE ? OR
                r.referral_status LIKE ?
            )";
            $like = "%$q%";
            $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like, $like]);
        }

        if ($barangay !== '') {
            $sql .= " AND r.receiving_barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = array_map(function($r){
            return [
                'referral_id' => $r['referral_id'],
                'referral_code' => $r['referral_code'],
                'patient_code' => $r['patient_code'],
                'name' => $r['name'] ?? '',
                'referral_date' => $r['referral_date'],
                'referring_unit' => $r['referring_unit'],
                'referring_tel' => $r['referring_tel'],
                'referring_email' => $r['referring_email'],
                'receiving_barangay' => $r['receiving_barangay'],
                'referral_status' => $r['referral_status'] ?? 'pending'
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_incoming_referrals() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "
            SELECT r.*, p.patient_code, p.name
            FROM referrals r
            LEFT JOIN patients p ON p.patient_id = r.patient_id
            WHERE r.receiving_barangay = ? AND r.referral_status = 'pending'
        ";
        $params = [$userBarangay];

        if ($q !== '') {
            $sql .= " AND (
                r.referral_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR r.details LIKE ? OR r.reason_for_referral LIKE ? OR
                DATE_FORMAT(r.referral_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(r.created_at, '%Y-%m-%d') LIKE ? OR
                r.referral_status LIKE ?
            )";
            $like = "%$q%";
            $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like, $like]);
        }

        if ($barangay !== '') {
            $sql .= " AND r.receiving_barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY r.referral_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = array_map(function($r){
            return [
                'referral_id' => $r['referral_id'],
                'referral_code' => $r['referral_code'],
                'patient_code' => $r['patient_code'],
                'name' => $r['name'] ?? '',
                'referral_date' => $r['referral_date'],
                'referring_unit' => $r['referring_unit'],
                'referring_tel' => $r['referring_tel'],
                'referring_email' => $r['referring_email'],
                'receiving_barangay' => $r['receiving_barangay'],
                'referral_status' => $r['referral_status']
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_received_referrals() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "
            SELECT r.*, p.patient_code, p.name
            FROM referrals r
            LEFT JOIN patients p ON p.patient_id = r.patient_id
            WHERE r.receiving_barangay = ? AND r.referral_status = 'received'
        ";
        $params = [$userBarangay];

        if ($q !== '') {
            $sql .= " AND (
                r.referral_code LIKE ? OR p.patient_code LIKE ? OR p.name LIKE ? OR r.details LIKE ? OR r.reason_for_referral LIKE ? OR
                DATE_FORMAT(r.referral_date, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(r.created_at, '%Y-%m-%d') LIKE ? OR
                r.referral_status LIKE ?
            )";
            $like = "%$q%";
            $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like, $like]);
        }

        if ($barangay !== '') {
            $sql .= " AND r.receiving_barangay LIKE ?";
            $params[] = "%$barangay%";
        }

        $sql .= " ORDER BY r.date_received DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = array_map(function($r){
            return [
                'referral_id' => $r['referral_id'],
                'referral_code' => $r['referral_code'],
                'patient_code' => $r['patient_code'],
                'name' => $r['name'] ?? '',
                'referral_date' => $r['referral_date'],
                'referring_unit' => $r['referring_unit'],
                'referring_tel' => $r['referring_tel'],
                'referring_email' => $r['referring_email'],
                'receiving_barangay' => $r['receiving_barangay'],
                'referral_status' => $r['referral_status']
            ];
        }, $rows);

        echo json_encode($out);
    }

    public function fetch_audit_logs() {
        header("Content-Type: application/json");
        $pdo = getDB();

        $filters = [];

        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action']))  $filters['action'] = $_GET['action'];
        if (!empty($_GET['table_name'])) $filters['table_name'] = $_GET['table_name'];
        if (!empty($_GET['from']))    $filters['from'] = $_GET['from'];
        if (!empty($_GET['to']))      $filters['to'] = $_GET['to'];

        $rows = LogModel::getLogs($filters);

        echo json_encode($rows);
    }

    public function check_current_password() {
        header('Content-Type: application/json');
        $current = $_POST['current_password'] ?? '';
        $user_id = $_SESSION['user']['user_id'] ?? null;
        if (!$user_id) {
            echo json_encode(['valid' => false, 'message' => 'Not logged in']);
            return;
        }
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $hashed = $stmt->fetchColumn();
        $valid = password_verify($current, $hashed);
        echo json_encode(['valid' => $valid, 'message' => $valid ? 'Password correct' : 'Current password is incorrect']);
    }
}

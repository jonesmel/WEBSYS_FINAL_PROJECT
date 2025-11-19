<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/models/PatientModel.php';

// Require login first
AuthMiddleware::requireLogin();

$user = $_SESSION['user'];
$role = $user['role'];

if (!in_array($role, ['super_admin', 'health_worker'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied.";
    exit;
}

if ($role === 'health_worker') {
    $barangay = $user['barangay_assigned'] ?? null;

    if (!$barangay) {
        echo "Barangay missing from session.";
        exit;
    }

    $rows = PatientModel::getAllByBarangay($barangay);
} else {
    $rows = PatientModel::exportAll();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=patients_export_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');

fputcsv($out, [
    'patient_code','barangay','age','sex','tb_case_number','bacteriological_status',
    'anatomical_site','drug_susceptibility','treatment_history','contact_number','created_at'
]);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['patient_code'],
        $r['barangay'],
        $r['age'],
        $r['sex'],
        $r['tb_case_number'],
        $r['bacteriological_status'],
        $r['anatomical_site'],
        $r['drug_susceptibility'],
        $r['treatment_history'],
        $r['contact_number'],
        $r['created_at']
    ]);
}

fclose($out);
exit;
?>

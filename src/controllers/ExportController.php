<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ExportController {

    public function patients_csv() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $rows = PatientModel::exportAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=patients_export_' . date('Ymd') . '.csv');

        $out = fopen('php://output', 'w');

        fputcsv($out, [
            'patient_code',
            'barangay',
            'age',
            'sex',
            'tb_case_number',
            'bacteriological_status',
            'anatomical_site',
            'drug_susceptibility',
            'treatment_history',
            'contact_number',
            'created_at'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['patient_code'],
                $row['barangay'],
                $row['age'],
                $row['sex'],
                $row['tb_case_number'],
                $row['bacteriological_status'],
                $row['anatomical_site'],
                $row['drug_susceptibility'],
                $row['treatment_history'],
                $row['contact_number'],
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}

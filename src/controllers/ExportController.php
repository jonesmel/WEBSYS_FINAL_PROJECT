<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Include Dompdf (make sure you installed it via Composer)
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController {
    public function patients_csv() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $user = $_SESSION['user'];
        $role = $user['role'];

        if ($role === 'health_worker') {
            $barangay = $user['barangay_assigned'] ?? null;
            if (!$barangay) {
                echo "Barangay missing from user session.";
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
            'patient_code','barangay','age','sex','tb_case_number',
            'bacteriological_status','anatomical_site','drug_susceptibility',
            'treatment_history','contact_number','created_at'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['patient_code'],$row['barangay'],$row['age'],$row['sex'],
                $row['tb_case_number'],$row['bacteriological_status'],$row['anatomical_site'],
                $row['drug_susceptibility'],$row['treatment_history'],$row['contact_number'],
                $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function patients_pdf() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $user = $_SESSION['user'];
        $role = $user['role'];

        if ($role === 'health_worker') {
            $barangay = $user['barangay_assigned'] ?? null;
            if (!$barangay) {
                echo "Barangay missing from user session.";
                exit;
            }
            $rows = PatientModel::getAllByBarangay($barangay);
        } else {
            $rows = PatientModel::exportAll();
        }

        // Setup Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Build HTML
        $html = '<h2>Patients Export</h2>';
        $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
        $html .= '<thead><tr>
            <th>Patient Code</th><th>Barangay</th><th>Age</th><th>Sex</th>
            <th>TB Case #</th><th>Bacteriological Status</th><th>Anatomical Site</th>
            <th>Drug Susceptibility</th><th>Treatment History</th><th>Contact Number</th><th>Created At</th>
        </tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.htmlspecialchars($row['patient_code']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['barangay']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['age']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['sex']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['tb_case_number']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['bacteriological_status']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['anatomical_site']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['drug_susceptibility']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['treatment_history']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['contact_number']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['created_at']).'</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Stream PDF - immediately download as attachment
        $dompdf->stream('patients_export_' . date('Ymd_His') . '.pdf', ["Attachment" => true]);
        exit;
    }
}

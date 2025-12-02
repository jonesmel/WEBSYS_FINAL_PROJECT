<?php
use Dompdf\Dompdf;
use Dompdf\Options;

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

    public function generateReport() {
        AuthMiddleware::requireRole(['super_admin']);

        // Get all analytics data
        $total = count(PatientModel::getAll());
        $ageStats = PatientModel::getAgeGroupStats();
        $genderStats = PatientModel::getGenderStats();
        $outcomeStats = PatientModel::getTreatmentOutcomeStats();
        $monthlyStats = PatientModel::getMonthlyStats(6);
        $barangayStats = PatientModel::getBarangayStats();

        // Helper function
        $getPercentage = function($count, $total) {
            return $total > 0 ? round(($count / $total) * 100, 1) : 0;
        };

        // Calculate metrics
        $completed = ($outcomeStats['cured'] ?? 0) + ($outcomeStats['treatment_completed'] ?? 0);
        $successRate = $getPercentage($completed, $total - ($outcomeStats['active'] ?? 0));
        $totalKnownAge = $total - ($ageStats['age_unknown'] ?? 0);
        $totalKnownGender = $total - ($genderStats['unknown'] ?? 0);

        // Generate HTML for PDF
        $html = "
<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
  <style>
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 12px;
      color: #111;
      line-height:1.4;
    }
    .container {
      width: 100%;
      position: relative;
      padding: 20px;
    }
    .header {
      text-align:center;
      font-size:20px;
      margin-bottom:20px;
      font-weight:bold;
      text-transform:uppercase;
      color: #333;
    }
    .subheader {
      text-align:center;
      font-size:14px;
      margin-bottom:30px;
      color: #666;
    }
    .section {
      margin-bottom: 25px;
    }
    .section-title {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 12px;
      color: #444;
      border-bottom: 2px solid #ccc;
      padding-bottom: 5px;
    }
    table {
      width:100%;
      border-collapse:collapse;
      margin-bottom:15px;
    }
    th, td {
      border:1px solid #444;
      padding:8px;
      font-size:11px;
      text-align:left;
      vertical-align:top;
    }
    th {
      background:#f5f5f5;
      font-weight:bold;
    }
    .footer-note {
      position: fixed;
      bottom: 10px;
      left: 20px;
      right: 20px;
      text-align: center;
      font-size: 10px;
      color: #666;
      border-top:1px solid #ccc;
      padding-top:8px;
    }
    .stats-grid {
      display: table;
      width: 100%;
      margin-bottom: 20px;
    }
    .stats-row {
      display: table-row;
    }
    .stats-cell {
      display: table-cell;
      padding: 10px 15px;
      vertical-align: top;
    }
    .stat-number {
      font-size: 18px;
      font-weight: bold;
      color: #333;
    }
    .stat-label {
      font-size: 12px;
      color: #666;
      margin-top: 4px;
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>TB Management System Analytics Report</div>
    <div class='subheader'>Report Generated on " . date('F j, Y \a\t H:i') . "</div>

    <div class='section'>
      <div class='section-title'>Executive Summary</div>
      <table>
        <tr>
          <td width='25%'><strong>Total Patients</strong><br><span style='font-size:16px;color:#007bff;'>$total</span></td>
          <td width='25%'><strong>Active Cases</strong><br><span style='font-size:16px;color:#f0ad4e;'>".($outcomeStats['active'] ?? 0)."</span></td>
          <td width='25%'><strong>Cured</strong><br><span style='font-size:16px;color:#5cb85c;'>".($outcomeStats['cured'] ?? 0)."</span></td>
          <td width='25%'><strong>Success Rate</strong><br><span style='font-size:16px;color:#5cb85c;'>{$successRate}%</span></td>
        </tr>
      </table>
      " . (!empty($barangayStats) ? "<p><strong>Most Affected Barangay:</strong> {$barangayStats[0]['barangay']} ({$barangayStats[0]['count']} patients)</p>" : "") . "
    </div>

    <div class='section'>
      <div class='section-title'>Age Group Demographics</div>
      " . ($totalKnownAge > 0 ? "
      <table>
        <tr><th>Age Group</th><th>Count</th><th>Percentage</th></tr>
        <tr><td>0-18 Years</td><td>".($ageStats['age_0_18'] ?? 0)."</td><td>".$getPercentage($ageStats['age_0_18'] ?? 0, $totalKnownAge)."%</td></tr>
        <tr><td>19-35 Years</td><td>".($ageStats['age_19_35'] ?? 0)."</td><td>".$getPercentage($ageStats['age_19_35'] ?? 0, $totalKnownAge)."%</td></tr>
        <tr><td>36-55 Years</td><td>".($ageStats['age_36_55'] ?? 0)."</td><td>".$getPercentage($ageStats['age_36_55'] ?? 0, $totalKnownAge)."%</td></tr>
        <tr><td>56+ Years</td><td>".($ageStats['age_56_plus'] ?? 0)."</td><td>".$getPercentage($ageStats['age_56_plus'] ?? 0, $totalKnownAge)."%</td></tr>
      </table>" : "<p>No age data available</p>") . "
    </div>

    <div class='section'>
      <div class='section-title'>Gender Distribution</div>
      " . ($totalKnownGender > 0 ? "
      <table>
        <tr><th>Gender</th><th>Count</th><th>Percentage</th></tr>
        <tr><td>Male</td><td>".($genderStats['male'] ?? 0)."</td><td>".$getPercentage($genderStats['male'] ?? 0, $totalKnownGender)."%</td></tr>
        <tr><td>Female</td><td>".($genderStats['female'] ?? 0)."</td><td>".$getPercentage($genderStats['female'] ?? 0, $totalKnownGender)."%</td></tr>
      </table>" : "<p>No gender data available</p>") . "
    </div>

    <div class='section'>
      <div class='section-title'>Treatment Outcomes</div>
      <table>
        <tr><th>Outcome</th><th>Count</th></tr>
        <tr><td>Active</td><td>".($outcomeStats['active'] ?? 0)."</td></tr>
        <tr><td>Cured</td><td>".($outcomeStats['cured'] ?? 0)."</td></tr>
        <tr><td>Treatment Completed</td><td>".($outcomeStats['treatment_completed'] ?? 0)."</td></tr>
        <tr><td>Died</td><td>".($outcomeStats['died'] ?? 0)."</td></tr>
        <tr><td>Lost to Follow-up</td><td>".($outcomeStats['lost_to_followup'] ?? 0)."</td></tr>
        <tr><td>Failed</td><td>".($outcomeStats['failed'] ?? 0)."</td></tr>
        <tr><td>Transferred Out</td><td>".($outcomeStats['transferred_out'] ?? 0)."</td></tr>
      </table>
    </div>

    <div class='section'>
      <div class='section-title'>Geographic Distribution (Top 10 Barangays)</div>
      " . (!empty($barangayStats) ? "
      <table>
        <tr><th>Barangay</th><th>Patient Count</th></tr>
        " . implode('', array_map(function($stat) {
            return "<tr><td>{$stat['barangay']}</td><td>{$stat['count']}</td></tr>";
        }, array_slice($barangayStats, 0, 10))) . "
      </table>" : "<p>No barangay data available</p>") . "
    </div>

    <div class='footer-note'>
      Generated by TB Management System — This report contains confidential patient analytics.<br>
      For internal use only. Generated on " . date('F j, Y \a\t H:i:s') . "
    </div>
  </div>
</body>
</html>";

        // Generate PDF using Dompdf
        require_once __DIR__ . '/../../vendor/autoload.php';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'TB_Analytics_Report_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}

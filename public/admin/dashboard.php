<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/NotificationModel.php';
AuthMiddleware::requireRole(['super_admin']);

$patients = PatientModel::getAll();
$total = count($patients);
$barangayCounts = [];
foreach ($patients as $p) {
  $barangayCounts[$p['barangay']] = ($barangayCounts[$p['barangay']] ?? 0) + 1;
}
?>

<div class="container py-4">
  <h3 class="mb-4">Super Admin Dashboard</h3>

  <div class="mb-3">
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_csv"
      class="btn btn-outline-primary btn-sm">
      Export Patients CSV
    </a>
  </div>
  
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center">
        <h5>Total Patients</h5>
        <div class="display-6 fw-bold"><?=$total?></div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card shadow-sm p-3">
        <h5>Patients per Barangay</h5>
        <table class="table table-sm table-bordered mt-2">
          <thead>
            <tr>
              <th>Barangay</th>
              <th>Count</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($barangayCounts as $b=>$c): ?>
            <tr>
              <td><?=htmlspecialchars($b)?></td>
              <td><?=$c?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
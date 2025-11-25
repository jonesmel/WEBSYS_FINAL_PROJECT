<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
AuthMiddleware::requireRole(['health_worker']);

$barangay = $_SESSION['user']['barangay_assigned'];
$patients = PatientModel::getAllByBarangay($barangay);
?>

<div class="container py-4">
  <h3 class="mb-4">Patients (<?=$barangay?>)</h3>

  <div class="card shadow-sm p-4">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">Patient List</h5>
      <a href="/WEBSYS_FINAL_PROJECT/public/patients/add.php" class="btn btn-primary btn-sm">Add Patient</a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle table-hover">
        <thead>
          <tr>
            <th>Patient Code</th>
            <th>Age</th>
            <th>Sex</th>
            <th>TB Case #</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($patients)): foreach ($patients as $p): ?>
          <tr>
            <td><?=htmlspecialchars($p['patient_code'])?></td>
            <td><?=htmlspecialchars($p['age'])?></td>
            <td><?=htmlspecialchars($p['sex'])?></td>
            <td><?=htmlspecialchars($p['tb_case_number'])?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/patients/view.php?id=<?=$p['patient_id']?>" class="btn btn-sm btn-outline-primary">View</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="text-center text-muted">No patients found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

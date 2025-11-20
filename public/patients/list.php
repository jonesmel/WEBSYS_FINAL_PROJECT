<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Patients</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/create" class="btn btn-primary">Add Patient</a>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Patient Code</th>
            <th>Barangay</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Case #</th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($patients as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['patient_code']) ?></td>
            <td><?= htmlspecialchars($p['barangay']) ?></td>
            <td><?= $p['age'] ?></td>
            <td><?= $p['sex'] ?></td>
            <td><?= htmlspecialchars($p['tb_case_number']) ?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?=$p['patient_id']?>" 
                 class="btn btn-sm btn-outline-primary">View</a>
              </a>
            </td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=<?= $p['patient_id'] ?>"
                onclick="return confirm('Delete this patient?');"
                class="btn btn-sm btn-danger">
                Delete
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

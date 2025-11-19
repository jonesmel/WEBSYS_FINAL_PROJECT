<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Contacts</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/add" class="btn btn-primary">Add Contact</a>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Contact Code</th>
            <th>Patient Code</th>
            <th>Barangay</th>
            <th>Linked Patient</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['contact_code'])?></td>
            <td><?=$c['patient_code']?></td>
            <td><?=htmlspecialchars($c['barangay'])?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?=$c['patient_id']?>"
                class="btn btn-sm btn-link">
                <?= htmlspecialchars($c['patient_code']) ?>
              </a>
            </td>
            <td><?=$c['age']?></td>
            <td><?=$c['sex']?></td>
            <td><?=$c['status']?></td>
            <td>
              <?php if ($c['status'] !== 'converted_patient'): ?>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/convert&id=<?=$c['contact_id']?>" class="btn btn-sm btn-outline-warning">Convert</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
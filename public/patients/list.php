<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Patients</h3>

    <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/create" class="btn btn-primary">Add Patient</a>
    <?php endif; ?>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Patient Code</th>
            <th>Barangay</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Case #</th>
            <th>User Account</th>
            <th width="300">Actions</th>
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

            <!-- USER ACCOUNT BADGE (CLICKABLE FOR SUPER ADMIN) -->
            <td>
              <?php if (!empty($p['user_id'])): ?>

                <span class="badge bg-success">Has Account</span>

              <?php else: ?>

                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users&patient_id=<?= $p['patient_id'] ?>"
                     class="badge bg-secondary text-decoration-none"
                     style="cursor: pointer;">
                    No Account — Create
                  </a>
                <?php else: ?>
                  <span class="badge bg-secondary">No Account</span>
                <?php endif; ?>

              <?php endif; ?>
            </td>

            <!-- ACTION BUTTONS -->
            <td>
              <div class="action-buttons">

                <!-- View -->
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?= $p['patient_id'] ?>"
                   class="btn btn-sm btn-outline-primary">
                  View
                </a>

                <!-- Delete (Super Admin Only) -->
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=<?= $p['patient_id'] ?>"
                     onclick="return confirm('Delete this patient?');"
                     class="btn btn-sm btn-danger">
                    Delete
                  </a>
                <?php endif; ?>

              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

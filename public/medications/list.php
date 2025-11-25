<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-3">Medications</h3>

  <?php if ($_SESSION['user']['role'] !== 'patient'): ?>
    <div class="mb-3">
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/add" class="btn btn-primary btn-sm">Add Medication</a>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Patient Code</th>
              <th>Drugs</th>
              <th>Start</th>
              <th>End</th>
              <th>Notes</th>
              <th>Created</th>
              <?php if ($_SESSION['user']['role'] !== 'patient'): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['patient_code'] ?? $m['patient_id']) ?></td>
                <td><?= htmlspecialchars($m['drugs'] ?? $m['regimen'] ?? '-') ?></td>
                <td><?= htmlspecialchars($m['start_date']) ?></td>
                <td><?= htmlspecialchars($m['end_date']) ?></td>
                <td><?= htmlspecialchars($m['notes']) ?></td>
                <td><?= htmlspecialchars($m['created_at'] ?? '-') ?></td>
                <?php if ($_SESSION['user']['role'] !== 'patient'): ?>
                <td>
                  <a class="btn btn-sm btn-warning" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/edit&id=<?= $m['medication_id'] ?? $m['id'] ?>">Edit</a>
                  <a class="btn btn-sm btn-danger" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/delete&id=<?= $m['medication_id'] ?? $m['id'] ?>"
                     onclick="return confirm('Delete medication?');">Delete</a>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

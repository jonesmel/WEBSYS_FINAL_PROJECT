<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="container-fluid py-3">
  <h3 class="mb-3">Medication Drugs</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <?php if ($_SESSION['user']['role'] !== 'patient'): ?>
      <div class="d-flex justify-content-end mb-3">
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/add" class="btn btn-primary btn-sm">Add Medication</a>
      </div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Patient Code</th>
              <th>Medication Drugs</th>
              <th>Start</th>
              <th>End</th>
              <th>Notes</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $m): ?>
            <tr>
              <td><?=htmlspecialchars($m['patient_id'])?></td>
              <td><?=htmlspecialchars($m['drugs'])?></td>
              <td><?=htmlspecialchars($m['start_date'])?></td>
              <td><?=htmlspecialchars($m['end_date'])?></td>
              <td><?=htmlspecialchars($m['notes'])?></td>
              <td><?=htmlspecialchars($m['created_at'])?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
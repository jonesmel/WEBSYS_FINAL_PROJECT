<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
AuthMiddleware::requireRole(['patient']);
?>

<div class="container py-4">
  <h3 class="mb-4">My Medications</h3>

  <div class="card shadow-sm p-4">
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>Drugs</th>
          <th>Start</th>
          <th>End</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['drugs']) ?></td>
          <td><?= htmlspecialchars($m['start_date']) ?></td>
          <td><?= htmlspecialchars($m['end_date']) ?></td>
          <td><?= nl2br(htmlspecialchars($m['notes'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="4" class="text-center text-muted">No medication records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index"
     class="btn btn-secondary mt-3">← Back</a>

</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

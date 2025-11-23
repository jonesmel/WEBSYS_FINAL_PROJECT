<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">My Medications</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Drugs</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Notes</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $m): ?>
            <tr>
              <td><?=htmlspecialchars($m['drugs'] ?? '-')?></td>
              <td><?=htmlspecialchars($m['start_date'] ?? '-')?></td>
              <td><?=htmlspecialchars($m['end_date'] ?? '-')?></td>
              <td><?=nl2br(htmlspecialchars($m['notes'] ?? '-'))?></td>
              <td><?=htmlspecialchars($m['created_at'] ?? '-')?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center">No medications found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

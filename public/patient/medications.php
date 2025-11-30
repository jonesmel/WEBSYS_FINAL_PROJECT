<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">My Medications</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:200px; min-width:150px;">Drugs</th>
            <th style="width:120px; min-width:100px;">Start Date</th>
            <th style="width:120px; min-width:100px;">End Date</th>
            <th style="width:300px; min-width:200px;">Notes</th>
            <th style="width:150px; min-width:120px;">Added</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $m): ?>
            <tr>
              <td class="text-center"><?=htmlspecialchars($m['drugs'] ?? '-')?></td>
              <td class="text-center"><?=htmlspecialchars($m['start_date'] ?? '-')?></td>
              <td class="text-center"><?=htmlspecialchars($m['end_date'] ?? '-')?></td>
              <td class="text-start"><?=nl2br(htmlspecialchars($m['notes'] ?? '-'))?></td>
              <td class="text-center"><?=htmlspecialchars($m['created_at'] ?? '-')?></td>
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

<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Staff Follow-up Required</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list" class="btn btn-outline-secondary">All Notifications</a>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:150px; min-width:120px;">Patient ID</th>
            <th style="width:200px; min-width:150px;">Title</th>
            <th style="width:350px; min-width:250px;">Message</th>
            <th style="width:150px; min-width:120px;">Date</th>
            <th style="width:120px; min-width:100px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $n): ?>
            <tr>
              <td class="text-center fw-bold text-primary"><?= htmlspecialchars($n['patient_id'] ?? 'N/A') ?></td>
              <td class="text-center fw-bold text-primary"><?= htmlspecialchars($n['title']) ?></td>
              <td class="text-start"><?= nl2br(htmlspecialchars($n['message'])) ?></td>
              <td class="text-center"><?= htmlspecialchars($n['created_at']) ?></td>
              <td class="text-center">
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=stafffollowup/resolve&id=<?= $n['notification_id'] ?>" class="btn btn-sm btn-success"
                   onclick="return confirm('Mark this follow-up as handled?');">Resolve</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center text-muted">No staff follow-up items.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

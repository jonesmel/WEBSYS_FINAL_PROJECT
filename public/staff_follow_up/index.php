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
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Patient ID</th>
            <th>Title</th>
            <th>Message</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $n): ?>
            <tr>
              <td><?= htmlspecialchars($n['patient_id'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($n['title']) ?></td>
              <td><?= nl2br(htmlspecialchars($n['message'])) ?></td>
              <td><?= htmlspecialchars($n['created_at']) ?></td>
              <td>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=staff_follow_up/resolve&id=<?= $n['notification_id'] ?>" class="btn btn-sm btn-success"
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

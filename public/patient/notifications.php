<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/middleware/VerifyEmailMiddleware.php';
require_once __DIR__.'/../../src/models/NotificationModel.php';
AuthMiddleware::requireRole(['patient']);
VerifyEmailMiddleware::enforce();

$uid = $_SESSION['user']['user_id'];
$rows = NotificationModel::getByUser($uid);

// Mark all notifications as read for the patient when they view the page
NotificationModel::markAllReadForUser($uid);
?>

<div class="container py-4">
  <h3 class="mb-4">My Notifications</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:200px; min-width:150px;">Title</th>
            <th style="width:400px; min-width:300px;">Message</th>
            <th style="width:150px; min-width:120px;">Date</th>
            <th style="width:100px; min-width:80px;">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($rows): foreach ($rows as $n): ?>
          <tr>
            <td class="text-center fw-bold text-primary"><?=htmlspecialchars($n['title'])?></td>
            <td class="text-start"><?=nl2br(htmlspecialchars($n['message']))?></td>
            <td class="text-center"><?=htmlspecialchars($n['created_at'])?></td>
            <td class="text-center">
              <?= $n['is_read'] ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning text-dark">Unread</span>' ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-center">No notifications found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

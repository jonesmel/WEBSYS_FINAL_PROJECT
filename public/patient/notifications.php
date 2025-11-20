<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/middleware/VerifyEmailMiddleware.php';
require_once __DIR__.'/../../src/models/NotificationModel.php';
AuthMiddleware::requireRole(['patient']);
VerifyEmailMiddleware::enforce();

$uid = $_SESSION['user']['user_id'];
$pdo = getDB();
$stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id=?");
$stmt->execute([$uid]);
$pid = $stmt->fetchColumn();

$rows = NotificationModel::getAllForPatient($pid);
?>

<div class="container py-4">
  <h3 class="mb-4">My Notifications</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Title</th>
            <th>Message</th>
            <th>Scheduled</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($rows): foreach ($rows as $n): ?>
          <tr>
            <td><?=htmlspecialchars($n['title'])?></td>
            <td><?=htmlspecialchars($n['message'])?></td>
            <td><?=$n['scheduled_at']?></td>
            <td><?=$n['is_sent'] ? '<span class="badge bg-success">Sent</span>' : '<span class="badge bg-warning text-dark">Pending</span>'?></td>
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
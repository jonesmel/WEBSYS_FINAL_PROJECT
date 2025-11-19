<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/middleware/VerifyEmailMiddleware.php';
require_once __DIR__.'/../../src/models/MedicationModel.php';
require_once __DIR__.'/../../src/models/NotificationModel.php';
AuthMiddleware::requireRole(['patient']);
VerifyEmailMiddleware::enforce();

$uid = $_SESSION['user']['user_id'];
$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.patient_id, p.patient_code FROM patients p WHERE p.user_id = ?");
$stmt->execute([$uid]);
$patient = $stmt->fetch();

if (!$patient) {
    echo "<div class='container py-4'>
            <div class='alert alert-warning'>
              Your patient profile is not yet linked. Please contact the health worker.
            </div>
          </div>";
    include __DIR__.'/../partials/footer.php';
    exit;
}

// Fetch notifications for the dashboard (next reminder)
$nstmt = $pdo->prepare("SELECT * FROM notifications WHERE patient_id=? AND is_sent=0 ORDER BY scheduled_at ASC LIMIT 1");
$nstmt->execute([$patient['patient_id']]);
$nextNotif = $nstmt->fetch();
?>

<div class="container py-4">
  <h3 class="mb-4">Patient Dashboard</h3>

  <div class="card shadow-sm p-4 mb-4">
    <h5>Your Patient Code</h5>
    <div class="fs-4 fw-bold"><?=$patient['patient_code']?></div>
  </div>

  <div class="card shadow-sm p-4">
    <h5>Next Reminder</h5>
    <?php if ($nextNotif): ?>
      <p><strong><?=htmlspecialchars($nextNotif['title']);?></strong></p>
      <p><?=htmlspecialchars($nextNotif['message']);?></p>
      <p class="text-muted">Scheduled: <?=$nextNotif['scheduled_at']?></p>
    <?php else: ?>
      <p class="text-muted">No upcoming reminders.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
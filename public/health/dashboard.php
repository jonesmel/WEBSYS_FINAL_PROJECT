<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/models/PatientModel.php';
require_once __DIR__ . '/../../src/models/NotificationModel.php';
require_once __DIR__ . '/../../src/models/ReferralModel.php';
AuthMiddleware::requireRole(['health_worker']);

$user = $_SESSION['user'];
$barangay = $_SESSION['user']['barangay_assigned'];
$patients = PatientModel::getAllByBarangay($barangay);
$total = count($patients);

$sent = count(ReferralModel::getSentByBarangay($barangay));
$incoming = count(ReferralModel::getIncomingForBarangay($barangay));
$received = count(ReferralModel::getReceivedByBarangay($barangay));

$notifs = NotificationModel::getByBarangay($user['barangay_assigned']);
$pending = array_filter($notifs, fn($n) => $n['is_sent'] == 0);
?>

<div class="container py-4">
  <h3 class="mb-4">Health Worker Dashboard</h3>

  <div class="mb-3">
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_csv" class="btn btn-outline-primary btn-sm">
        Export Patients CSV
    </a>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center">
        <h5>Incoming Referrals</h5>
        <div class="display-6 fw-bold"><?= $incoming ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center">
        <h5>Sent Referrals</h5>
        <div class="display-6 fw-bold"><?= $sent ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center">
        <h5>Received Referrals</h5>
        <div class="display-6 fw-bold"><?= $received ?></div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center">
        <h5>Total Patients in Barangay</h5>
        <div class="display-6 fw-bold"><?= $total ?></div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card shadow-sm p-3">
        <h5>Pending Follow-ups & Notifications</h5>
        <?php if (empty($pending)): ?>
          <p class="text-muted mb-0">No pending follow-ups.</p>
        <?php else: ?>
        <div class="table-responsive mt-2">
          <table class="table table-bordered table-sm align-middle">
            <thead>
              <tr>
                <th>Patient Code</th>
                <th>Type</th>
                <th>Scheduled</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending as $p): ?>
              <tr>
                <td><?=htmlspecialchars($p['patient_id'])?></td>
                <td><?=htmlspecialchars($p['notification_type'])?></td>
                <td><?=htmlspecialchars($p['scheduled_at'])?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
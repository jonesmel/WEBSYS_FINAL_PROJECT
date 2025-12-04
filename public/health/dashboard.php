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

$notifs = NotificationModel::getByUser($_SESSION['user']['user_id']);
// For health workers, show both unsent notifications AND sent health worker alerts that are unread
$unsent = array_filter($notifs, fn($n) => $n['is_sent'] == 0);
$sentAlerts = array_filter($notifs, fn($n) =>
    $n['is_sent'] == 1 &&
    $n['is_read'] == 0 &&
    $n['type'] === 'health_worker_alert'
);
$pending = array_merge($unsent, $sentAlerts);
?>

<div class="container py-4">
  <h3 class="mb-4">Health Worker Dashboard</h3>

  <div class="mb-3">
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_csv" class="btn btn-outline-primary btn-sm">
        Export Patient Details (CSV)
    </a>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_pdf" class="btn btn-outline-danger btn-sm ms-2">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export Patient Details (PDF)
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
        <?php
        // Debug: Show all notifications for this user
        echo "<!-- DEBUG: " . count($notifs) . " notifications found -->";
        echo "<!-- DEBUG: " . count($unsent) . " unsent, " . count($sentAlerts) . " sent alerts -->";
        foreach($notifs as $n) {
          echo "<!-- NOTIF: type={$n['type']} sent={$n['is_sent']} read={$n['is_read']} title={$n['title']} -->";
        }
        ?>
        <?php if (empty($pending)): ?>
          <p class="text-muted mb-0">No pending follow-ups.</p>
        <?php else: ?>
        <div class="table-responsive mt-2">
          <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr style="text-align: center;">
                <th>Alert</th>
                <th>Type</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending as $p): ?>
              <?php
                // Get patient info if available
                $patientCode = '';
                if (!empty($p['patient_id'])) {
                  $pdo = getDB();
                  $stmt = $pdo->prepare("SELECT patient_code FROM patients WHERE patient_id = ?");
                  $stmt->execute([$p['patient_id']]);
                  $patient = $stmt->fetch();
                  $patientCode = $patient ? $patient['patient_code'] : $p['patient_id'];
                }
              ?>
              <tr>
                <td class="text-center"><strong><?=$p['title']?></strong><?php if(!empty($patientCode)): ?> <em>(Patient: <?=$patientCode?>)</em><?php endif; ?></td>
                <td class="text-center"><?php if($p['type'] === 'health_worker_alert'): ?><span class="badge bg-warning">Action Required</span><?php else: ?><?=$p['type']?><?php endif; ?></td>
                <td class="text-center"><?=$p['scheduled_at'] ?: date('M j, Y', strtotime($p['created_at']))?></td>
                <td class="text-center">
                  <?php if($p['link']): ?>
                    <a href="<?=$p['link']?>" class="btn btn-sm btn-primary">View</a>
                  <?php endif; ?>
                </td>
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

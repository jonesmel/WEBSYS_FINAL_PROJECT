<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/middleware/VerifyEmailMiddleware.php';
require_once __DIR__.'/../../src/models/ReferralModel.php';

AuthMiddleware::requireRole(['patient']);
VerifyEmailMiddleware::enforce();

$uid = $_SESSION['user']['user_id'];
$pdo = getDB();

// Get patient_id of logged-in patient
$stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$stmt->execute([$uid]);
$pid = $stmt->fetchColumn();

// Get all referrals for the patient
$refs = ReferralModel::getByPatient($pid);
?>

<div class="container py-4">
  <h3 class="mb-4">My Referrals</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Referral Code</th>
            <th>Date</th>
            <th>Referring Barangay</th>
            <th>Status</th>
            <th width="150">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($refs): foreach ($refs as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['referral_code']) ?></td>
            <td><?= htmlspecialchars($r['referral_date']) ?></td>
            <td><?= htmlspecialchars($r['referring_unit']) ?></td>
            <td><?= ucfirst($r['referral_status'] ?? 'pending') ?></td>

            <td>
              <a target="_blank"
                 href="/WEBSYS_FINAL_PROJECT/public/?route=referral/print&id=<?= $r['referral_id'] ?>"
                 class="btn btn-sm btn-outline-primary">
                 Open PDF
              </a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted">No referrals found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:900px;">
  <div class="d-flex justify-content-between mb-3">
    <h3>Referral Details</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/print&id=<?= $ref['referral_id'] ?>" 
       class="btn btn-outline-primary" target="_blank">Print PDF</a>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/index" class="btn btn-secondary">← Back</a>
  </div>

  <div class="card shadow-sm p-4">

    <h5 class="text-primary">Referral Information</h5>
    <p><strong>Referral Code:</strong> <?= $ref['referral_code'] ?></p>
    <p><strong>Date:</strong> <?= $ref['referral_date'] ?></p>

    <h5 class="text-primary mt-4">Patient</h5>
    <p><strong>Patient:</strong> <?= htmlspecialchars($ref['name'] ?? '') ?> (<?= $ref['patient_code'] ?>)</p>
    <p><strong>TB Case Number:</strong> <?= $ref['patient_tb_case'] ?></p>

    <h5 class="text-primary mt-4">Sender (Referring Barangay)</h5>
    <p><strong>Barangay:</strong> <?= htmlspecialchars($ref['referring_unit']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($ref['referring_email']) ?></p>
    <p><strong>Telephone:</strong> <?= htmlspecialchars($ref['referring_tel']) ?></p>

    <h5 class="text-primary mt-4">Receiving Barangay</h5>
    <p><strong>Barangay:</strong> <?= htmlspecialchars($ref['receiving_barangay']) ?></p>

    <h5 class="text-primary mt-4">Details</h5>
    <p><strong>Reason:</strong><br><?= nl2br(htmlspecialchars($ref['reason_for_referral'])) ?></p>
    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($ref['details'])) ?></p>

    <?php if ($ref['referral_status'] === 'received'): ?>
      <h5 class="text-primary mt-4">Receiving Unit</h5>
      <p><strong>Officer:</strong> <?= htmlspecialchars($ref['receiving_officer']) ?></p>
      <p><strong>Unit:</strong> <?= htmlspecialchars($ref['receiving_unit']) ?></p>
      <p><strong>Date Received:</strong> <?= $ref['date_received'] ?></p>
      <p><strong>Action Taken:</strong><br><?= nl2br(htmlspecialchars($ref['action_taken'])) ?></p>
      <p><strong>Remarks:</strong><br><?= nl2br(htmlspecialchars($ref['remarks'])) ?></p>
    <?php endif; ?>

    <div class="mt-4">
      <?php if (
          ($ref['referral_status'] !== 'received') && 
          (
              $_SESSION['user']['role'] === 'super_admin' || 
              $ref['created_by'] == $_SESSION['user']['user_id']
          )
      ): ?>
          <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/edit&id=<?= $ref['referral_id'] ?>" 
            class="btn btn-warning">Edit</a>
      <?php endif; ?>

      <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/delete&id=<?= $ref['referral_id'] ?>"
           class="btn btn-danger" onclick="return confirm('Delete referral?');">Delete</a>
      <?php endif; ?>

      <?php if (
          $_SESSION['user']['role'] === 'health_worker' &&
          $ref['receiving_barangay'] === $_SESSION['user']['barangay_assigned'] &&
          $ref['referral_status'] !== 'received'
      ): ?>
          <a class="btn btn-success"
            href="/WEBSYS_FINAL_PROJECT/public/?route=referral/receive&id=<?= $ref['referral_id'] ?>">
            Receive Referral
          </a>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

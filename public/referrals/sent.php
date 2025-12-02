<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Sent Referrals</h3>

  <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/create" class="btn btn-success mb-3">
    + Create Referral
  </a>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:150px; min-width:120px;">Referral Code</th>
            <th style="width:200px; min-width:180px;">Patient (Name/Code)</th>
            <th style="width:150px; min-width:120px;">Receiving Barangay</th>
            <th style="width:100px; min-width:80px;">Status</th>
            <th style="width:120px; min-width:100px;">Date</th>
            <th style="width:100px; min-width:80px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td class="text-center fw-bold text-primary"><?= htmlspecialchars($r['referral_code']) ?></td>
              <td class="text-center fw-bold text-success">
                <?= htmlspecialchars($r['name'] ?? '') ?> (<?= htmlspecialchars($r['patient_code']) ?>)
              </td>
              <td class="text-center"><?= htmlspecialchars($r['receiving_barangay']) ?></td>
              <td class="text-center">
                <?php if ($r['referral_status'] === 'received'): ?>
                    <span class="badge bg-success">Received</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= htmlspecialchars($r['referral_date']) ?></td>
              <td class="text-center">
                <a class="btn btn-sm btn-primary"
                   href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $r['referral_id'] ?>">View</a>
                <?php if ($r['referral_status'] !== 'received'): ?>
                    <a class="btn btn-sm btn-warning"
                      href="/WEBSYS_FINAL_PROJECT/public/?route=referral/edit&id=<?= $r['referral_id'] ?>">Edit</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center">No referrals sent.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

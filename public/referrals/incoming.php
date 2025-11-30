<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Incoming Referrals</h3>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:150px; min-width:120px;">Referral Code</th>
            <th style="width:120px; min-width:100px;">Patient Code</th>
            <th style="width:150px; min-width:120px;">Sender Barangay</th>
            <th style="width:100px; min-width:80px;">Status</th>
            <th style="width:120px; min-width:100px;">Date</th>
            <th style="width:150px; min-width:130px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td class="text-center fw-bold text-primary"><?= htmlspecialchars($r['referral_code']) ?></td>
              <td class="text-center fw-bold text-success"><?= htmlspecialchars($r['patient_code']) ?></td>
              <td class="text-center fw-bold text-info"><?= htmlspecialchars($r['referring_unit']) ?></td>
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

                <?php if (($r['referral_status'] ?? '') !== 'received'): ?>
                  <a class="btn btn-sm btn-success"
                     href="/WEBSYS_FINAL_PROJECT/public/?route=referral/receive&id=<?= $r['referral_id'] ?>">Receive</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center">No incoming referrals.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

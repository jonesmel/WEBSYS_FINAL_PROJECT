<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Received Referrals</h3>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:150px; min-width:120px;">Referral Code</th>
            <th style="width:200px; min-width:180px;">Patient (Name/Code)</th>
            <th style="width:150px; min-width:120px;">Sender Barangay</th>
            <th style="width:130px; min-width:110px;">Date Received</th>
            <th style="width:100px; min-width:80px;">Status</th>
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
              <td class="text-center fw-bold text-info"><?= htmlspecialchars($r['referring_unit']) ?></td>

              <td class="text-center"><?= htmlspecialchars($r['date_received']) ?></td>

              <td class="text-center">
                <span class="badge bg-success">Received</span>
              </td>

              <td class="text-center">
                <a class="btn btn-sm btn-primary"
                   href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $r['referral_id'] ?>">View</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center">No received referrals.</td></tr>
          <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

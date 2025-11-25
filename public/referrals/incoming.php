<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Incoming Referrals</h3>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>Referral Code</th>
            <th>Patient Code</th>
            <th>Sender Barangay</th>
            <th>Status</th>
            <th>Date</th>
            <th width="150">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['referral_code']) ?></td>
              <td><?= htmlspecialchars($r['patient_code']) ?></td>
              <td><?= htmlspecialchars($r['referring_unit']) ?></td>
              <td>
                <?php if ($r['referral_status'] === 'received'): ?>
                    <span class="badge bg-success">Received</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($r['referral_date']) ?></td>
              <td>
                <a class="btn btn-sm btn-primary"
                   href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $r['referral_id'] ?>">View</a>

                <?php if (($r['referral_status'] ?? '') !== 'received'): ?>
                  <a class="btn btn-sm btn-success"
                     href="/WEBSYS_FINAL_PROJECT/public/?route=referral/receive&id=<?= $r['referral_id'] ?>">Receive</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center text-muted">No incoming referrals.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

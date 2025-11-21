<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Received Referrals</h3>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>Referral Code</th>
            <th>Patient Code</th>
            <th>Sender Barangay</th>
            <th>Date Received</th>
            <th>Status</th>
            <th width="120">Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['referral_code']) ?></td>
              <td><?= htmlspecialchars($r['patient_code']) ?></td>
              <td><?= htmlspecialchars($r['referring_unit']) ?></td>

              <td><?= htmlspecialchars($r['date_received']) ?></td>

              <td>
                <span class="badge bg-success">Received</span>
              </td>

              <td>
                <a class="btn btn-sm btn-primary"
                   href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $r['referral_id'] ?>">View</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center text-muted">No received referrals.</td></tr>
          <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

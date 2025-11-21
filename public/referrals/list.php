<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">All Referrals</h3>

  <div class="mb-3">
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/create" class="btn btn-success">
      + Create New Referral
    </a>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>Referral Code</th>
            <th>Patient Code</th>
            <th>Sender Barangay</th>
            <th>Receiving Barangay</th>
            <th>Status</th>
            <th>Date</th>
            <th width="140">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['referral_code']) ?></td>
              <td><?= htmlspecialchars($r['patient_code']) ?></td>
              <td><?= htmlspecialchars($r['referring_unit']) ?></td>
              <td><?= htmlspecialchars($r['receiving_barangay']) ?></td>
              <td>
                <?php if ($r['referral_status'] === 'received'): ?>
                    <span class="badge bg-success">Received</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($r['referral_date']) ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $r['referral_id'] ?>">View</a>
                <a class="btn btn-sm btn-danger" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/delete&id=<?= $r['referral_id'] ?>"
                   onclick="return confirm('Delete this referral?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-muted">No referrals found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

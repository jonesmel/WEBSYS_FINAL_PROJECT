<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">All Referrals</h3>

  <div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/create" class="btn btn-success">
        + Create New Referral
      </a>
    </div>

    <form class="row g-2" method="GET" action="/WEBSYS_FINAL_PROJECT/public/">
      <input type="hidden" name="route" value="referral/index">
      <div class="col-auto">
        <input name="q" class="form-control" placeholder="Search code / patient" value="<?=htmlspecialchars($_GET['q'] ?? '')?>">
      </div>
      <div class="col-auto">
        <?php
          require_once __DIR__ . '/../../src/helpers/BarangayHelper.php';
          $barangays = BarangayHelper::getAll();
        ?>
        <select name="receiving_barangay" class="form-select">
          <option value="">-- Receiving Barangay --</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?=htmlspecialchars($b)?>" <?= (isset($_GET['receiving_barangay']) && $_GET['receiving_barangay']===$b) ? 'selected' : '' ?>><?=htmlspecialchars($b)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <select name="status" class="form-select">
          <option value="">-- Status --</option>
          <option value="pending" <?= (($_GET['status'] ?? '') === 'pending') ? 'selected' : '' ?>>Pending</option>
          <option value="received" <?= (($_GET['status'] ?? '') === 'received') ? 'selected' : '' ?>>Received</option>
        </select>
      </div>
      <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
    </form>
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

<script>
document.addEventListener("DOMContentLoaded", () => {
    const brgySelect = document.querySelector("select[name='receiving_barangay']");
    if (brgySelect) createSearchableDropdown(brgySelect);
});
</script>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

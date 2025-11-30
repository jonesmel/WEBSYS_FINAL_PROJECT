<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">All Referrals</h3>

  <div class="d-flex justify-content-between align-items-end mb-3">

    <!-- Create New Referral button (aligned + same color as others) -->
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/create"
       class="btn btn-primary"
       style="height: 38px; display: flex; align-items: center;">
       Create New Referral
    </a>

    <form class="d-flex gap-3 align-items-end"
          method="GET"
          action="/WEBSYS_FINAL_PROJECT/public/"
          data-ajax="referrals"
          style="max-width: 800px;">

      <input type="hidden" name="route" value="referral/index">

      <!-- Search All Fields -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Search All Fields</label>
        <input name="q"
               class="form-control"
               placeholder="Referral code, patient code, status, dates, etc."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </div>

      <!-- Receiving Barangay -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Receiving Barangay</label>
        <?php
          require_once __DIR__ . '/../../src/helpers/BarangayHelper.php';
          $barangays = BarangayHelper::getAll();
        ?>
        <select name="barangay" class="form-select">
          <option value="">All</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"
              <?= (isset($_GET['barangay']) && $_GET['barangay'] === $b) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Referring Barangay -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Referring Barangay</label>
        <select name="referring_barangay" class="form-select">
          <option value="">All</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"
              <?= (isset($_GET['referring_barangay']) && $_GET['referring_barangay'] === $b) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Clear Button -->
      <button type="button"
              class="btn btn-secondary search-clear-btn"
              style="height: 38px;"
              onclick="clearFilters(this.closest('form'))">
        Clear
      </button>
    </form>
</div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th>Referral Code</th>
            <th>Patient Code</th>
            <th>Sender Barangay</th>
            <th>Receiving Barangay</th>
            <th>Status</th>
            <th>Date</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody class="referrals-table-body">
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td class="text-center"><?= htmlspecialchars($r['referral_code']) ?></td>
              <td class="text-center"><?= htmlspecialchars($r['patient_code']) ?></td>
              <td class="text-center"><?= htmlspecialchars($r['referring_unit']) ?></td>
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
    const brgySelect = document.querySelector("select[name='barangay']");
    if (brgySelect) createSearchableAjaxDropdown(brgySelect);
    const refBrgySelect = document.querySelector("select[name='referring_barangay']");
    if (refBrgySelect) createSearchableAjaxDropdown(refBrgySelect);
});
</script>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

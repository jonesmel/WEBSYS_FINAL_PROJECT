<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-3">Contact Tracing</h3>

  <div class="d-flex justify-content-between align-items-end mb-3">

    <!-- Add Contact button aligned with inputs -->
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/add"
       class="btn btn-primary"
       style="height: 38px; display: flex; align-items: center;">
       Add Contact
    </a>

    <!-- SEARCH + FILTERS -->
    <form class="d-flex gap-3 align-items-end"
          method="GET"
          action="/WEBSYS_FINAL_PROJECT/public/"
          data-ajax="contacts"
          style="max-width: 600px;">

      <input type="hidden" name="route" value="contact/list">

      <!-- Search All Fields -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Search All Fields</label>
        <input name="q"
               value="<?=htmlspecialchars($_GET['q'] ?? '')?>"
               class="form-control"
               placeholder="Contact code, patient name/code, age, sex, status, relationship, etc.">
      </div>

      <!-- Barangay Filter -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Filter by Barangay</label>
        <?php require_once __DIR__ . '/../../src/helpers/BarangayHelper.php'; 
              $barangays = BarangayHelper::getAll(); ?>
        <select name="barangay" class="form-select">
          <option value="">All Barangays</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?=htmlspecialchars($b)?>"
              <?= (isset($_GET['barangay']) && $_GET['barangay'] === $b) ? 'selected' : '' ?>>
              <?=htmlspecialchars($b)?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

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
      <table class="table table-striped table-bordered table-hover align-middle">
        <thead>
          <tr style="text-align: center;">
            <th>Contact Code</th>
            <th>Barangay</th>
            <th>Linked Patient (Code)</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Status</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody class="contacts-table-body">
        <?php if (empty($rows)): ?>
        <tr><td colspan="7" class="text-center text-muted">No contacts found.</td></tr>
        <?php else: ?>
        <?php foreach ($rows as $c): ?>
          <tr>
            <td class="text-center"><?=htmlspecialchars($c['contact_code'])?></td>
            <td class="text-center"><?=htmlspecialchars($c['barangay'])?></td>
            <td class="text-center">
              <?php if (!empty($c['patient_id'])): ?>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?=$c['patient_id']?>"
                  class="btn btn-sm btn-link">
                  <?= htmlspecialchars($c['name'] ?? '') ?> (<?= htmlspecialchars($c['patient_code']) ?>)
                </a>
              <?php else: ?>
                <em class="text-muted small">None</em>
              <?php endif; ?>
            </td>
            <td class="text-center"><?=$c['age']?></td>
            <td class="text-center"><?=$c['sex']?></td>
            <td class="text-center"><?=$c['status']?></td>
            <td class="text-center">
              <?php if ($c['status'] !== 'converted_patient'): ?>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/convert&id=<?=$c['contact_id']?>" class="btn btn-sm btn-outline-warning">Convert</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const brgySelect = document.querySelector("select[name='barangay']");
    if (brgySelect) {
        createSearchableDropdown(brgySelect);
    }
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

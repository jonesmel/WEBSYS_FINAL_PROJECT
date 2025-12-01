<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-3">Patients</h3>

    <div class="d-flex justify-content-between align-items-end mb-3">

      <!-- Add Patient button (aligned vertically with inputs) -->
      <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/create"
          class="btn btn-primary"
          style="height:38px; display:flex; align-items:center;">
          Add Patient
        </a>
      <?php else: ?>
        <div></div>
      <?php endif; ?>

      <!-- SEARCH + FILTERS -->
      <form class="d-flex gap-3 align-items-end"
            method="GET"
            action="/WEBSYS_FINAL_PROJECT/public/"
            data-ajax="patients"
            style="max-width: 600px;">

        <input type="hidden" name="route" value="patient/index">

        <!-- Search All Fields -->
        <div class="d-flex flex-column" style="width: 250px;">
          <label class="form-label mb-1">Search All Fields</label>
          <input name="q"
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                class="form-control"
                placeholder="Patient code, TB case, age, sex, etc.">
        </div>

        <!-- Barangay Filter -->
        <div class="d-flex flex-column" style="width: 250px;">
          <label class="form-label mb-1">Filter by Barangay</label>
          <select name="barangay" class="form-select" data-placeholder="Search barangay...">
            <option value="">All Barangays</option>
            <?php foreach ($barangays as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"
                <?= (isset($_GET['barangay']) && $_GET['barangay'] === $b) ? 'selected' : '' ?>>
                <?= htmlspecialchars($b) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Clear Button -->
        <button type="button"
                class="btn btn-secondary search-clear-btn"
                style="height:38px;"
                onclick="clearFilters(this.closest('form'))">
          Clear
        </button>

      </form>
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:180px; min-width:140px;">Patient Code</th>
            <th style="width:180px; min-width:140px;">Name</th>
            <th style="width:140px; min-width:100px;">Barangay</th>
            <th style="width:60px; min-width:50px;">Age</th>
            <th style="width:60px; min-width:45px;">Sex</th>
            <th style="width:160px; min-width:120px;">Case #</th>
            <th style="width:140px; min-width:120px;">PhilHealth ID</th>
            <th style="width:120px; min-width:100px;">User Account</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody class="patients-table-body">
          <?php foreach ($patients as $p): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($p['patient_code']) ?></td>
            <td class="text-center"><?= htmlspecialchars($p['name'] ?? '') ?></td>
            <td class="text-center"><?= htmlspecialchars($p['barangay']) ?></td>
            <td class="text-center"><?= $p['age'] ?></td>
            <td class="text-center"><?= $p['sex'] ?></td>
            <td class="text-center"><?= htmlspecialchars($p['tb_case_number']) ?></td>
            <td class="text-center">
              <?php if (!empty($p['philhealth_id'])): ?>
                <?= htmlspecialchars(substr($p['philhealth_id'], 0, 2) . '-' . substr($p['philhealth_id'], 2, 9) . '-' . substr($p['philhealth_id'], 11, 1)) ?>
              <?php else: ?>
                <em class="text-muted small">-</em>
              <?php endif; ?>
            </td>

            <td class="text-center">
              <?php if (!empty($p['user_id'])): ?>
                <span class="badge bg-success">Has Account</span>
              <?php else: ?>
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users&patient_id=<?= $p['patient_id'] ?>"
                     class="badge bg-secondary text-decoration-none" style="cursor: pointer;">
                    No Account — Create
                  </a>
                <?php else: ?>
                  <span class="badge bg-secondary">No Account</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>

            <td class="text-center">
              <div class="action-buttons d-flex justify-content-center gap-1">
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?= $p['patient_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=<?= $p['patient_id'] ?>"
                     onclick="return confirm('Delete this patient?');"
                     class="btn btn-sm btn-danger">Delete</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
window.USER_ROLE = "<?= $_SESSION['user']['role'] ?>";

document.addEventListener("DOMContentLoaded", () => {
    // Initialize searchable barangay dropdown (AJAX version)
    const brgySelect = document.querySelector("select[name='barangay']");
    if (brgySelect && typeof createSearchableAjaxDropdown === 'function') {
        createSearchableAjaxDropdown(brgySelect, 'Search barangay...');
    } else if (brgySelect && typeof createSearchableDropdown === 'function') {
        // fallback if your app.js uses a different name
        createSearchableDropdown(brgySelect);
    }

    // If you relied on the global clearFilters helper (Option B), ensure it exists.
    // If not present, define it here so the inline onclick works.
    if (typeof window.clearFilters !== 'function') {
      window.clearFilters = function(form) {
        if (!form) return;
        const qInput = form.querySelector("input[name='q']");
        const brgyWrapper = form.querySelector('.position-relative');
        const brgyInput = brgyWrapper ? brgyWrapper.querySelector('input.brgy-search-input') : null;
        const realSelect = form.querySelector("select[name='barangay']");

        if (qInput) qInput.value = '';
        if (brgyInput) brgyInput.value = '';
        if (realSelect) {
          realSelect.value = '';
          realSelect.removeAttribute('data-typed-value');
          // trigger change in case something listens to select change
          realSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Trigger AJAX refresh: dispatch input event on qInput
        if (qInput) qInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

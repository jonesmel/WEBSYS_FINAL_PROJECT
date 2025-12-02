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
                placeholder="Patient name/code, TB case, philhealth, etc.">
        </div>

        <!-- Barangay Filter -->
        <div class="d-flex flex-column" style="width: 200px;">
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

        <!-- Treatment Outcome Filter -->
        <div class="d-flex flex-column" style="width: 200px;">
          <label class="form-label mb-1">Filter by Status</label>
          <select name="treatment_outcome" class="form-select">
            <option value="">All Status</option>
            <option value="active" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="cured" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'cured') ? 'selected' : '' ?>>Cured</option>
            <option value="treatment_completed" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'treatment_completed') ? 'selected' : '' ?>>Treatment Completed</option>
            <option value="died" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'died') ? 'selected' : '' ?>>Died</option>
            <option value="lost_to_followup" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'lost_to_followup') ? 'selected' : '' ?>>Lost to Follow-Up</option>
            <option value="failed" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'failed') ? 'selected' : '' ?>>Failed</option>
            <option value="transferred_out" <?= (isset($_GET['treatment_outcome']) && $_GET['treatment_outcome'] === 'transferred_out') ? 'selected' : '' ?>>Transferred Out</option>
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
            <th style="width:100px; min-width:80px;">Status</th>
            <th style="width:140px; min-width:120px;">PhilHealth ID</th>
            <th style="width:120px; min-width:100px;">User Account</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody class="patients-table-body">
          <?php if (empty($patients)): ?>
          <tr><td colspan="10" class="text-center text-muted">No patients found.</td></tr>
          <?php else: ?>
          <?php foreach ($patients as $p): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($p['patient_code']) ?></td>
            <td class="text-center"><?= htmlspecialchars($p['name'] ?? '') ?></td>
            <td class="text-center"><?= htmlspecialchars($p['barangay']) ?></td>
            <td class="text-center"><?= $p['age'] ?></td>
            <td class="text-center"><?= $p['sex'] ?></td>
            <td class="text-center"><?= htmlspecialchars($p['tb_case_number']) ?></td>
            <td class="text-center">
              <?php
              $outcomes = [
                'active' => 'Active',
                'cured' => 'Cured',
                'treatment_completed' => 'Completed',
                'died' => 'Died',
                'lost_to_followup' => 'Lost',
                'failed' => 'Failed',
                'transferred_out' => 'Transferred'
              ];
              $status = $outcomes[$p['treatment_outcome']] ?? $p['treatment_outcome'];
              $badgeClass = '';
              if ($p['treatment_outcome'] === 'active') $badgeClass = 'bg-primary';
              elseif (in_array($p['treatment_outcome'], ['cured', 'treatment_completed'])) $badgeClass = 'bg-success';
              elseif ($p['treatment_outcome'] === 'died') $badgeClass = 'bg-danger';
              else $badgeClass = 'bg-warning';
              ?>
              <span class="badge <?= $badgeClass ?> text-white"><?= htmlspecialchars($status) ?></span>
            </td>
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
          <?php endif; ?>
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

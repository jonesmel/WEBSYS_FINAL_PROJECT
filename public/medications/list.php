<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-3">Medications</h3>

  <div class="d-flex justify-content-between align-items-end mb-3">
    <!-- Add Medication button (aligned w/ search boxes) -->
    <?php if ($_SESSION['user']['role'] !== 'patient'): ?>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/add"
         class="btn btn-primary"
         style="height: 38px; display: flex; align-items: center;">
        Add Medication
      </a>
    <?php else: ?>
      <div></div>
    <?php endif; ?>

    <!-- SEARCH + FILTERS -->
    <form class="d-flex gap-3 align-items-end"
          method="GET"
          action="/WEBSYS_FINAL_PROJECT/public/"
          data-ajax="medications"
          style="max-width: 600px;">

      <input type="hidden" name="route" value="medication/list">

      <!-- Search All Fields -->
      <div class="d-flex flex-column" style="width: 250px;">
        <label class="form-label mb-1">Search All Fields</label>
      <input name="q"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
               class="form-control"
               placeholder="Patient name/code, drugs, notes, dates, etc.">
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

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
          <tr style="text-align: center;">
              <th style="width:200px; min-width:150px;">Patient (Code)</th>
              <th style="width:250px; min-width:200px;">Drugs</th>
              <th style="width:120px; min-width:100px;">Start</th>
              <th style="width:120px; min-width:100px;">End</th>
              <th style="width:120px; min-width:100px;">Compliance</th>
              <th style="width:150px; min-width:120px;">Notes</th>
              <th style="width:120px; min-width:100px;">Created</th>
              <?php if ($_SESSION['user']['role'] !== 'patient'): ?><th style="width:140px; min-width:120px;">Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody class="medications-table-body">
            <?php foreach ($rows as $m): ?>
              <tr>
                <td class="text-center fw-bold text-primary">
                  <?= htmlspecialchars($m['name'] ?? '') ?> (<?= htmlspecialchars($m['patient_code'] ?? $m['patient_id']) ?>)
                </td>
                <td class="text-center"><?= htmlspecialchars($m['drugs'] ?? $m['regimen'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($m['start_date']) ?></td>
                <td class="text-center"><?= htmlspecialchars($m['end_date']) ?></td>
                <td class="text-center">
                  <span class="badge
                    <?=$m['compliance_status'] === 'taken' ? 'bg-success' :
                       ($m['compliance_status'] === 'missed' ? 'bg-danger' :
                       ($m['compliance_status'] === 'partial' ? 'bg-warning' : 'bg-secondary'))?>">
                    <?=ucfirst($m['compliance_status'] ?? 'pending')?>
                  </span>
                </td>
                <td class="text-center"><?= htmlspecialchars($m['notes']) ?></td>
                <td class="text-center"><?= htmlspecialchars($m['created_at'] ?? '-') ?></td>
                <?php if ($_SESSION['user']['role'] !== 'patient'): ?>
                <td class="text-center">
                  <a class="btn btn-sm btn-warning" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/edit&id=<?= $m['medication_id'] ?? $m['id'] ?>">Edit</a>
                  <a class="btn btn-sm btn-danger" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/delete&id=<?= $m['medication_id'] ?? $m['id'] ?>"
                     onclick="return confirm('Delete medication?');">Delete</a>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

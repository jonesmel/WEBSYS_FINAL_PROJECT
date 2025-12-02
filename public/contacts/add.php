<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);

$barangays = BarangayHelper::getAll();
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Add Contact</h3>

  <div class="card shadow-sm p-4">
    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=contact/add">

      <div class="mb-3">
        <label class="form-label">Linked Patient</label>
        <select name="patient_id" class="form-select" required>
          <option value="">Select Patient</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?=$p['patient_id']?>">
              <?=$p['name'] ?? ''?> (<?=$p['patient_code']?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Age</label>
          <input type="number" name="age" class="form-control" min="0" max="120">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Sex</label>
          <select name="sex" class="form-select">
            <option value="Unknown">Unknown</option>
            <option value="M">M</option>
            <option value="F">F</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Relationship</label>
          <input type="text" name="relationship" class="form-control">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Barangay</label>
        <select name="barangay" class="form-select" required>
          <option value="">-- Select --</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i>Add Contact
        </button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/list" class="btn btn-secondary">
          <i class="bi bi-x me-1"></i>Cancel
        </a>
      </div>
    </form>
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

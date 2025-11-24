<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Add Patient</h3>
  <div class="card shadow-sm p-4">
    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=patient/create">

      <div class="mb-3">
        <label class="form-label">Patient Code (leave blank to auto-generate)</label>
        <input type="text" name="patient_code" class="form-control" maxlength="50">
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
      <?php if ($user && $user['role'] === 'health_worker'): ?>
        <div class="mb-3">
          <label class="form-label">Barangay (assigned)</label>
          <input type="text" class="form-control" value="<?=htmlspecialchars($user['barangay_assigned'])?>" disabled>
          <input type="hidden" name="barangay" value="<?=htmlspecialchars($user['barangay_assigned'])?>">
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Barangay</label>
          <input type="text" name="barangay" class="form-control" required>
        </div>
      <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Bacteriological Status</label>
          <select name="bacteriological_status" class="form-select">
            <option>Unknown</option><option>BC</option><option>CD</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Anatomical Site</label>
          <select name="anatomical_site" class="form-select">
            <option>Unknown</option><option>P</option><option>EP</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Drug Susceptibility</label>
          <select name="drug_susceptibility" class="form-select">
            <option>Unknown</option><option>DS</option><option>DR</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Treatment History</label>
        <select name="treatment_history" class="form-select">
          <option>Unknown</option><option>New</option><option>Retreatment</option>
        </select>
      </div>

      <button class="btn btn-primary">Save</button>
    </form>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
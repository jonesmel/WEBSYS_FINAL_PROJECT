<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);

$user = $_SESSION['user'];
$barangays = BarangayHelper::getAll();
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Add Patient</h3>

  <div class="card shadow-sm p-4">
    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=patient/create">

      <input type="hidden" name="patient_code" value="">

      <div class="mb-3">
        <label class="form-label">Patient Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Age</label>
          <input type="number" name="age" class="form-control">
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Sex</label>
          <select name="sex" class="form-select">
            <option>Unknown</option>
            <option>M</option>
            <option>F</option>
          </select>
        </div>
      </div>

      <?php if ($user['role'] === 'health_worker'): ?>
        <div class="mb-3">
          <label class="form-label">Barangay</label>
          <input class="form-control" value="<?= htmlspecialchars($user['barangay_assigned']) ?>" disabled>
          <input type="hidden" name="barangay" value="<?= htmlspecialchars($user['barangay_assigned']) ?>">
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Barangay</label>
          <select name="barangay" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ($barangays as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">PhilHealth ID</label>
        <input type="text" name="philhealth_id" class="form-control" placeholder="12-digit PhilHealth number">
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
          <option>Unknown</option>
          <option>New</option>
          <option>Retreatment</option>
        </select>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i>Create Patient
        </button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index" class="btn btn-secondary">
          <i class="bi bi-x me-1"></i>Cancel
        </a>
      </div>

    </form>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';
require_once __DIR__.'/../../src/models/ReferralModel.php';

AuthMiddleware::requireRole(['super_admin','health_worker']);
$isAdmin = $_SESSION['user']['role'] === 'super_admin';

$barangays = BarangayHelper::getAll();
$hasPendingRef = ReferralModel::patientHasPending($patient['patient_id']);
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Edit Patient</h3>

  <?php if ($hasPendingRef): ?>
    <div class="alert alert-warning">
      <strong>Note:</strong> Patient has pending referrals — barangay locked.
    </div>
  <?php endif; ?>

  <div class="card shadow-sm p-4">
    <form method="POST"
          action="/WEBSYS_FINAL_PROJECT/public/?route=patient/edit&id=<?= $patient['patient_id'] ?>">

      <div class="mb-3">
        <label class="form-label">Patient Code</label>
        <input class="form-control" value="<?= htmlspecialchars($patient['patient_code']) ?>" disabled>
        <input type="hidden" name="patient_code" value="<?= htmlspecialchars($patient['patient_code']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">TB Case Number</label>
        <input class="form-control" value="<?= htmlspecialchars($patient['tb_case_number']) ?>" disabled>
        <input type="hidden" name="tb_case_number" value="<?= htmlspecialchars($patient['tb_case_number']) ?>">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Age</label>
          <input type="number" name="age" class="form-control"
                 value="<?= htmlspecialchars($patient['age']) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Sex</label>
          <select name="sex" class="form-select">
            <option <?= $patient['sex']=='Unknown'?'selected':'' ?>>Unknown</option>
            <option value="M" <?= $patient['sex']=='M'?'selected':'' ?>>M</option>
            <option value="F" <?= $patient['sex']=='F'?'selected':'' ?>>F</option>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Barangay</label>

          <?php if ($isAdmin && !$hasPendingRef): ?>
            <select name="barangay" class="form-select" required>
              <?php foreach ($barangays as $b): ?>
                <option value="<?= htmlspecialchars($b) ?>"
                        <?= $patient['barangay']==$b?'selected':'' ?>>
                    <?= htmlspecialchars($b) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input class="form-control"
                   value="<?= htmlspecialchars($patient['barangay']) ?>" readonly>
            <input type="hidden" name="barangay"
                   value="<?= htmlspecialchars($patient['barangay']) ?>">
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input name="contact_number" class="form-control"
               value="<?= htmlspecialchars($patient['contact_number']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Bacteriological Status</label>
        <select name="bacteriological_status" class="form-select">
          <option <?= $patient['bacteriological_status']=='Unknown'?'selected':'' ?>>Unknown</option>
          <option value="BC" <?= $patient['bacteriological_status']=='BC'?'selected':'' ?>>BC</option>
          <option value="CD" <?= $patient['bacteriological_status']=='CD'?'selected':'' ?>>CD</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Anatomical Site</label>
        <select name="anatomical_site" class="form-select">
          <option <?= $patient['anatomical_site']=='Unknown'?'selected':'' ?>>Unknown</option>
          <option value="P" <?= $patient['anatomical_site']=='P'?'selected':'' ?>>P</option>
          <option value="EP" <?= $patient['anatomical_site']=='EP'?'selected':'' ?>>EP</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Drug Susceptibility</label>
        <select name="drug_susceptibility" class="form-select">
          <option <?= $patient['drug_susceptibility']=='Unknown'?'selected':'' ?>>Unknown</option>
          <option value="DS" <?= $patient['drug_susceptibility']=='DS'?'selected':'' ?>>DS</option>
          <option value="DR" <?= $patient['drug_susceptibility']=='DR'?'selected':'' ?>>DR</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Treatment History</label>
        <select name="treatment_history" class="form-select">
          <option <?= $patient['treatment_history']=='Unknown'?'selected':'' ?>>Unknown</option>
          <option value="New" <?= $patient['treatment_history']=='New'?'selected':'' ?>>New</option>
          <option value="Retreatment" <?= $patient['treatment_history']=='Retreatment'?'selected':'' ?>>Retreatment</option>
        </select>
      </div>

      <div class="text-end">
        <button class="btn btn-primary">Save Changes</button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?= $patient['patient_id'] ?>"
           class="btn btn-secondary">Cancel</a>
      </div>

    </form>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

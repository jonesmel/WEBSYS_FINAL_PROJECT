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
        <label class="form-label">Patient Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($patient['name'] ?? '') ?>" required>
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
        <label class="form-label">PhilHealth ID</label>
        <input name="philhealth_id" class="form-control" placeholder="12-digit PhilHealth number"
               value="<?= htmlspecialchars($patient['philhealth_id'] ?? '') ?>">
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

      <div class="mb-3">
        <label class="form-label">Treatment Outcome</label>
        <select name="treatment_outcome" id="treatment_outcome" class="form-select">
          <option value="active" <?= $patient['treatment_outcome']=='active'?'selected':'' ?>>Active</option>
          <option value="cured" <?= $patient['treatment_outcome']=='cured'?'selected':'' ?>>Cured</option>
          <option value="treatment_completed" <?= $patient['treatment_outcome']=='treatment_completed'?'selected':'' ?>>Treatment Completed</option>
          <option value="died" <?= $patient['treatment_outcome']=='died'?'selected':'' ?>>Died</option>
          <option value="lost_to_followup" <?= $patient['treatment_outcome']=='lost_to_followup'?'selected':'' ?>>Lost to Follow-Up</option>
          <option value="failed" <?= $patient['treatment_outcome']=='failed'?'selected':'' ?>>Failed</option>
          <option value="transferred_out" <?= $patient['treatment_outcome']=='transferred_out'?'selected':'' ?>>Transferred Out</option>
        </select>
      </div>

      <div class="mb-3" id="outcome-notes-container" style="display: none;">
        <label class="form-label" id="outcome-notes-label">Additional Notes</label>
        <input type="text" name="outcome_notes" class="form-control" value="<?= htmlspecialchars($patient['outcome_notes'] ?? '') ?>" placeholder="Enter details...">
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i>Save Changes
        </button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?= $patient['patient_id'] ?>" class="btn btn-secondary">
          <i class="bi bi-x me-1"></i>Cancel
        </a>
      </div>

    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const outcomeSelect = document.getElementById('treatment_outcome');
  const notesContainer = document.getElementById('outcome-notes-container');
  const notesLabel = document.getElementById('outcome-notes-label');

  const labels = {
    'died': 'Cause of Death',
    'failed': 'Reason for Failure',
    'lost_to_followup': 'Circumstances of Loss',
    'transferred_out': 'Transfer Destination'
  };

  function toggleNotes() {
    const value = outcomeSelect.value;
    if (labels[value]) {
      notesContainer.style.display = 'block';
      notesLabel.textContent = labels[value];
    } else {
      notesContainer.style.display = 'none';
      notesLabel.textContent = 'Additional Notes';
    }
  }

  outcomeSelect.addEventListener('change', toggleNotes);
  // Initialize on page load
  toggleNotes();
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

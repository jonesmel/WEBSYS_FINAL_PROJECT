<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';

$barangays = BarangayHelper::getAll();
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Convert Contact to Patient</h3>

  <div class="card shadow-sm p-4">
    <!-- Contact Info -->
    <div class="alert alert-info">
      <strong>Contact Information:</strong><br>
      Code: <?= htmlspecialchars($contact['contact_code']) ?><br>
      Age: <?= htmlspecialchars($contact['age']) ?><br>
      Sex: <?= htmlspecialchars($contact['sex']) ?><br>
      Relationship: <?= htmlspecialchars($contact['relationship']) ?><br>
      Contact Number: <?= htmlspecialchars($contact['contact_number']) ?><br>
      Barangay: <?= htmlspecialchars($contact['barangay']) ?><br>
      <?php if ($linkedPatient): ?>
        <strong>Linked Patient:</strong> <?= htmlspecialchars($linkedPatient['name']) ?> (<?= htmlspecialchars($linkedPatient['patient_code']) ?>) - <?= htmlspecialchars($linkedPatient['barangay']) ?>
      <?php endif; ?>
    </div>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=contact/convert&id=<?= $contact['contact_id'] ?>">
      <h5>Patient Details</h5>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Age</label>
          <input type="number" name="age" class="form-control" min="0" max="120" value="<?= htmlspecialchars($contact['age']) ?>">
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Sex</label>
          <select name="sex" class="form-select">
            <option value="Unknown" <?= $contact['sex'] === 'Unknown' ? 'selected' : '' ?>>Unknown</option>
            <option value="M" <?= $contact['sex'] === 'M' ? 'selected' : '' ?>>M</option>
            <option value="F" <?= $contact['sex'] === 'F' ? 'selected' : '' ?>>F</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($contact['contact_number']) ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Barangay *</label>
        <input type="text" name="barangay" class="form-control" readonly required
               value="<?= htmlspecialchars($contact['barangay'] ?: ($linkedPatient['barangay'] ?? 'Unknown')) ?>">
        <small class="form-text text-muted">Barangay is set from contact tracing data and cannot be changed during conversion.</small>
      </div>

      <div class="mb-3">
        <label class="form-label">PhilHealth ID</label>
        <input type="text" name="philhealth_id" class="form-control" pattern="\d{12}" maxlength="14" placeholder="1234567890123">
      </div>

      <hr>
      <h6>Medical Information</h6>

      <div class="mb-3">
        <label class="form-label">Bacteriological Status</label>
        <select name="bacteriological_status" class="form-select">
          <option value="Unknown" selected>Unknown</option>
          <option value="BC">BC</option>
          <option value="CD">CD</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Anatomical Site</label>
        <select name="anatomical_site" class="form-select">
          <option value="Unknown" selected>Unknown</option>
          <option value="P">P</option>
          <option value="EP">EP</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Drug Susceptibility</label>
        <select name="drug_susceptibility" class="form-select">
          <option value="Unknown" selected>Unknown</option>
          <option value="DS">DS</option>
          <option value="DR">DR</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Treatment History</label>
        <select name="treatment_history" class="form-select">
          <option value="Unknown" selected>Unknown</option>
          <option value="New">New</option>
          <option value="Retreatment">Retreatment</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Convert to Patient</button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/list" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
// No scripts needed for this page since barangay is readonly
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

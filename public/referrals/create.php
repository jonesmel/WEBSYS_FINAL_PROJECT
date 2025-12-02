<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:900px;">
  <h3 class="mb-4">Create Referral</h3>

  <form method="POST" class="card shadow-sm p-4">

    <div class="mb-3">
      <label class="form-label">Select Patient</label>
        <select name="patient_id" class="form-select" required>
            <option value="">Select Patient</option>
            <?php foreach ($patients as $p): ?>
                <option value="<?= $p['patient_id'] ?>">
                    <?= htmlspecialchars($p['name'] ?? '') ?> (<?= htmlspecialchars($p['patient_code']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- REFERRING BARANGAY / UNIT -->
    <div class="mb-3">
      <label class="form-label">Referring Barangay / Unit</label>

      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'health_worker'): ?>

          <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['barangay_assigned']) ?>" disabled>
          <input type="hidden" name="referring_unit" value="<?= htmlspecialchars($_SESSION['user']['barangay_assigned']) ?>">
          <input type="hidden" name="referring_email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>">

      <?php else: ?>

          <select name="referring_unit" class="form-select" required>
              <option value="">-- Select Referring Barangay --</option>
              <?php foreach ($barangays as $b): ?>
                  <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
              <?php endforeach; ?>
          </select>

      <?php endif; ?>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Referring Contact (Tel)</label>
        <input type="text" name="referring_tel" class="form-control" placeholder="Contact number (optional)">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Referring Email</label>
        <input type="email" name="referring_email" class="form-control" placeholder="Email (optional)"
               <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'health_worker'): ?> value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" readonly <?php endif; ?>>
      </div>
    </div>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'super_admin'): ?>
    <div class="mb-3">
      <label class="form-label">Referring Address</label>
      <select name="referring_address" class="form-select">
        <option value="">-- Select Referring Address/Barangay --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php else: ?>
    <div class="mb-3">
      <label class="form-label">Referring Address</label>
      <input type="text" name="referring_address" class="form-control" placeholder="Address or notes (optional)">
    </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Referral Date</label>
      <input type="date" name="referral_date" class="form-control" required value="<?= date('Y-m-d') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Reason for Referral</label>
      <textarea name="reason_for_referral" class="form-control" rows="3" placeholder="Short reason (copy to Form 7)"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Details</label>
      <textarea name="details" class="form-control" rows="4" placeholder="More detailed description (clinical details, tests, history)"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Receiving Barangay</label>
      <select name="receiving_barangay" class="form-select" required>
        <option value="">-- Select --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-primary">
        <i class="bi bi-check-circle me-1"></i>Create Referral
      </button>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/index" class="btn btn-secondary">
        <i class="bi bi-x me-1"></i>Cancel
      </a>
    </div>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const brgySelect = document.querySelector("select[name='referring_unit']");
    if (brgySelect) createSearchableDropdown(brgySelect);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const brgySelect = document.querySelector("select[name='receiving_barangay']");
    if (brgySelect) createSearchableDropdown(brgySelect);
});
</script>


<?php require_once __DIR__.'/../partials/footer.php'; ?>

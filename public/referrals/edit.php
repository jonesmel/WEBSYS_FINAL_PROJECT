<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);

if (!isset($ref)) {
    echo "<div class='container mt-5 text-danger'>Error: Referral data missing.</div>";
    require_once __DIR__.'/../partials/footer.php';
    exit;
}
?>

<div class="container py-4" style="max-width:900px;">
  <?php \Flash::display(); ?>

  <h3 class="mb-3">Edit Referral <?= htmlspecialchars($ref['referral_code']) ?></h3>

  <div class="card shadow-sm p-4">
    <form method="POST"
          action="/WEBSYS_FINAL_PROJECT/public/?route=referral/edit&id=<?= $ref['referral_id'] ?>">

      <!-- PATIENT -->
      <div class="mb-3">
        <label class="form-label">Patient</label>
        <select name="patient_id" class="form-select" required>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['patient_id'] ?>"
              <?= ($p['patient_id'] == $ref['patient_id'] ? 'selected' : '') ?>>
              <?= htmlspecialchars($p['patient_code']) ?> (<?= htmlspecialchars($p['barangay']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- DATE + RECEIVING BARANGAY -->
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Referral Date</label>
          <input type="date"
                 name="referral_date"
                 class="form-control"
                 required
                 value="<?= htmlspecialchars(substr($ref['referral_date'], 0, 10)) ?>">
        </div>

        <div class="col-md-8 mb-3">
          <label class="form-label">Receiving Barangay</label>
          <select name="receiving_barangay" class="form-select" required>
            <option value="">Select Receiving Barangay</option>
            <?php foreach ($barangays as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"
                <?= ($b == $ref['receiving_barangay'] ? 'selected' : '') ?>>
                <?= htmlspecialchars($b) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- REFERRING INFO (READONLY LIKE CREATE PAGE) -->
      <div class="mb-3">
        <label class="form-label">Referring Barangay / Unit</label>
        <input type="text"
               class="form-control"
               value="<?= htmlspecialchars($ref['referring_unit']) ?>"
               disabled>
        <input type="hidden"
               name="referring_unit"
               value="<?= htmlspecialchars($ref['referring_unit']) ?>">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Referring Telephone</label>
          <input type="text"
                 class="form-control"
                 name="referring_tel"
                 value="<?= htmlspecialchars($ref['referring_tel']) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Referring Email</label>
          <input type="email"
                 class="form-control"
                 name="referring_email"
                 value="<?= htmlspecialchars($ref['referring_email']) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Referring Address</label>
          <input type="text"
                 class="form-control"
                 name="referring_address"
                 value="<?= htmlspecialchars($ref['referring_address']) ?>">
        </div>
      </div>

      <!-- REASON & DETAILS -->
      <div class="mb-3">
        <label class="form-label">Reason for Referral</label>
        <textarea name="reason_for_referral" class="form-control" rows="2"><?= htmlspecialchars($ref['reason_for_referral']) ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Details</label>
        <textarea name="details" class="form-control" rows="3"><?= htmlspecialchars($ref['details']) ?></textarea>
      </div>

      <button class="btn btn-primary">Update Referral</button>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?= $ref['referral_id'] ?>"
         class="btn btn-secondary ms-2">
         Cancel
      </a>

    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const brgySelect = document.querySelector("select[name='receiving_barangay']");
    if (brgySelect) createSearchableDropdown(brgySelect);
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

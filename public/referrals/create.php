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
                    <?= htmlspecialchars($p['patient_code']) ?> (<?= htmlspecialchars($p['barangay']) ?>)
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

    <div class="mb-3">
      <label class="form-label">Referring Address</label>
      <input type="text" name="referring_address" class="form-control" placeholder="Address or notes (optional)">
    </div>

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

    <button class="btn btn-primary">Submit</button>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/index" class="btn btn-secondary mt-3">← Back</a>
  </form>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

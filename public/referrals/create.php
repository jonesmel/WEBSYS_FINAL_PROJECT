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
                    <?= $p['patient_code'] ?> (<?= htmlspecialchars($p['barangay']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

        <!-- REFERRING BARANGAY / UNIT -->
    <div class="mb-3">
      <label class="form-label">Referring Barangay / Unit</label>

      <?php if ($_SESSION['user']['role'] === 'health_worker'): ?>

          <input type="text" class="form-control" value="<?= $_SESSION['user']['barangay_assigned'] ?>" disabled>
          <input type="hidden" name="referring_unit" value="<?= $_SESSION['user']['barangay_assigned'] ?>">

      <?php else: ?>

          <select name="referring_unit" class="form-select" required>
              <option value="">-- Select Referring Barangay --</option>
              <?php foreach ($barangays as $b): ?>
                  <option value="<?= $b ?>"><?= $b ?></option>
              <?php endforeach; ?>
          </select>

      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Referral Date</label>
      <input type="date" name="referral_date" class="form-control" required value="<?= date('Y-m-d') ?>">
    </div>

    <!-- referring_unit auto-filled if health worker -->

    <div class="mb-3">
      <label class="form-label">Reason for Referral</label>
      <textarea name="reason_for_referral" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Details</label>
      <textarea name="details" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Receiving Barangay</label>
      <select name="receiving_barangay" class="form-select" required>
        <option value="">-- Select --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= $b ?>"><?= $b ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button class="btn btn-primary">Submit</button>
  </form>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

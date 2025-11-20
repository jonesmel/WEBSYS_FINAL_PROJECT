<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
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
              <?=$p['patient_code']?> (<?=$p['barangay']?>)
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
            <option value="">-- Select Barangay --</option>
            <?php
              $barangays = [
                'Ambiong','Loakan Proper','Pacdal',
                'BGH Compound','Bakakeng Central','Camp 7'
              ];
              foreach ($barangays as $b):
            ?>
              <option value="<?= $b ?>"><?= $b ?></option>
            <?php endforeach; ?>
        </select>
      </div>

      <button class="btn btn-primary">Save Contact</button>
    </form>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
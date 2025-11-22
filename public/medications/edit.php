<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__ . '/../../src/models/MedicationModel.php';
require_once __DIR__ . '/../../src/models/PatientModel.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "Missing ID"; exit; }

$med = MedicationModel::getById($id);
if (!$med) { echo "Not found"; exit; }

$patients = PatientModel::getAll();
?>

<div class="container py-4" style="max-width:600px;">
  <div class="card shadow-sm p-4">
    <h4 class="mb-3">Edit Medication</h4>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=medication/edit&id=<?=$id?>">
      <div class="mb-3">
        <label class="form-label">Patient</label>
        <select name="patient_id" class="form-select" required>
          <?php foreach ($patients as $p): ?>
            <option value="<?=$p['patient_id']?>" <?= $p['patient_id'] == $med['patient_id'] ? 'selected' : '' ?>>
              <?=$p['patient_code']?> (<?=$p['barangay']?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php 
      $drugs = ["Isoniazid (INH)", "Rifampicin (RIF)", "Pyrazinamide (PZA)", "Ethambutol (EMB)", "Streptomycin (STM)"];
      ?>

      <div class="mb-3">
        <label class="form-label">Drugs Administered</label>
        <select name="drugs" class="form-select" required>
          <option value="">-- Select Drug --</option>
          <?php foreach ($drugs as $d): ?>
              <option value="<?=$d?>"><?=$d?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Start Date</label>
          <input type="date" name="start_date" value="<?=htmlspecialchars($med['start_date'])?>" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">End Date</label>
          <input type="date" name="end_date" value="<?=htmlspecialchars($med['end_date'])?>" class="form-control">
        </div>
      </div>

      <div class="mt-3 mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"><?=htmlspecialchars($med['notes'])?></textarea>
      </div>

      <button class="btn btn-success w-100">Update</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

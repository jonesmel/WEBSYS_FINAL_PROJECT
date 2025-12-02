<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:600px;">
  <div class="card shadow-sm p-4">
    <h4 class="mb-3">Add Medication Drugs</h4>

    <form action="/WEBSYS_FINAL_PROJECT/public/?route=medication/add" method="POST">
      <div class="mb-3">
        <label class="form-label">Patient ID (patient_id)</label>
        <select name="patient_id" class="form-select" required>
          <option value="">-- Select Patient --</option>
          <?php
            require_once __DIR__ . '/../../src/models/PatientModel.php';
            $user = $_SESSION['user'];
            if ($user['role'] === 'health_worker') {
                $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
            } else {
                $patients = PatientModel::getAll();
            }
          foreach ($patients as $p):
          ?>
            <option value="<?=$p['patient_id']?>"><?=$p['name'] ?? ''?> (<?=$p['patient_code']?>)</option>
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
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">End Date</label>
          <input type="date" name="end_date" class="form-control">
        </div>
      </div>

      <div class="mt-3 mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"></textarea>
      </div>

      <button class="btn btn-success w-100">Save</button>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list" class="btn btn-secondary mt-3">← Back</a>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

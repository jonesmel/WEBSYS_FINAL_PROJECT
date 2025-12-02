<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);
include __DIR__.'/../partials/header.php';
include __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:800px;">
  <h3 class="mb-3">Import Patients CSV</h3>

  <div class="alert alert-info">
    Upload a CSV file using the exact template:<br>
    <code>name,email,patient_code,age,sex,barangay,contact_number,philhealth_id,tb_case_number,bacteriological_status,anatomical_site,drug_susceptibility,treatment_history,treatment_outcome,outcome_notes</code>
    <br><small>Note: patient_code and tb_case_number can be left empty for auto-generation. treatment_outcome defaults to 'active' if not specified. outcome_notes is optional and may be left blank. email and philhealth_id are optional.</small>
  </div>

  <div class="card shadow-sm p-4">
    <form method="POST" enctype="multipart/form-data" action="/WEBSYS_FINAL_PROJECT/public/?route=import/upload">
      <div class="mb-3">
        <label class="form-label">Select CSV File</label>
        <input type="file" name="csv_file" accept=".csv" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Upload & Import</button>
    </form>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

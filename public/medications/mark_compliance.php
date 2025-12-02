<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../config/db.php';
AuthMiddleware::requireRole(['super_admin', 'health_worker']);

// Get patient info
$patient = [];
if (!empty($medication['patient_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT patient_id, patient_code, name, barangay FROM patients WHERE patient_id = ?");
    $stmt->execute([$medication['patient_id']]);
    $patient = $stmt->fetch();
}
?>

<div class="container py-4" style="max-width:800px;">
  <?php \Flash::display(); ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Mark Medication Compliance</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/compliance" class="btn btn-secondary">← Back to Compliance List</a>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Patient Information</h5>
    <div class="row">
      <div class="col-md-6">
        <p><strong>Patient:</strong> <?=htmlspecialchars($patient['name'] ?? 'Unknown')?> (<?=htmlspecialchars($patient['patient_code'] ?? 'N/A')?>)</p>
        <p><strong>Barangay:</strong> <?=htmlspecialchars($patient['barangay'] ?? 'N/A')?></p>
      </div>
      <div class="col-md-6">
        <p><strong>Medication ID:</strong> #<?=$medication['medication_id']?></p>
        <p><strong>Compliance Status:</strong>
          <span class="badge
            <?=$medication['compliance_status'] === 'taken' ? 'bg-success' :
               ($medication['compliance_status'] === 'missed' ? 'bg-danger' :
               ($medication['compliance_status'] === 'partial' ? 'bg-warning' : 'bg-secondary'))?>">
            <?=ucfirst($medication['compliance_status'] ?? 'pending')?>
          </span>
        </p>
      </div>
    </div>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Medication Details</h5>
    <div class="row">
      <div class="col-md-6">
        <p><strong>Drugs:</strong> <?=htmlspecialchars($medication['drugs'])?></p>
        <p><strong>Start Date:</strong> <?=htmlspecialchars($medication['start_date'] ?? 'N/A')?></p>
        <p><strong>End Date:</strong> <?=htmlspecialchars($medication['end_date'] ?? 'N/A')?></p>
      </div>
      <div class="col-md-6">
        <p><strong>Scheduled For:</strong> <?=htmlspecialchars($medication['scheduled_for_date'] ?? 'N/A')?></p>
        <p><strong>Compliance Deadline:</strong>
          <span class="<?= strtotime($medication['compliance_deadline']) < time() ? 'text-danger fw-bold' : '' ?>">
            <?=htmlspecialchars($medication['compliance_deadline'] ?? 'N/A')?>
            <?php if (strtotime($medication['compliance_deadline']) < time()): ?>
              <small class="text-danger d-block">(Overdue)</small>
            <?php endif; ?>
          </span>
        </p>
        <?php if (!empty($medication['compliance_date'])): ?>
          <p><strong>Last Verified:</strong> <?=htmlspecialchars($medication['compliance_date'])?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($medication['notes'])): ?>
      <p><strong>Notes:</strong> <?=nl2br(htmlspecialchars($medication['notes']))?></p>
    <?php endif; ?>
  </div>

  <div class="card shadow-sm p-4">
    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=medication/mark_compliance&id=<?=$medication['medication_id']?>">
      <input type="hidden" name="medication_id" value="<?=$medication['medication_id']?>">

      <h5 class="mb-3">Update Compliance Status</h5>

      <div class="mb-3">
        <label class="form-label">Compliance Status <span class="text-danger">*</span></label>
        <select name="compliance_status" class="form-select" required>
          <option value="">Select Status</option>
          <option value="taken" <?=($medication['compliance_status'] === 'taken') ? 'selected' : ''?>>✅ Taken - Medication taken as prescribed</option>
          <option value="missed" <?=($medication['compliance_status'] === 'missed') ? 'selected' : ''?>>❌ Missed - Medication was missed</option>
          <option value="partial" <?=($medication['compliance_status'] === 'partial') ? 'selected' : ''?>>⚠️ Partial - Taken partially or irregularly</option>
          <option value="pending" <?=($medication['compliance_status'] === 'pending' || empty($medication['compliance_status'])) ? 'selected' : ''?>>⏳ Pending - Still awaiting verification</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Compliance Notes</label>
        <textarea name="compliance_notes" class="form-control" rows="3" placeholder="Add any observations about medication compliance, patient feedback, or reasons for status..."><?=$medication['compliance_notes'] ?? ''?></textarea>
        <div class="form-text">Optional: Document any relevant details about this compliance update.</div>
      </div>

      <div class="alert alert-info">
        <strong>Note:</strong>
        <ul class="mb-0">
          <li>Selecting "Missed" will create a staff follow-up notification for additional patient support.</li>
          <li>This action will be logged with your user details and timestamp.</li>
          <li>The compliance status reflects professional verification, not patient self-reporting.</li>
        </ul>
      </div>

      <button type="submit" class="btn btn-success">Update Compliance Status</button>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/compliance" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

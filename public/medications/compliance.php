<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin', 'health_worker']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Medication Compliance Tracking</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list" class="btn btn-outline-secondary">All Medications</a>
  </div>

  <div class="alert alert-info">
    <strong>Pending Compliance Review:</strong> Medications that have passed their compliance deadline and require staff verification.
  </div>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th style="width:150px; min-width:120px;">Patient Code</th>
            <th style="width:200px; min-width:150px;">Patient Name</th>
            <th style="width:250px; min-width:200px;">Drugs</th>
            <th style="width:150px; min-width:120px;">Scheduled Date</th>
            <th style="width:150px; min-width:120px;">Deadline</th>
            <th style="width:120px; min-width:100px;">Status</th>
            <th style="width:150px; min-width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $med): ?>
            <tr>
              <td class="text-center fw-bold"><?=$med['patient_code']?></td>
              <td class="text-start"><?=$med['name'] ?? 'N/A'?></td>
              <td class="text-start"><?=$med['drugs']?></td>
              <td class="text-center"><?=$med['scheduled_for_date'] ?? 'N/A'?></td>
              <td class="text-center <?= strtotime($med['compliance_deadline']) < time() ? 'text-danger fw-bold' : '' ?>">
                <?=$med['compliance_deadline'] ?? 'N/A'?>
                <?php if (strtotime($med['compliance_deadline']) < time()): ?>
                  <br><small class="text-danger">(Overdue)</small>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge
                  <?=$med['compliance_status'] === 'taken' ? 'bg-success' :
                     ($med['compliance_status'] === 'missed' ? 'bg-danger' :
                     ($med['compliance_status'] === 'partial' ? 'bg-warning' : 'bg-secondary'))?>">
                  <?=ucfirst($med['compliance_status'] ?? 'pending')?>
                </span>
              </td>
              <td class="text-center">
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/mark_compliance&id=<?=$med['medication_id']?>"
                   class="btn btn-primary btn-sm">
                  Mark Compliance
                </a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-muted">No medications requiring compliance review.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

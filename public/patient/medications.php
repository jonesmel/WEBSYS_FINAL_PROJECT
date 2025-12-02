<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container py-4">
  <h3 class="mb-4">My Medications</h3>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th>Drugs</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Compliance Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): foreach ($rows as $m): ?>
            <tr>
              <td class="text-center"><?=htmlspecialchars($m['drugs'] ?? '-')?></td>
              <td class="text-center"><?=htmlspecialchars($m['start_date'] ?? '-')?></td>
              <td class="text-center"><?=htmlspecialchars($m['end_date'] ?? '-')?></td>
              <td class="text-center">
                <span class="
                  <?=$m['compliance_status'] === 'taken' ? 'badge bg-success' :
                     ($m['compliance_status'] === 'missed' ? 'badge bg-danger' :
                     ($m['compliance_status'] === 'partial' ? 'badge bg-warning' : 'badge bg-secondary'))?>
                ">
                  <?php
                  switch($m['compliance_status']) {
                      case 'taken': echo '✓ Taken'; break;
                      case 'missed': echo '✗ Missed'; break;
                      case 'partial': echo '⚠ Partially taken'; break;
                      default: echo 'Not yet reviewed';
                  }
                  ?>
                </span>
                <?php if (!empty($m['compliance_date'])): ?>
                  <br><small class="text-muted mt-1 d-block">Updated: <?=date('M d, Y', strtotime($m['compliance_date']))?></small>
                <?php endif; ?>
              </td>
              <td class="text-start">
                <?=nl2br(htmlspecialchars($m['notes'] ?? '-'))?>
                <?php if (!empty($m['compliance_notes'])): ?>
                  <hr style="margin:8px 0;">
                  <strong>Healthcare Provider Notes:</strong><br>
                  <?=nl2br(htmlspecialchars($m['compliance_notes']))?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center">No medications found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="alert alert-info mt-3">
    <strong>💊 Medication Compliance:</strong> Your compliance status is verified by healthcare providers.
    Status updates reflect professional monitoring and should be discussed with your healthcare team.
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

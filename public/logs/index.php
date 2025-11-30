<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);

// Helper function to format audit changes (shows only what's different or import summaries)
function formatAuditChanges($oldJson, $newJson, $action = '') {
    $oldData = $oldJson ? json_decode($oldJson, true) : [];
    $newData = $newJson ? json_decode($newJson, true) : [];

    if ($oldData === false && $newData === false) {
        return '<span class="text-muted">— Invalid data —</span>';
    }

    // Special handling for import actions
    if (strpos($action, 'import') !== false) {
        // Check for file key (always present) rather than inserted (might be missing)
        if (!$newData || !isset($newData['file'])) {
            return '<span class="text-muted">— Import data —</span>';
        }
        $count = $newData['inserted'] ?? 0;
        $skipped = $newData['skipped'] ?? 0;
        $file = $newData['file'] ?? 'unknown file';

        $parts = [];
        if ($count > 0) {
            $parts[] = "inserted {$count}";
        }
        if ($skipped > 0) {
            $parts[] = "skipped {$skipped}";
        }

        // Show summary even if inserted=0 and skipped=0 (for informational purposes)
        $summary = !empty($parts) ? implode(', ', $parts) : 'completed';
        return '<div class="audit-import-summary"><strong>Imported ' . htmlspecialchars($summary) . ' from ' . htmlspecialchars($file) . '</strong></div>';
    }

    // Handle case where only one is available
    if (empty($oldData) && !empty($newData)) {
        return '<span class="text-muted">— Record created —</span>';
    }
    if (empty($newData) && !empty($oldData)) {
        return '<span class="text-muted">— Record deleted —</span>';
    }

    $changes = [];

    // Only compare fields that exist in BOTH old and new data
    // This prevents false changes from fields missing in partial JSON data
    $commonFields = array_intersect(array_keys($oldData), array_keys($newData));

    foreach ($commonFields as $key) {
        $oldValue = $oldData[$key];
        $newValue = $newData[$key];
        if ($oldValue != $newValue) {
            $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
        }
    }

    if (empty($changes)) {
        return '<span class="text-muted">— No changes —</span>';
    }

    $output = '<div class="audit-changes">';
    foreach ($changes as $key => $change) {
        $label = ucwords(str_replace(['_', 'id'], [' ', 'ID'], $key));
        $labelMap = [
            'Patient Id' => 'Patient ID',
            'User Id' => 'User ID',
            'Referral Id' => 'Referral ID',
            'Contact Id' => 'Contact ID',
            'Medication Id' => 'Medication ID',
            'Created At' => 'Created At',
            'Updated At' => 'Updated At',
            'Start Date' => 'Start Date',
            'End Date' => 'End Date',
            'Tb Case Number' => 'TB Case Number',
            'Is Verified' => 'Verified',
            'Is Active' => 'Active',
            'File' => 'Import File'
        ];
        $label = $labelMap[$label] ?? $label;

        $oldDisplay = $change['old'];
        $newDisplay = $change['new'];

        if ($oldDisplay === '' || $oldDisplay === null) {
            $oldDisplay = '<span class="text-muted">(empty)</span>';
        } elseif (is_bool($oldDisplay)) {
            $oldDisplay = $oldDisplay ? 'Yes' : 'No';
        } elseif (is_array($oldDisplay)) {
            $oldDisplay = '[Array/Object]';
        } else {
            $oldDisplay = htmlspecialchars($oldDisplay);
        }

        if ($newDisplay === '' || $newDisplay === null) {
            $newDisplay = '<span class="text-muted">(empty)</span>';
        } elseif (is_bool($newDisplay)) {
            $newDisplay = $newDisplay ? 'Yes' : 'No';
        } elseif (is_array($newDisplay)) {
            $newDisplay = '[Array/Object]';
        } else {
            $newDisplay = htmlspecialchars($newDisplay);
        }

        $output .= '<div class="audit-change">';
        $output .= '<strong>' . htmlspecialchars($label) . ':</strong> ';
        $output .= '<span class="old-value">' . $oldDisplay . '</span> → <span class="new-value">' . $newDisplay . '</span>';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
}

include __DIR__.'/../partials/header.php';
include __DIR__.'/../partials/navbar.php';
?>

<div class="container-fluid py-4">
  <h3 class="mb-4">Audit Logs</h3>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/" data-ajax="audit_logs">
        <input type="hidden" name="route" value="log/index">

        <div class="col-sm-2">
          <label class="form-label">User ID</label>
          <input type="number" name="user_id" class="form-control" value="<?=htmlspecialchars($_GET['user_id'] ?? '')?>">
        </div>

        <div class="col-sm-2">
          <label class="form-label">Action</label>
          <input type="text" name="action" class="form-control" placeholder="create, update, delete" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
        </div>

        <div class="col-sm-2">
          <label class="form-label">Table</label>
          <select name="table_name" class="form-control">
            <option value="">All</option>
            <option value="patients" <?= ($_GET['table_name'] ?? '') === 'patients' ? 'selected' : '' ?>>Patients</option>
            <option value="users" <?= ($_GET['table_name'] ?? '') === 'users' ? 'selected' : '' ?>>Users</option>
            <option value="contacts" <?= ($_GET['table_name'] ?? '') === 'contacts' ? 'selected' : '' ?>>Contacts</option>
            <option value="medications" <?= ($_GET['table_name'] ?? '') === 'medications' ? 'selected' : '' ?>>Medications</option>
            <option value="referrals" <?= ($_GET['table_name'] ?? '') === 'referrals' ? 'selected' : '' ?>>Referrals</option>
          </select>
        </div>

        <div class="col-sm-2">
          <label class="form-label">From</label>
          <input type="date" name="from" class="form-control" value="<?=htmlspecialchars($_GET['from'] ?? '')?>">
        </div>

        <div class="col-sm-2">
          <label class="form-label">To</label>
          <input type="date" name="to" class="form-control" value="<?=htmlspecialchars($_GET['to'] ?? '')?>">
        </div>

        <div class="col-sm-2 d-flex align-items-end justify-content-end">
          <button type="button"
                  class="btn btn-secondary search-clear-btn"
                  style="height: 38px;"
                  onclick="clearFilters(this.closest('form'))">
            Clear
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-light">
            <tr style="text-align: center;">
              <th style="width:80px; min-width:60px;">ID</th>
              <th style="width:80px; min-width:60px;">User ID</th>
              <th style="width:100px; min-width:80px;">Action</th>
              <th style="width:120px; min-width:80px;">Table</th>
              <th style="width:100px; min-width:80px;">Record ID</th>
              <th style="width:300px; min-width:200px;">Changes</th>
              <th style="width:180px; min-width:140px;">Timestamp</th>
            </tr>
          </thead>

          <tbody class="audit-logs-table-body">
            <?php foreach ($rows as $log): ?>
            <tr>
              <td class="text-center"><?=htmlspecialchars($log['log_id'])?></td>
              <td class="text-center"><?=htmlspecialchars($log['user_id'])?></td>
              <td class="text-center"><?=htmlspecialchars($log['action'])?></td>
              <td class="text-center"><?=htmlspecialchars($log['table_name'])?></td>
              <td class="text-center"><?=htmlspecialchars($log['record_id']??'')?></td>
              <td class="text-center" style="max-width: 300px;"><?=formatAuditChanges($log['old_values'], $log['new_values'], $log['action'])?></td>
              <td class="text-center"><?=htmlspecialchars($log['created_at'])?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

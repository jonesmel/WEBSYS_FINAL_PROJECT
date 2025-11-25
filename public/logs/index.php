<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);
include __DIR__.'/../partials/header.php';
include __DIR__.'/../partials/navbar.php';
?>

<div class="container-fluid py-4">
  <h3 class="mb-4">Audit Logs</h3>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/">
        <input type="hidden" name="route" value="log/index">

        <div class="col-md-3">
          <label class="form-label">User ID</label>
          <input type="number" name="user_id" class="form-control" value="<?=htmlspecialchars($_GET['user_id'] ?? '')?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Action</label>
          <input type="text" name="action" class="form-control" placeholder="create, update, delete, convert_contact, import" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">From Date</label>
          <input type="date" name="from" class="form-control" value="<?=htmlspecialchars($_GET['from'] ?? '')?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">To Date</label>
          <input type="date" name="to" class="form-control" value="<?=htmlspecialchars($_GET['to'] ?? '')?>">
        </div>

        <div class="col-12 mt-2">
          <button class="btn btn-primary">Filter</button>
          <a href="/WEBSYS_FINAL_PROJECT/public/?route=log/index" class="btn btn-secondary ms-2">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Action</th>
              <th>Table</th>
              <th>Record ID</th>
              <th>Old Values</th>
              <th>New Values</th>
              <th>IP</th>
              <th>User Agent</th>
              <th>Timestamp</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($rows as $log): ?>
            <tr>
              <td><?=htmlspecialchars($log['log_id'])?></td>
              <td><?=htmlspecialchars($log['user_id'])?></td>
              <td><?=htmlspecialchars($log['action'])?></td>
              <td><?=htmlspecialchars($log['table_name'])?></td>
              <td><?=htmlspecialchars($log['record_id'])?></td>
              <td><pre class="small text-muted" style="white-space: pre-wrap;"><?=htmlspecialchars($log['old_values'])?></pre></td>
              <td><pre class="small text-success" style="white-space: pre-wrap;"><?=htmlspecialchars($log['new_values'])?></pre></td>
              <td><?=htmlspecialchars($log['ip_address'])?></td>
              <td><span class="small d-block" style="max-width:200px; white-space:normal;"><?=htmlspecialchars($log['user_agent'])?></span></td>
              <td><?=htmlspecialchars($log['created_at'])?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

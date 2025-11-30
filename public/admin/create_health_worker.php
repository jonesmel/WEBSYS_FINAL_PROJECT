<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/helpers/BarangayHelper.php';
AuthMiddleware::requireRole(['super_admin']);

$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$barangay = trim($_GET['barangay'] ?? '');

$sql = "SELECT * FROM users WHERE role = 'health_worker'";
$params = [];

if ($q !== '') {
    $sql .= " AND email LIKE ?";
    $params[] = "%$q%";
}

if ($barangay !== '') {
    $sql .= " AND barangay_assigned = ?";
    $params[] = $barangay;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$healthWorkers = $stmt->fetchAll();

$barangays = BarangayHelper::getAll();
?>

<div class="container py-4">
  <h3 class="mb-4">Health Worker User Management</h3>

  <!-- Search Boxes -->
  <form class="d-flex justify-content-end mb-3" 
        method="GET" 
        action="/WEBSYS_FINAL_PROJECT/public/" 
        data-ajax="health_workers">

      <input type="hidden" name="route" value="user/create_health_worker">

      <div class="d-flex gap-2" style="max-width: 900px;">
        
        <!-- Search All -->
        <div class="d-flex flex-column" style="width: 250px;">
          <label class="form-label mb-1">Search All Fields</label>
          <input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search...">
        </div>

        <!-- Barangay (AJAX dropdown replaces select) -->
        <div class="d-flex flex-column" style="width: 250px;">
          <label class="form-label mb-1">Filter by Barangay</label>
          <select name="barangay" class="form-select">
            <option value="">All Barangays</option>
            <?php foreach ($barangays as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>" <?= ($b === $barangay) ? 'selected' : '' ?>>
                <?= htmlspecialchars($b) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Clear Button -->
        <button type="button" class="btn btn-secondary search-clear-btn"
                onclick="clearFilters(this.closest('form'))">
          Clear
        </button>

      </div>
  </form>

  <!-- CREATE HEALTH WORKER -->
  <div class="card shadow-sm p-4 mb-4">
    <h5>Create Health Worker Account</h5>

    <form method="POST" class="row g-3" action="/WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker">
      <div class="col-md-6">
        <label class="form-label">Assigned Barangay</label>
        <select name="barangay_assigned" class="form-select" required>
          <option value="">-- Select Barangay --</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>
        <div id="email-status" class="mt-1 small"></div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Create Health Worker</button>
      </div>
    </form>
  </div>

  <!-- HEALTH WORKER TABLE -->
  <div class="card shadow-sm p-4">
    <h5>Existing Health Worker Accounts</h5>

    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th>Email</th>
            <th>Verified?</th>
            <th>Assigned Barangay</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>

        <tbody class="hw-table-body">
        <?php if ($healthWorkers): foreach ($healthWorkers as $hw): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($hw['email']) ?></td>
            <td class="text-center"><?= $hw['is_verified']
                  ? '<span class="badge bg-success">Yes</span>'
                  : '<span class="badge bg-warning text-dark">No</span>' ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($hw['barangay_assigned']) ?></td>
            <td class="text-center">
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?= $hw['user_id'] ?>"
                onclick="return confirm('Delete this user?');"
                class="btn btn-danger btn-sm">Delete</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-center text-muted">No health workers found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

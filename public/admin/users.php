<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/UserModel.php';
require_once __DIR__.'/../../src/helpers/EmailHelper.php';
require_once __DIR__.'/../../src/helpers/Flash.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';

AuthMiddleware::requireRole(['super_admin']);

$patients = PatientModel::getAllWithoutUser();

$pdo = getDB();

// Filters
$q = trim($_GET['q'] ?? '');
$barangay = trim($_GET['barangay'] ?? '');

$sql = "
    SELECT u.*, p.patient_code, p.barangay AS patient_barangay
    FROM users u
    JOIN patients p ON p.user_id = u.user_id
    WHERE u.role = 'patient'
";

$params = [];

if ($q !== '') {
    $sql .= " AND (u.email LIKE ? OR p.patient_code LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
}

if ($barangay !== '') {
    $sql .= " AND p.barangay = ?";
    $params[] = $barangay;
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patientUsers = $stmt->fetchAll();

$barangays = BarangayHelper::getAll();
?>

<div class="container py-4">
  <h3 class="mb-4">Patient User Management</h3>

  <!-- Search Boxes -->
  <form class="d-flex justify-content-end mb-3"
        method="GET"
        action="/WEBSYS_FINAL_PROJECT/public/"
        data-ajax="patient_users">

      <input type="hidden" name="route" value="admin/users">

      <div class="d-flex gap-2 align-items-end" style="max-width: 900px;">

          <!-- Search All Fields -->
          <div class="d-flex flex-column" style="width: 250px;">
              <label class="form-label mb-1">Search All Fields</label>
              <input name="q"
                    value="<?= htmlspecialchars($q) ?>"
                    class="form-control"
                    placeholder="Search...">
          </div>

          <!-- Barangay Filter -->
          <div class="d-flex flex-column" style="width: 250px;">
              <label class="form-label mb-1">Filter by Barangay</label>
              <select name="barangay" class="form-select">
                  <option value="">All Barangays</option>
                  <?php foreach ($barangays as $b): ?>
                      <option value="<?= htmlspecialchars($b) ?>"
                          <?= ($b === $barangay) ? 'selected' : '' ?>>
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

  <?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message_type'] ?? 'info') ?> alert-dismissible fade show">
      <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); unset($_SESSION['flash_message_type']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- CREATE PATIENT USER -->
  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Create User Account for Patient</h5>

    <form method="POST" class="row g-3" action="/WEBSYS_FINAL_PROJECT/public/?route=user/create_patient_user">

      <div class="col-md-6">
        <label class="form-label">Select Patient (Name, Code, Barangay)</label>
        <select name="patient_id" class="form-select" required>
          <option value="">-- Select --</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['patient_id'] ?>">
              <?= htmlspecialchars($p['name'] ?? '') ?>
              (<?= htmlspecialchars($p['patient_code']) ?>
              <?= isset($p['barangay']) ? ' - ' . htmlspecialchars($p['barangay']) . ')' : ')' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
        <div id="email-status" class="mt-1 small"></div>
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="submit">Create Account</button>
      </div>
    </form>
  </div>

  <!-- TABLE -->
  <div class="card shadow-sm p-4 mb-4">
    <h5>Existing Patient User Accounts</h5>

    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr style="text-align: center;">
            <th>Email</th>
            <th>Verified?</th>
            <th>Assigned Patient Code</th>
            <th>Patient Barangay</th>
            <th style="width:140px; min-width:120px;">Actions</th>
          </tr>
        </thead>

        <tbody class="patient-table-body">
        <?php if (empty($patientUsers)): ?>
        <tr><td colspan="5" class="text-center text-muted">No patient users found.</td></tr>
        <?php else: ?>
        <?php foreach ($patientUsers as $u): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($u['email']) ?></td>
            <td class="text-center"><?= $u['is_verified']
                  ? '<span class="badge bg-success">Yes</span>'
                  : '<span class="badge bg-warning text-dark">No</span>' ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($u['patient_code']) ?></td>
            <td class="text-center"><?= htmlspecialchars($u['patient_barangay']) ?></td>
            <td class="text-center">
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?= $u['user_id'] ?>"
                onclick="return confirm('Delete this user?');"
                class="btn btn-danger btn-sm">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const patientSelect = document.querySelector("select[name='patient_id']");
    if (patientSelect) createSearchablePatientDropdown(patientSelect);
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

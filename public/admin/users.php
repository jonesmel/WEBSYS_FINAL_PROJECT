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

  <!-- AJAX Filter Form -->
  <form class="row g-2 mb-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/" data-ajax="patient_users">
    <input type="hidden" name="route" value="admin/users">

    <div class="col-md-4">
      <input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search email or patient code">
    </div>

    <div class="col-md-3">
      <select name="barangay" class="form-select">
        <option value="">-- Barangay --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= htmlspecialchars($b) ?>" <?= ($b === $barangay) ? 'selected' : '' ?>>
            <?= htmlspecialchars($b) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>

    <div class="col-md-3 text-end">
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users" class="btn btn-secondary">Reset</a>
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
        <label class="form-label">Select Patient (patient_code)</label>
        <select name="patient_id" class="form-select" required>
          <option value="">-- Select --</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?= $p['patient_id'] ?>">
              <?= htmlspecialchars($p['patient_code']) ?>
              <?= isset($p['barangay']) ? '(' . htmlspecialchars($p['barangay']) . ')' : '' ?>
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
          <tr>
            <th>Email</th>
            <th>Verified?</th>
            <th>Assigned Patient Code</th>
            <th>Patient Barangay</th>
            <th width="90"></th>
          </tr>
        </thead>

        <tbody class="patient-table-body">
        <?php foreach ($patientUsers as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['is_verified']
                  ? '<span class="badge bg-success">Yes</span>'
                  : '<span class="badge bg-warning text-dark">No</span>' ?>
            </td>
            <td><?= htmlspecialchars($u['patient_code']) ?></td>
            <td><?= htmlspecialchars($u['patient_barangay']) ?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?= $u['user_id'] ?>"
                onclick="return confirm('Delete this user?');"
                class="btn btn-danger btn-sm w-100">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
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

<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/UserModel.php';
require_once __DIR__.'/../../src/helpers/EmailHelper.php';
require_once __DIR__.'/../../src/helpers/Flash.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';

AuthMiddleware::requireRole(['super_admin']);

$pdo = getDB();

// Fetch early
$patients = PatientModel::getAllWithoutUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $email = trim($_POST['email'] ?? '');

    if (!$patient_id || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Flash::set('danger', 'Invalid input. Select a patient and enter a valid email.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
        exit;
    }

    if (UserModel::emailExists($email)) {
        Flash::set('danger', 'Email already registered.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
        exit;
    }

    try {
        $token = bin2hex(random_bytes(16));
        $tempPass = bin2hex(random_bytes(6));

        $uid = UserModel::createPatientUser($email, $tempPass, $token);

        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE patients SET user_id=? WHERE patient_id=?");
        $stmt->execute([$uid, $patient_id]);

        EmailHelper::sendVerificationEmail($email, $token);

        Flash::set('success', 'Patient user created. Verification email sent.');

    } catch (Exception $e) {
        Flash::set('danger', 'Error: '.$e->getMessage());
    }

    header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
    exit;
}

$pdo = getDB();
$patientUsers = $pdo->query("
    SELECT u.*, p.patient_code
    FROM users u
    JOIN patients p ON p.user_id = u.user_id
    WHERE u.role = 'patient'
    ORDER BY u.created_at DESC
")->fetchAll();
?>

<div class="container py-4">
  <h3 class="mb-4">Patient User Management</h3>

  <!-- Filter form -->
  <form class="row g-2 mb-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/">
    <input type="hidden" name="route" value="admin/users">
    <div class="col-md-4"><input name="q" value="<?=htmlspecialchars($q)?>" class="form-control" placeholder="Search email or patient code"></div>
    <div class="col-md-3">
      <select name="barangay" class="form-select">
        <option value="">-- Barangay --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?=htmlspecialchars($b)?>" <?= $b === $barangay ? 'selected' : '' ?>><?=htmlspecialchars($b)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
    <div class="col-md-3 text-end"><a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users" class="btn btn-secondary">Reset</a></div>
  </form>

  <?php if (!empty($_SESSION['flash_message'])): ?>
      <div class="alert alert-<?=htmlspecialchars($_SESSION['flash_message_type'] ?? 'info')?> alert-dismissible fade show">
        <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); unset($_SESSION['flash_message_type']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  <?php endif; ?>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Create User Account for Patient</h5>

    <form method="POST" class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Select Patient (patient_code)</label>
        <select name="patient_id" class="form-select" required>
          <option value="">-- Select --</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?=$p['patient_id']?>">
              <?=htmlspecialchars($p['patient_code'])?>
              <?=isset($p['barangay']) ? '('.htmlspecialchars($p['barangay']).')' : ''?>
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
        <tbody>
        <?php foreach ($patientUsers as $u): ?>
          <tr>
            <td><?=htmlspecialchars($u['email'])?></td>
            <td><?= $u['is_verified'] 
                ? '<span class="badge bg-success">Yes</span>'
                : '<span class="badge bg-warning text-dark">No</span>' ?></td>
            <td><?=htmlspecialchars($u['patient_code'])?></td>
            <td><?=htmlspecialchars($u['patient_barangay'])?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?=$u['user_id']?>"
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
    const emailField = document.querySelector("input[name='email']");
    const statusBox = document.getElementById("email-status");
    const submitBtn = document.querySelector("button[type='submit']");
    if (submitBtn) submitBtn.disabled = true;

    let typingTimeout = null;

    emailField?.addEventListener("input", function () {
        clearTimeout(typingTimeout);
        const email = this.value.trim();

        if (email.length === 0) {
            statusBox.innerHTML = "";
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        typingTimeout = setTimeout(() => {
            fetch("/WEBSYS_FINAL_PROJECT/public/?route=ajax/check_email&email=" + encodeURIComponent(email))
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        statusBox.innerHTML = "<span class='text-success'>" + data.message + "</span>";
                        if (submitBtn) submitBtn.disabled = false;
                    } else {
                        statusBox.innerHTML = "<span class='text-danger'>" + data.message + "</span>";
                        if (submitBtn) submitBtn.disabled = true;
                    }
                });
        }, 300);
    });
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>

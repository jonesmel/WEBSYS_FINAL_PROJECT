<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/UserModel.php';
require_once __DIR__.'/../../src/helpers/EmailHelper.php';
require_once __DIR__.'/../../src/helpers/Flash.php';
AuthMiddleware::requireRole(['super_admin']);

$patients = PatientModel::getAllWithoutUser();

// Handle POST (create user for patient)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $patient_id = $_POST['patient_id'] ?? null;
  $email = trim($_POST['email'] ?? '');

  if (!$patient_id || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Flash::set('danger', 'Invalid input. Select a patient and enter a valid email.');
    header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
    exit;
  }

  try {
    $token = bin2hex(random_bytes(16));
    $tempPass = bin2hex(random_bytes(6));
    $uid = UserModel::createPatientUser($email, $tempPass, $token);

    // Link user to patient
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE patients SET user_id=? WHERE patient_id=?");
    $stmt->execute([$uid, $patient_id]);

    EmailHelper::sendVerificationEmail($email, $token);

    // Log user creation
    require_once __DIR__.'/../../src/models/LogModel.php';
    LogModel::insertLog($_SESSION['user']['user_id'],'create','users',$uid,null,json_encode(['email'=>$email,'patient_id'=>$patient_id]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

    Flash::set('success', 'User account created and verification email sent.');
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      Flash::set('danger', 'This email is already registered.');
    } else {
      Flash::set('danger', 'Database error: ' . $e->getMessage());
    }
  } catch (Exception $e) {
    Flash::set('danger', 'Error: ' . $e->getMessage());
  }

  header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
  exit;
}
?>

<div class="container py-4">
  <h3 class="mb-4">User Management</h3>

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
            <option value="<?=$p['patient_id']?>"><?=htmlspecialchars($p['patient_code'])?> <?=isset($p['barangay'])? '('.htmlspecialchars($p['barangay']).')':''?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="col-12">
        <button class="btn btn-primary">Create Account</button>
      </div>
    </form>
  </div>

  <div class="card shadow-sm p-4">
    <h5>Existing Users</h5>
    <table class="table table-bordered mt-3">
      <thead>
        <tr>
          <th>Email</th>
          <th>Role</th>
          <th>Verified?</th>
          <th>Assigned Patient</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php
        $pdo = getDB();
        $rows = $pdo->query("
            SELECT u.*, p.patient_code 
            FROM users u 
            LEFT JOIN patients p ON p.user_id=u.user_id 
            WHERE u.role != 'super_admin'
            ORDER BY u.created_at DESC
        ")->fetchAll();
        foreach ($rows as $u):
      ?>
        <tr>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td><?=htmlspecialchars($u['role'])?></td>
          <td><?= $u['is_verified'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
          <td><?=htmlspecialchars($u['patient_code'] ?? '-')?></td>
          <td>
            <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?=$u['user_id']?>"
              onclick="return confirm('Delete this user?');"
              class="btn btn-danger btn-sm">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

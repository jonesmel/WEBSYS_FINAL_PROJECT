<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/middleware/VerifyEmailMiddleware.php';
AuthMiddleware::requireRole(['patient']);
VerifyEmailMiddleware::enforce();

$uid = $_SESSION['user']['user_id'];
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$uid]);
$patient = $stmt->fetch();
?>

<div class="container py-4" style="max-width:700px;">

  <!-- Show messages -->
  <?php if (!empty($messageError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($messageError) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($messageSuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($messageSuccess) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <h3 class="mb-4">My Profile</h3>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Patient Information</h5>
    <p><strong>Patient Code:</strong> <?=htmlspecialchars($patient['patient_code'])?></p>
    <p><strong>TB Case Number:</strong> <?=htmlspecialchars($patient['tb_case_number'])?></p>
    <p><strong>Barangay:</strong> <?=htmlspecialchars($patient['barangay'])?></p>
    <p><strong>Contact Number:</strong> <?=htmlspecialchars($patient['contact_number'])?></p>
  </div>

  <div class="card shadow-sm p-4">
    <h5 class="mb-3">Change Password</h5>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=auth/change_password">
      <input type="hidden" name="uid" value="<?=$uid?>">

      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-control" required minlength="8">
      </div>

      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" required minlength="8">
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required minlength="8">
      </div>

      <button class="btn btn-primary w-100">Update Password</button>
    </form>
  </div>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

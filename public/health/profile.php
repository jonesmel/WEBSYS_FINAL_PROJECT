<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/helpers/Flash.php';
AuthMiddleware::requireRole(['health_worker']);

$user = $_SESSION['user'];
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">My Profile</h3>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Account Information</h5>

    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> Health Worker</p>
    <p><strong>Assigned Barangay:</strong> <?= htmlspecialchars($user['barangay_assigned']) ?></p>
  </div>

  <div class="card shadow-sm p-4">
    <h5 class="mb-3">Change Password</h5>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=auth/change_password">
      <input type="hidden" name="uid" value="<?= $user['user_id'] ?>">

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

<?php include __DIR__ . '/../partials/footer.php'; ?>

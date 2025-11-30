<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/navbar.php';

$user = $_SESSION['user'];
?>

<div class="container py-4" style="max-width:700px;">

  <h3 class="mb-4">My Profile</h3>

  <div class="card p-4 shadow-sm mb-4">
    <h5 class="mb-3">Account Information</h5>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> Super Admin</p>
  </div>

  <div class="card p-4 shadow-sm mb-4">
    <h5 class="mb-3">Change Password</h5>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=auth/change_password" data-ajax="change_password">
      <input type="hidden" name="uid" value="<?= $user['user_id'] ?>">

      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <div class="position-relative">
          <input type="password" name="current_password" class="form-control" required minlength="8">
          <div class="invalid-feedback current-password-feedback"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="position-relative">
          <input type="password" name="password" class="form-control" required minlength="8">
          <div class="invalid-feedback new-password-feedback"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <div class="position-relative">
          <input type="password" name="confirm_password" class="form-control" required minlength="8">
          <div class="invalid-feedback confirm-password-feedback"></div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100" disabled>Update Password</button>
    </form>
  </div>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

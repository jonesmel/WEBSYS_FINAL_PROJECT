<?php
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/navbar.php';

$uid = $_GET['uid'] ?? ($_POST['uid'] ?? null);
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card shadow-sm p-4" style="max-width:400px; width:100%;">
    <h4 class="mb-3 text-center">Set New Password</h4>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=auth/reset_password" autocomplete="off">

      <input type="hidden" name="uid" value="<?=htmlspecialchars($uid)?>">

      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" required minlength="8">
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required minlength="8">
      </div>

      <button class="btn btn-success w-100">Set Password</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

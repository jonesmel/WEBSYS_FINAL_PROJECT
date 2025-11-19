<?php
class VerifyEmailMiddleware {
  public static function enforce() {
    if (empty($_SESSION['user'])) return;

    $u = $_SESSION['user'];

    if ($u['role'] === 'patient') {
      if (!$u['is_verified']) {
        echo 'Please verify your email to continue.';
        exit;
      }
      if ($u['password_reset_required']) {
        header('Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid='.$u['user_id']);
        exit;
      }
    }
  }
}
?>
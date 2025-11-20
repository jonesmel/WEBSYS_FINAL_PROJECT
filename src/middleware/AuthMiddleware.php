<?php
class AuthMiddleware {
  public static function requireLogin() {
    if (empty($_SESSION['user'])) {
      header('Location: /WEBSYS_FINAL_PROJECT/public/login.php');
      exit;
    }
  }

  public static function requireRole($roles) {
    self::requireLogin();
    $roles = (array)$roles;
    $user = $_SESSION['user'];

    if (!in_array($user['role'], $roles)) {
      http_response_code(403);
      echo 'Access denied';
      exit;
    }
  }

  public static function requireVerified() {
    self::requireLogin();
    $u = $_SESSION['user'];

    if (!$u['is_verified']) {
      echo 'Please verify your email first.';
      exit;
    }

    if ($u['password_reset_required']) {
      header('Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid='.$u['user_id']);
      exit;
    }
  }
}
?>
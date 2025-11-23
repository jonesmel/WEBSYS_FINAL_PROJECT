<?php
$message = $_GET['msg'] ?? 'Access denied.';
$code = $_GET['code'] ?? 403;

http_response_code($code);

require_once __DIR__.'/partials/header.php';
?>

<div class="container py-5" style="max-width:600px;">
  <div class="card shadow-sm border-danger">
    <div class="card-body text-center">
      <h2 class="text-danger mb-3"><?= htmlspecialchars($message) ?></h2>
      <p class="text-muted mb-4">
        You do not have permission to access this page.
      </p>
      <a href="/WEBSYS_FINAL_PROJECT/public/login.php" class="btn btn-primary">
        Go to Login
      </a>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/partials/footer.php'; ?>

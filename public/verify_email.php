<?php
include __DIR__.'/partials/header.php';
include __DIR__.'/partials/navbar.php';
?>

<div class="container py-5" style="max-width:600px;">
  <div class="card p-4 shadow-sm">
    <h4>Email Verification</h4>
    <p>Please wait while we verify your account…</p>

    <?php
    $token = $_GET['token'] ?? null;
    if (!$token) {
      echo '<div class="alert alert-danger">Invalid token.</div>';
    } else {
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=auth/verify&token='.$token);
      exit;
    }
    ?>
  </div>
</div>

<?php include __DIR__.'/partials/footer.php'; ?>
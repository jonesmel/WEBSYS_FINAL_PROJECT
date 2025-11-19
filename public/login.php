<?php
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/navbar.php';
?>
<?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
<div class="alert alert-success">Password updated. You may now log in.</div>
<?php endif; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card shadow-sm p-4" style="max-width:400px; width:100%;">
    <h4 class="mb-3 text-center">TB-MAS Login</h4>

    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=auth/login">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <button class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
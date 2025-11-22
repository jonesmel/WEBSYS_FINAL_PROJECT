<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);
?>

<div class="container py-4" style="max-width:700px;">
  <h3 class="mb-4">Create Health Worker Account</h3>

  <?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
      <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm p-4">
    <form method="POST" action="/WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>
        <div id="email-status" class="mt-1 small"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Assigned Barangay</label>
        <select name="barangay_assigned" class="form-select" required>
          <option value="">-- Select Barangay --</option>
          <?php
              $barangays = [
                  'Ambiong',
                  'Loakan Proper',
                  'Pacdal',
                  'BGH Compound',
                  'Bakakeng Central',
                  'Camp 7'
              ];
              foreach ($barangays as $b):
          ?>
              <option value="<?=$b?>"><?=$b?></option>
          <?php endforeach; ?>
      </select>
      </div>

      <button type="submit" class="btn btn-primary w-100">Create Health Worker</button>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const emailField = document.querySelector("input[name='email']");
    const statusBox = document.getElementById("email-status");
    const submitBtn = document.querySelector("button[type='submit']");

    submitBtn.disabled = true;

    let typingTimeout = null;

    emailField.addEventListener("input", function () {
        clearTimeout(typingTimeout);

        const email = this.value.trim();

        if (email.length === 0) {
            statusBox.innerHTML = "";
            submitBtn.disabled = true;
            return;
        }

        typingTimeout = setTimeout(() => {
            fetch("/WEBSYS_FINAL_PROJECT/public/?route=ajax/check_email&email=" + encodeURIComponent(email))
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        statusBox.innerHTML = "<span class='text-success'>" + data.message + "</span>";
                        submitBtn.disabled = false;
                    } else {
                        statusBox.innerHTML = "<span class='text-danger'>" + data.message + "</span>";
                        submitBtn.disabled = true;
                    }
                });
        }, 300);
    });

});
</script>


<?php include __DIR__ . '/../partials/footer.php'; ?>

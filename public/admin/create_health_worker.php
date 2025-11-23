<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
AuthMiddleware::requireRole(['super_admin']);

$pdo = getDB();

$healthWorkers = $pdo->query("
    SELECT *
    FROM users
    WHERE role = 'health_worker'
    ORDER BY created_at DESC
")->fetchAll();
?>

<div class="container py-4">
  <h3 class="mb-4">Health Worker User Management</h3>

  <?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
      <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm p-4 mb-4">
    <h5>Create Health Worker Account</h5>

    <form method="POST" class="row g-3" action="/WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker">
      <div class="col-md-6">
        <label class="form-label">Assigned Barangay</label>
        <select name="barangay_assigned" class="form-select" required>
          <option value="">-- Select Barangay --</option>
          <?php
            $barangays = [
              'Ambiong', 'Loakan Proper', 'Pacdal', 'BGH Compound', 'Bakakeng Central', 'Camp 7'
            ];
            foreach ($barangays as $b):
          ?>
            <option value="<?=$b?>"><?=$b?></option>
          <?php endforeach; ?>
        </select>
      </div>
    
      <div class="col-md-6">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>
        <div id="email-status" class="mt-1 small"></div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Create Health Worker</button>
      </div>
    </form>
  </div>

  <!-- HEALTH WORKER TABLE -->
  <div class="card shadow-sm p-4">
    <h5>Existing Health Worker Accounts</h5>

    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>Email</th>
            <th>Verified?</th>
            <th>Assigned Barangay</th>
            <th width="90"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($healthWorkers as $hw): ?>
          <tr>
            <td><?=htmlspecialchars($hw['email'])?></td>
            <td><?= $hw['is_verified']
                ? '<span class="badge bg-success">Yes</span>'
                : '<span class="badge bg-warning text-dark">No</span>' ?></td>
            <td><?=htmlspecialchars($hw['barangay_assigned'])?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=<?=$hw['user_id']?>"
                onclick="return confirm('Delete this user?');"
                class="btn btn-danger btn-sm w-100">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

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

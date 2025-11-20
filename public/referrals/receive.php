<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:900px;">

  <h3 class="mb-3">Receive Referral</h3>

  <form method="POST" class="card shadow-sm p-4">

    <div class="mb-3">
      <label class="form-label">Receiving Officer</label>
      <input type="text" name="receiving_officer" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Receiving Unit</label>
      <input type="text" name="receiving_unit" class="form-control" 
             value="<?= htmlspecialchars($_SESSION['user']['barangay_assigned']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Date Received</label>
      <input type="date" name="date_received" class="form-control" value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Action Taken</label>
      <textarea name="action_taken" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Remarks</label>
      <textarea name="remarks" class="form-control" rows="3"></textarea>
    </div>

    <button class="btn btn-success">Submit</button>

  </form>

</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>

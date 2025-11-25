<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Patients</h3>

    <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/create" class="btn btn-primary">Add Patient</a>
    <?php endif; ?>
  </div>

  <!-- Filters -->
  <form class="row g-2 mb-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/">
    <input type="hidden" name="route" value="patient/index">
    <div class="col-md-4"><input name="q" value="<?=htmlspecialchars($_GET['q'] ?? '')?>" class="form-control" placeholder="Search patient code or TB case"></div>
    <div class="col-md-3">
      <select name="barangay" class="form-select">
        <option value="">-- Barangay --</option>
        <?php
          $barangays = $GLOBALS['barangays'] ?? (function_exists('array')? []: []);
          // if $barangays not set by controller, fallback:
          if (empty($barangays)) {
            require_once __DIR__ . '/../../src/helpers/BarangayHelper.php';
            $barangays = BarangayHelper::getAll();
          }
        ?>
        <?php foreach ($barangays as $b): ?>
          <option value="<?=htmlspecialchars($b)?>" <?= (isset($_GET['barangay']) && $_GET['barangay']===$b) ? 'selected' : '' ?>><?=htmlspecialchars($b)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
    <div class="col-md-3 text-end"><a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index" class="btn btn-secondary">Reset</a></div>
  </form>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Patient Code</th>
            <th>Barangay</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Case #</th>
            <th>User Account</th>
            <th width="300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($patients as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['patient_code']) ?></td>
            <td><?= htmlspecialchars($p['barangay']) ?></td>
            <td><?= $p['age'] ?></td>
            <td><?= $p['sex'] ?></td>
            <td><?= htmlspecialchars($p['tb_case_number']) ?></td>

            <td>
              <?php if (!empty($p['user_id'])): ?>
                <span class="badge bg-success">Has Account</span>
              <?php else: ?>
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users&patient_id=<?= $p['patient_id'] ?>"
                     class="badge bg-secondary text-decoration-none" style="cursor: pointer;">
                    No Account — Create
                  </a>
                <?php else: ?>
                  <span class="badge bg-secondary">No Account</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>

            <td>
              <div class="action-buttons">
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?= $p['patient_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                <?php if ($_SESSION['user']['role'] === 'super_admin'): ?>
                  <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=<?= $p['patient_id'] ?>"
                     onclick="return confirm('Delete this patient?');"
                     class="btn btn-sm btn-danger">Delete</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

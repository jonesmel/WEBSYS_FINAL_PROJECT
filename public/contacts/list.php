<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Contacts</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/add" class="btn btn-primary">Add Contact</a>
  </div>

  <form class="row g-2 mb-3" method="GET" action="/WEBSYS_FINAL_PROJECT/public/">
    <input type="hidden" name="route" value="contact/list">
    <div class="col-md-4"><input name="q" value="<?=htmlspecialchars($_GET['q'] ?? '')?>" class="form-control" placeholder="Search contact code / patient code"></div>
    <div class="col-md-3">
      <?php require_once __DIR__ . '/../../src/helpers/BarangayHelper.php'; $barangays = BarangayHelper::getAll(); ?>
      <select name="barangay" class="form-select">
        <option value="">-- Barangay --</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?=htmlspecialchars($b)?>" <?= (isset($_GET['barangay']) && $_GET['barangay']===$b) ? 'selected' : '' ?>><?=htmlspecialchars($b)?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
    <div class="col-md-3 text-end"><a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/list" class="btn btn-secondary">Reset</a></div>
  </form>

  <div class="card shadow-sm p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Contact Code</th>
            <th>Patient Code</th>
            <th>Barangay</th>
            <th>Linked Patient</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['contact_code'])?></td>
            <td><?=$c['patient_code']?></td>
            <td><?=htmlspecialchars($c['barangay'])?></td>
            <td>
              <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=<?=$c['patient_id']?>"
                class="btn btn-sm btn-link">
                <?= htmlspecialchars($c['patient_code']) ?>
              </a>
            </td>
            <td><?=$c['age']?></td>
            <td><?=$c['sex']?></td>
            <td><?=$c['status']?></td>
            <td>
              <?php if ($c['status'] !== 'converted_patient'): ?>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=contact/convert&id=<?=$c['contact_id']?>" class="btn btn-sm btn-outline-warning">Convert</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

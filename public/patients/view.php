<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/ReferralModel.php';
require_once __DIR__.'/../../src/models/ContactModel.php';
require_once __DIR__.'/../../src/models/MedicationModel.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Patient Details</h3>
    <a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/edit&id=<?=$patient['patient_id']?>" class="btn btn-warning">Edit</a>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <h5>Patient Code</h5>
    <div class="fs-4 fw-bold mb-3"><?=htmlspecialchars($patient['patient_code'])?></div>

    <h5>Patient Name</h5>
    <div class="fs-5 mb-3"><?=htmlspecialchars($patient['name'] ?? '')?></div>

    <div class="row mb-3">
      <div class="col-md-3"><strong>Barangay:</strong><br><?=htmlspecialchars($patient['barangay'])?></div>
      <div class="col-md-3"><strong>Age:</strong><br><?=$patient['age']?></div>
      <div class="col-md-3"><strong>Sex:</strong><br><?=$patient['sex']?></div>
      <div class="col-md-3"><strong>Contact No:</strong><br><?=htmlspecialchars($patient['contact_number'])?></div>
    </div>

    <div class="mb-3">
      <strong>PhilHealth ID:</strong><br>
      <?php if (!empty($patient['philhealth_id'])): ?>
        <?= htmlspecialchars(substr($patient['philhealth_id'], 0, 2) . '-' . substr($patient['philhealth_id'], 2, 9) . '-' . substr($patient['philhealth_id'], 11, 1)) ?>
      <?php else: ?>
        Not provided
      <?php endif; ?>
    </div>

    <div class="row mb-3">
      <div class="col-md-3"><strong>TB Case #:</strong><br><?=htmlspecialchars($patient['tb_case_number'])?></div>
      <div class="col-md-3"><strong>Bacteriology:</strong><br><?=$patient['bacteriological_status']?></div>
      <div class="col-md-3"><strong>Anatomical Site:</strong><br><?=$patient['anatomical_site']?></div>
      <div class="col-md-3"><strong>Drug Susceptibility:</strong><br><?=$patient['drug_susceptibility']?></div>
    </div>

    <div class="mb-3">
      <strong>Treatment History:</strong><br><?=$patient['treatment_history']?>
    </div>

    <div class="mb-3">
      <strong>Treatment Outcome:</strong><br>
      <?php
      $outcomes = [
        'active' => 'Active',
        'cured' => 'Cured',
        'treatment_completed' => 'Treatment Completed',
        'died' => 'Died',
        'lost_to_followup' => 'Lost to Follow-Up',
        'failed' => 'Failed',
        'transferred_out' => 'Transferred Out'
      ];
      echo $outcomes[$patient['treatment_outcome']] ?? $patient['treatment_outcome'];
      ?>
      <?php if (!empty($patient['outcome_notes'])): ?>
        <br><small class="text-muted">Details: <?= htmlspecialchars($patient['outcome_notes']) ?></small>
      <?php endif; ?>
    </div>
  </div>

  <!-- Linked modules: Referrals, Contacts, Medications -->

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Referrals</h5>
    <?php $refs = ReferralModel::getByPatient($patient['patient_id']); ?>

    <?php if ($refs): ?>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Referral Date</th>
              <th>Referring Unit</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($refs as $r): ?>
            <tr>
              <td><?=htmlspecialchars($r['referral_date'])?></td>
              <td><?=htmlspecialchars($r['referring_unit'])?></td>
              <td>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=<?=$r['referral_id']?>" class="btn btn-sm btn-outline-primary">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No referrals found.</p>
    <?php endif; ?>
  </div>


  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Contacts</h5>
    <?php $contacts = ContactModel::getByPatient($patient['patient_id']); ?>

    <?php if ($contacts): ?>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Contact Code</th>
              <th>Age</th>
              <th>Sex</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($contacts as $c): ?>
            <tr>
              <td><?=htmlspecialchars($c['contact_code'])?></td>
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
    <?php else: ?>
      <p class="text-muted">No contacts found.</p>
    <?php endif; ?>
  </div>


  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Medications</h5>
    <?php $meds = MedicationModel::getByPatient($patient['patient_id']); ?>

    <?php if ($meds): ?>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Drugs</th>
              <th>Start</th>
              <th>End</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($meds as $m): ?>
            <tr>
              <td><?=htmlspecialchars($m['drugs'])?></td>
              <td><?=$m['start_date']?></td>
              <td><?=$m['end_date']?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No medication history.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/NotificationModel.php';
AuthMiddleware::requireRole(['super_admin']);

// Get comprehensive analytics data
$total = count(PatientModel::getAll());
$ageStats = PatientModel::getAgeGroupStats();
$genderStats = PatientModel::getGenderStats();
$outcomeStats = PatientModel::getTreatmentOutcomeStats();
$monthlyStats = PatientModel::getMonthlyStats(6);
$barangayStats = PatientModel::getBarangayStats();

// Calculate percentages and totals for better display
$totalKnownAge = $total - ($ageStats['age_unknown'] ?? 0);
$totalKnownGender = $total - ($genderStats['unknown'] ?? 0);

// Handle tied barangays for "Most Affected" display
$maxPatients = !empty($barangayStats) ? $barangayStats[0]['count'] : 0;
$tiedBarangays = array_filter($barangayStats, function($b) use ($maxPatients) {
    return $b['count'] === $maxPatients;
});

// Helper function to calculate percentage
function getPercentage($count, $total) {
  return $total > 0 ? round(($count / $total) * 100, 1) : 0;
}
?>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Super Admin Dashboard</h3>
    <div>
      <a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/generateReport" class="btn btn-success">
        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
      </a>
    </div>
  </div>

  <!-- Quick Summary Cards -->
  <div class="row g-3 mb-4 justify-content-center">
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Total Patients</h6>
        <div class="display-6 fw-bold text-primary"><?=$total?></div>
      </div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Active Cases</h6>
        <div class="display-6 fw-bold" style="color: #856404;"><?=$outcomeStats['active'] ?? 0?></div>
      </div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Cured</h6>
        <div class="display-6 fw-bold text-success"><?=$outcomeStats['cured'] ?? 0?></div>
      </div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Most Affected Barangay</h6>
        <?php if (!empty($tiedBarangays)): ?>
          <?php
          $barangayNames = array_column($tiedBarangays, 'barangay');
          $barangayCount = count($barangayNames);
          ?>
          <?php if ($barangayCount === 1): ?>
            <div class="fw-bold text-info"><?=$barangayNames[0]?></div>
            <div class="small text-muted"><?=$maxPatients?> patients</div>
          <?php elseif ($barangayCount === 2): ?>
            <div class="fw-bold text-info"><?=$barangayNames[0]?></div>
            <div class="fw-bold text-info">& <?=$barangayNames[1]?></div>
            <div class="small text-muted">tied (<?=$maxPatients?> each)</div>
          <?php else: ?>
            <div class="fw-bold text-info"><?=$barangayCount?> barangays tied</div>
            <div class="small text-muted"><?=$maxPatients?> patients each</div>
          <?php endif; ?>
        <?php else: ?>
          <div class="fw-bold text-info">N/A</div>
          <div class="small text-muted">no data</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Success Rate</h6>
        <div class="display-6 fw-bold text-success">
          <?=getPercentage(($outcomeStats['cured'] ?? 0) + ($outcomeStats['treatment_completed'] ?? 0),
                          $total - ($outcomeStats['active'] ?? 0))?>%
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 justify-content-center">
    <!-- Age Group Demographics -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">Age Group Distribution</h5>
        </div>
        <div class="card-body">
          <?php if ($totalKnownAge > 0): ?>
            <div class="row g-3">
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 fw-bold text-primary mb-1"><?=$ageStats['age_0_18'] ?? 0?></div>
                  <small class="text-primary">0-18 years</small>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: <?=getPercentage($ageStats['age_0_18'] ?? 0, $totalKnownAge)?>%"></div>
                  </div>
                  <?php if ($totalKnownAge > 10): ?>
                  <div class="small text-muted mt-1"><?=getPercentage($ageStats['age_0_18'] ?? 0, $totalKnownAge)?>%</div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 fw-bold text-info mb-1"><?=$ageStats['age_19_35'] ?? 0?></div>
                  <small class="text-info">19-35 years</small>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: <?=getPercentage($ageStats['age_19_35'] ?? 0, $totalKnownAge)?>%"></div>
                  </div>
                  <?php if ($totalKnownAge > 10): ?>
                  <div class="small text-muted mt-1"><?=getPercentage($ageStats['age_19_35'] ?? 0, $totalKnownAge)?>%</div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                <div class="h4 fw-bold mb-1" style="color: #856404;">><?=$ageStats['age_36_55'] ?? 0?></div>
                <small style="color: #856404;">36-55 years</small>
                <div class="progress mt-2" style="height: 6px;">
                  <div class="progress-bar" style="width: <?=getPercentage($ageStats['age_36_55'] ?? 0, $totalKnownAge)?>%; background-color: #856404;"></div>
                </div>
                  <?php if ($totalKnownAge > 10): ?>
                  <div class="small text-muted mt-1"><?=getPercentage($ageStats['age_36_55'] ?? 0, $totalKnownAge)?>%</div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 fw-bold text-danger mb-1"><?=$ageStats['age_56_plus'] ?? 0?></div>
                  <small class="text-danger">56+ years</small>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-danger" style="width: <?=getPercentage($ageStats['age_56_plus'] ?? 0, $totalKnownAge)?>%"></div>
                  </div>
                  <?php if ($totalKnownAge > 10): ?>
                  <div class="small text-muted mt-1"><?=getPercentage($ageStats['age_56_plus'] ?? 0, $totalKnownAge)?>%</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-person-fill h3 text-muted mb-3"></i>
              <br>No age data available
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Gender Distribution -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">Gender Distribution</h5>
        </div>
        <div class="card-body">
          <?php if ($totalKnownGender > 0): ?>
            <div class="row g-3">
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 fw-bold text-info mb-1"><?=$genderStats['male'] ?? 0?></div>
                  <small class="text-info"><i class="bi bi-gender-male me-1"></i>Male</small>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: <?=getPercentage($genderStats['male'] ?? 0, $totalKnownGender)?>%"></div>
                  </div>
                  <div class="small text-muted mt-1"><?=getPercentage($genderStats['male'] ?? 0, $totalKnownGender)?>%</div>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 fw-bold text-danger mb-1"><?=$genderStats['female'] ?? 0?></div>
                  <small class="text-danger"><i class="bi bi-gender-female me-1"></i>Female</small>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-danger" style="width: <?=getPercentage($genderStats['female'] ?? 0, $totalKnownGender)?>%"></div>
                  </div>
                  <div class="small text-muted mt-1"><?=getPercentage($genderStats['female'] ?? 0, $totalKnownGender)?>%</div>
                </div>
              </div>
            </div>
            <?php if (($genderStats['unknown'] ?? 0) > 0): ?>
            <hr class="my-3">
            <div class="text-center">
              <small class="text-muted">
                Unknown Gender: <strong><?=$genderStats['unknown'] ?? 0?></strong>
              </small>
            </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-gender-trans h3 text-muted mb-3"></i>
              <br>No gender data available
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2 justify-content-center">
    <!-- Treatment Outcomes -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">Treatment Outcomes</h5>
        </div>
        <div class="card-body">
          <!-- Positive Outcomes Row -->
          <div class="row mb-3">
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold text-success mb-1"><?=$outcomeStats['cured'] ?? 0?></div>
                <small class="text-success">✓ Cured</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold text-success mb-1"><?=$outcomeStats['treatment_completed'] ?? 0?></div>
                <small class="text-success">✓ Completed</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold mb-1" style="color: #856404;"><?=$outcomeStats['active'] ?? 0?></div>
                <small style="color: #856404;">○ Active</small>
              </div>
            </div>
          </div>

          <!-- Negative Outcomes Row -->
          <div class="row">
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold text-danger mb-1"><?=$outcomeStats['died'] ?? 0?></div>
                <small class="text-danger">✗ Died</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold text-secondary mb-1"><?=$outcomeStats['lost_to_followup'] ?? 0?></div>
                <small class="text-secondary"> ? Lost to Follow-up</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center">
                <div class="h5 fw-bold text-danger mb-1"><?=$outcomeStats['failed'] ?? 0?></div>
                <small class="text-danger">✗ Failed</small>
              </div>
            </div>
          </div>

          <?php if (!empty($outcomeStats) && (
            ($outcomeStats['transferred_out'] ?? 0) > 0 ||
            ($outcomeStats['treatment_outcome'] ?? '') !== ''
          )): ?>
          <hr class="my-3">
          <div class="text-center">
            <small class="text-muted">
              Transferred Out: <strong><?=$outcomeStats['transferred_out'] ?? 0?></strong>
            </small>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Geographic Distribution -->
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">Patients per Barangay</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($barangayStats)): ?>
            <div class="row g-3">
              <?php
              foreach (array_slice($barangayStats, 0, 5) as $stat):
                $percentage = getPercentage($stat['count'], $total);
              ?>
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold"><?=$stat['barangay']?></span>
                  <span class="badge bg-primary"><?=$stat['count']?></span>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-primary" style="width: <?=$percentage?>%"></div>
                </div>
              </div>
              <?php endforeach; ?>

              <?php if (count($barangayStats) > 5): ?>
              <div class="col-12">
                <div class="text-center mt-2">
                  <small class="text-muted">
                    And <?=count($barangayStats) - 5?> more barangays...
                  </small>
                </div>
              </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-geo-alt-fill h3 text-muted mb-3"></i>
              <br>No barangay data available
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Export/Report Actions -->
  <div class="row g-3 mt-3">
    <div class="col-12">
      <div class="card shadow-sm p-3">
        <h5>Data Export & Reports</h5>
        <div class="d-flex gap-3">
          <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_csv"
            class="btn btn-outline-primary">
            <i class="bi bi-table me-2"></i>Export Patient Details (CSV)
          </a>
          <a href="/WEBSYS_FINAL_PROJECT/public/?route=export/patients_pdf"
            class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-2"></i>Export Patient Details (PDF)
          </a>
          <a href="/WEBSYS_FINAL_PROJECT/public/?route=log/index"
            class="btn btn-outline-secondary">
            <i class="bi bi-journal-text me-2"></i>View Audit Logs
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>

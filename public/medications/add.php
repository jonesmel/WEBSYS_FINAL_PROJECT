<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:800px;">
  <div class="card shadow-sm p-4">
    <h4 class="mb-3">
      <i class="bi bi-capsule me-2"></i>Add Medication Drugs
    </h4>
    <p class="text-muted small">Add one or multiple drugs for the patient's medication regimen</p>

    <form action="/WEBSYS_FINAL_PROJECT/public/?route=medication/add" method="POST" id="medicationForm">

      <!-- Patient Selection (Shared) -->
      <div class="mb-3">
        <label class="form-label fw-bold">
          <i class="bi bi-person-fill me-1"></i>Patient
        </label>
        <select name="patient_id" class="form-select" required>
          <option value="">-- Select Patient --</option>
          <?php
            require_once __DIR__ . '/../../src/models/PatientModel.php';
            $user = $_SESSION['user'];
            if ($user['role'] === 'health_worker') {
                $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
            } else {
                $patients = PatientModel::getAll();
            }
            foreach ($patients as $p):
          ?>
            <option value="<?=$p['patient_id']?>"><?=$p['name'] ?? ''?> (<?=$p['patient_code']?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Shared Dates -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-bold">
            <i class="bi bi-calendar-event me-1"></i>Start Date
          </label>
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">
            <i class="bi bi-calendar-check me-1"></i>End Date
          </label>
          <input type="date" name="end_date" class="form-control">
        </div>
      </div>

      <!-- Drug Selection Container -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <label class="form-label fw-bold mb-0">
            <i class="bi bi-capsule-me-1"></i>Drugs to Administer
          </label>
          <button type="button" class="btn btn-outline-success btn-sm" id="addDrugBtn">
            <i class="bi bi-plus-circle me-1"></i>Add Another Drug
          </button>
        </div>

        <div id="drugsContainer">
          <!-- Drug Row Template -->
          <div class="drug-row mb-3 p-3 border rounded" data-index="0">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Drug <span class="drug-number">1</span></label>
                <select name="drugs[0]" class="form-select drug-select" required>
                  <option value="">-- Select Drug --</option>
                  <?php
                  $drugs = ["Isoniazid (INH)", "Rifampicin (RIF)", "Pyrazinamide (PZA)", "Ethambutol (EMB)", "Streptomycin (STM)"];
                  foreach ($drugs as $d):
                  ?>
                    <option value="<?=$d?>"><?=$d?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label">Notes/Dosage</label>
                <textarea name="notes[0]" class="form-control drug-notes" rows="2" placeholder="e.g., 300mg daily"></textarea>
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger remove-drug-btn" style="display: none;">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i>Add Medication(s)
        </button>
        <a href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list" class="btn btn-secondary">
          <i class="bi bi-x me-1"></i>Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let drugIndex = 1;

  // Add new drug row
  document.getElementById('addDrugBtn').addEventListener('click', function() {
    addDrugRow();
  });

  // Function to add a new drug row
  function addDrugRow(drugValue = '', notesValue = '') {
    const container = document.getElementById('drugsContainer');
    const index = drugIndex++;

    const drugRow = document.createElement('div');
    drugRow.className = 'drug-row mb-3 p-3 border rounded';
    drugRow.setAttribute('data-index', index);

    const drugOptions = <?php echo json_encode($drugs); ?>;

    drugRow.innerHTML = `
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Drug <span class="drug-number">${index + 1}</span></label>
          <select name="drugs[${index}]" class="form-select drug-select" required>
            <option value="">-- Select Drug --</option>
            ${drugOptions.map(drug =>
              `<option value="${drug}" ${drugValue === drug ? 'selected' : ''}>${drug}</option>`
            ).join('')}
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Notes/Dosage</label>
          <textarea name="notes[${index}]" class="form-control drug-notes" rows="2" placeholder="e.g., 300mg daily">${notesValue}</textarea>
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-outline-danger remove-drug-btn">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;

    container.appendChild(drugRow);
    updateRemoveButtons();
    updateDrugNumbers();
  }

  // Function to update remove button visibility
  function updateRemoveButtons() {
    const drugRows = document.querySelectorAll('.drug-row');
    if (drugRows.length > 1) {
      document.querySelectorAll('.remove-drug-btn').forEach(btn => {
        btn.style.display = 'block';
      });
    } else {
      document.querySelectorAll('.remove-drug-btn').forEach(btn => {
        btn.style.display = 'none';
      });
    }
  }

  // Function to update drug numbers
  function updateDrugNumbers() {
    document.querySelectorAll('.drug-number').forEach((el, index) => {
      el.textContent = index + 1;
    });
  }

  // Remove drug row event delegation
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-drug-btn')) {
      e.target.closest('.drug-row').remove();
      updateRemoveButtons();
      updateDrugNumbers();
    }
  });

  // Form validation
  document.getElementById('medicationForm').addEventListener('submit', function(e) {
    const patientSelect = this.querySelector('select[name="patient_id"]');
    const drugSelects = this.querySelectorAll('.drug-select');

    if (!patientSelect.value) {
      e.preventDefault();
      alert('Please select a patient.');
      return;
    }

    let hasValidDrug = false;
    drugSelects.forEach(select => {
      if (select.value) {
        hasValidDrug = true;
      }
    });

    if (!hasValidDrug) {
      e.preventDefault();
      alert('Please select at least one drug.');
      return;
    }
  });

  // Initialize state
  updateRemoveButtons();
  updateDrugNumbers();
});
</script>

<style>
.drug-row {
  transition: all 0.3s ease;
}
.drug-row:hover {
  border-color: #0d6efd !important;
  box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.25);
}
.remove-drug-btn {
  min-width: 38px;
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>

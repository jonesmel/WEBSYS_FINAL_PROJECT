// app.js - unified AJAX system (fetch + JSON)
// Supports: patients, health_workers, patient_users
// Searchable selects: barangay (AJAX), barangay_assigned (AJAX), patient_id (local)
// Usage: pages must have <form data-ajax="patients"> or "health_workers" or "patient_users"
// and table tbody classes: .patients-table-body, .hw-table-body, .patient-table-body

// debounce helper
function debounce(fn, delay = 200) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
}

// Helper function to format audit changes in JavaScript (similar to PHP version)
function formatAuditChangesJS(oldJson, newJson, action) {
    const oldData = oldJson ? JSON.parse(oldJson) : {};
    const newData = newJson ? JSON.parse(newJson) : {};

    if (!Object.keys(oldData).length && !Object.keys(newData).length) {
        return '<span class="text-muted">— Invalid data —</span>';
    }

    // Special handling for import actions
    const actionStr = typeof action === 'string' ? action : '';
    if (actionStr.indexOf('import') !== -1) {
        // Check for file key (always present) rather than inserted (might be missing)
        if (!newData || !newData.file) {
            return '<span class="text-muted">— Import data —</span>';
        }
        const count = newData.inserted || 0;
        const skipped = newData.skipped || 0;
        const file = newData.file || 'unknown file';

        const parts = [];
        if (count > 0) {
            parts.push(`inserted ${count}`);
        }
        if (skipped > 0) {
            parts.push(`skipped ${skipped}`);
        }

        // Show summary even if inserted=0 and skipped=0 (for informational purposes)
        const summary = parts.length > 0 ? parts.join(', ') : 'completed';
        return `<div class="audit-import-summary"><strong>Imported ${escapeHtml(summary)} from ${escapeHtml(file)}</strong></div>`;
    }

    // Handle case where only one is available
    if (!Object.keys(oldData).length && Object.keys(newData).length > 0) {
        return '<span class="text-muted">— Record created —</span>';
    }
    if (Object.keys(oldData).length > 0 && !Object.keys(newData).length) {
        return '<span class="text-muted">— Record deleted —</span>';
    }

    // Only compare fields that exist in BOTH old and new data
    const commonKeys = Object.keys(oldData).filter(key => key in newData);
    const changes = {};

    commonKeys.forEach(key => {
        const oldValue = oldData[key];
        const newValue = newData[key];
        if (oldValue !== newValue) {
            changes[key] = { old: oldValue, new: newValue };
        }
    });

    if (!Object.keys(changes).length) {
        return '<span class="text-muted">— No changes —</span>';
    }

    let output = '<div class="audit-changes">';
    Object.keys(changes).forEach(key => {
        const change = changes[key];
        let label = key.replace(/_/g, ' ').replace(/id/i, 'ID');

        // Field mapping for better labels - maps both raw and processed field names
        const labelMap = {
            // Direct field mappings (raw field names as they appear in DB)
            'age': 'Age',
            'sex': 'Sex',
            'barangay': 'Barangay',
            'philhealth_id': 'PhilHealth ID',
            'tb_case_number': 'TB Case Number',
            'patient_code': 'Patient Code',
            'contact_code': 'Contact Code',
            'referral_code': 'Referral Code',
            'user_barangay': 'User Barangay',
            'referring_unit': 'Referring Unit',
            'receiving_barangay': 'Receiving Barangay',
            'referral_status': 'Referral Status',
            'referral_date': 'Referral Date',
            'start_date': 'Start Date',
            'end_date': 'End Date',
            'created_at': 'Created At',
            'updated_at': 'Updated At',
            'is_verified': 'Verified',
            'is_active': 'Active',

            // Processed field mappings (after replace operations)
            'Patient id': 'Patient ID',
            'User id': 'User ID',
            'Referral id': 'Referral ID',
            'Contact id': 'Contact ID',
            'Medication id': 'Medication ID',
            'Notification id': 'Notification ID',
            'Log id': 'Log ID',
            'philhealth ID': 'PhilHealth ID',
            'Age': 'Age',
            'Sex': 'Sex',
            'Barangay': 'Barangay',
            'Created at': 'Created At',
            'Updated at': 'Updated At',
            'Start date': 'Start Date',
            'End date': 'End Date',
            'Date': 'Date',
            'Referral date': 'Referral Date',
            'Tb case number': 'TB Case Number',
            'Patient code': 'Patient Code',
            'Contact code': 'Contact Code',
            'Referral code': 'Referral Code',
            'User Barangay': 'User Barangay',
            'Referring unit': 'Referring Unit',
            'Receiving Barangay': 'Receiving Barangay',
            'Referral status': 'Referral Status',
            'Is verified': 'Verified',
            'Is active': 'Active',
            'Verification token': 'Verification Token',
            'File': 'Import File'
        };
        label = labelMap[label] || label;

        let oldDisplay = change.old;
        let newDisplay = change.new;

        if (oldDisplay === '' || oldDisplay === null) {
            oldDisplay = '<span class="text-muted">(empty)</span>';
        } else if (typeof oldDisplay === 'boolean') {
            oldDisplay = oldDisplay ? 'Yes' : 'No';
        } else if (typeof oldDisplay === 'object') {
            oldDisplay = '[Array/Object]';
        } else {
            oldDisplay = escapeHtml(String(oldDisplay));
        }

        if (newDisplay === '' || newDisplay === null) {
            newDisplay = '<span class="text-muted">(empty)</span>';
        } else if (typeof newDisplay === 'boolean') {
            newDisplay = newDisplay ? 'Yes' : 'No';
        } else if (typeof newDisplay === 'object') {
            newDisplay = '[Array/Object]';
        } else {
            newDisplay = escapeHtml(String(newDisplay));
        }

        output += `<div class="audit-change">`;
        output += `<strong>${escapeHtml(label)}:</strong> `;
        output += `<span class="old-value">${oldDisplay}</span> → <span class="new-value">${newDisplay}</span>`;
        output += `</div>`;
    });
    output += '</div>';
    return output;
}

// Clear filters helper
function clearFilters(form) {
  // Special handling for audit_logs
  const type = form.getAttribute('data-ajax');
  if (type === 'audit_logs') {
    const userIdInput = form.querySelector("input[name='user_id']");
    const actionInput = form.querySelector("input[name='action']");
    const tableInput = form.querySelector("select[name='table_name']");
    const fromInput = form.querySelector("input[name='from']");
    const toInput = form.querySelector("input[name='to']");

    if (userIdInput) {
      userIdInput.value = '';
      userIdInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (actionInput) {
      actionInput.value = '';
      actionInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (tableInput) {
      tableInput.value = '';
      tableInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (fromInput) {
      fromInput.value = '';
      fromInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (toInput) {
      toInput.value = '';
      toInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    return;
  }

  // Default handling for other forms
  const qInput = form.querySelector("input[name='q']");
  const brgySelect = form.querySelector("select[name='barangay']");
  const refBrgySelect = form.querySelector("select[name='referring_barangay']");
  if (qInput) {
    qInput.value = '';
    qInput.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (brgySelect) {
    brgySelect.value = '';
    // update the brgy input too
    const brgyWrapper = brgySelect.closest('.position-relative');
    if (brgyWrapper) {
      const brgyInput = brgyWrapper.querySelector('input.brgy-search-input');
      if (brgyInput) {
        brgyInput.value = '';
        brgyInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    brgySelect.dispatchEvent(new Event('change', { bubbles: true }));
  }
  if (refBrgySelect) {
    refBrgySelect.value = '';
    // update the ref brgy input too
    const refBrgyWrapper = refBrgySelect.closest('.position-relative');
    if (refBrgyWrapper) {
      const refBrgyInput = refBrgyWrapper.querySelector('input.brgy-search-input');
      if (refBrgyInput) {
        refBrgyInput.value = '';
        refBrgyInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    refBrgySelect.dispatchEvent(new Event('change', { bubbles: true }));
  }
}

// escape helper
function escapeHtml(s) {
  if (!s && s !== 0) return '';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// Create local (client-side) searchable dropdown for selects (patient_id)
function createSearchableLocalDropdown(selectElement, placeholder = 'Search...') {
  const wrapper = document.createElement('div');
  wrapper.classList.add('position-relative');

  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'form-control mb-1 local-search-input';
  input.placeholder = placeholder;

  const list = document.createElement('div');
  list.className = 'list-group position-absolute w-100';
  list.style.zIndex = '1000';
  list.style.maxHeight = '220px';
  list.style.overflowY = 'auto';
  list.hidden = true;

  selectElement.parentNode.insertBefore(wrapper, selectElement);
  wrapper.appendChild(input);
  wrapper.appendChild(list);
  wrapper.appendChild(selectElement);
  selectElement.style.display = 'none';

  const options = Array.from(selectElement.options).map(opt => ({ value: opt.value, text: opt.textContent }));

  function render(q) {
    list.innerHTML = '';
    const term = (q || '').toLowerCase();
    const filtered = options.filter(o => o.text.toLowerCase().includes(term));
    if (filtered.length === 0) {
      list.innerHTML = "<div class='list-group-item disabled'>No results</div>";
      list.hidden = false;
      return;
    }
    filtered.forEach(o => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'list-group-item list-group-item-action';
      item.textContent = o.text;
      item.addEventListener('click', () => {
        selectElement.value = o.value;
        input.value = o.text;
        list.hidden = true;
        // dispatch input/change events if other code listens
        selectElement.dispatchEvent(new Event('change', { bubbles: true }));
      });
      list.appendChild(item);
    });
    list.hidden = false;
  }

  input.addEventListener('input', () => render(input.value.trim()));
  input.addEventListener('focus', () => render(input.value.trim()));
  document.addEventListener('click', e => { if (!wrapper.contains(e.target)) list.hidden = true; });

  return { wrapper, input, list, realSelect: selectElement };
}

// Create AJAX-driven searchable dropdown for barangays
function createSearchableAjaxDropdown(selectElement, placeholder = 'Search...') {
  const wrapper = document.createElement('div');
  wrapper.classList.add('position-relative');

  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'form-control mb-1 brgy-search-input';
  input.placeholder = placeholder;

  const list = document.createElement('div');
  list.className = 'list-group position-absolute w-100';
  list.style.zIndex = '1000';
  list.style.maxHeight = '220px';
  list.style.overflowY = 'auto';
  list.hidden = true;

  selectElement.parentNode.insertBefore(wrapper, selectElement);
  wrapper.appendChild(input);
  wrapper.appendChild(list);
  wrapper.appendChild(selectElement);
  selectElement.style.display = 'none';

  async function search(q) {
    try {
      const url = "/WEBSYS_FINAL_PROJECT/public/?route=ajax/search_barangay&q=" + encodeURIComponent(q || '');
      const res = await fetch(url);
      const items = await res.json();
      list.innerHTML = '';
      if (!items || items.length === 0) {
        list.innerHTML = "<div class='list-group-item disabled'>No results</div>";
        list.hidden = false;
        return;
      }
      items.forEach(b => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.textContent = b;
        item.addEventListener('click', () => {
          selectElement.value = b;
          input.value = b;
          list.hidden = true;
          // sync to allow fallback
          selectElement.dispatchEvent(new Event('change', { bubbles: true }));
        });
        list.appendChild(item);
      });
      list.hidden = false;
    } catch (err) {
      console.error('barangay search error', err);
      list.innerHTML = "<div class='list-group-item disabled'>Error</div>";
      list.hidden = false;
    }
  }

  const debounced = debounce((q) => search(q), 180);

  input.addEventListener('input', () => {
    const q = input.value.trim();
    // store typed value for fallback if needed
    selectElement.setAttribute('data-typed-value', q);
    // do not set select.value here (fallback uses real options)
    debounced(q);
    // dispatch change for any listeners
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
  input.addEventListener('focus', () => search(input.value.trim()));
  document.addEventListener('click', e => { if (!wrapper.contains(e.target)) list.hidden = true; });

  return { wrapper, input, list, realSelect: selectElement };
}

// Universal init & handlers
document.addEventListener('DOMContentLoaded', () => {
  // 1) Initialize searchable selects:
  // - AJAX barangays for name=barangay and barangay_assigned
  // - LOCAL searchable for patient_id
  document.querySelectorAll('select').forEach(select => {
    // skip if already initialized
    if (select.closest('.position-relative')) return;

    const name = select.getAttribute('name');
    const placeholder = select.getAttribute('data-placeholder') || (name === 'patient_id' ? 'Search patient...' : 'Search...');

    if (name === 'barangay' || name === 'barangay_assigned') {
      createSearchableAjaxDropdown(select, placeholder);
    } else if (name === 'patient_id') {
      createSearchableLocalDropdown(select, placeholder);
    }
    // other selects untouched
  });

  // 2) Email checker (reusable)
  document.querySelectorAll("input[name='email']").forEach(emailField => {
    const form = emailField.closest('form');
    const submitBtn = form?.querySelector("button[type='submit']");
    const statusBox = form?.querySelector("#email-status");
    if (submitBtn) submitBtn.disabled = true;
    let t;
    emailField.addEventListener('input', () => {
      clearTimeout(t);
      const val = emailField.value.trim();
      if (!val) { if (statusBox) statusBox.innerHTML = ''; if (submitBtn) submitBtn.disabled = true; return; }
      t = setTimeout(() => {
        fetch("/WEBSYS_FINAL_PROJECT/public/?route=ajax/check_email&email=" + encodeURIComponent(val))
          .then(r => r.json())
          .then(data => {
            if (statusBox) statusBox.innerHTML = data.valid ? "<span class='text-success'>" + data.message + "</span>" : "<span class='text-danger'>" + data.message + "</span>";
            if (submitBtn) submitBtn.disabled = !data.valid;
          })
          .catch(err => console.error(err));
      }, 300);
    });
  });

  // 3) Universal AJAX form handler
  const mapping = {
    'health_workers':      { endpoint: 'fetch_health_workers',      tbody: '.hw-table-body',            cols: 4 },
    'patient_users':       { endpoint: 'fetch_patient_users',       tbody: '.patient-table-body',       cols: 5 },
    'patients':            { endpoint: 'fetch_patients',             tbody: '.patients-table-body',      cols: 10 },
    'contacts':            { endpoint: 'fetch_contacts',             tbody: '.contacts-table-body',      cols: 7 },
    'medications':         { endpoint: 'fetch_medications',          tbody: '.medications-table-body',   cols: 6 },
    'referrals':           { endpoint: 'fetch_referrals',            tbody: '.referrals-table-body',     cols: 7 },
    'sent_referrals':      { endpoint: 'fetch_sent_referrals',       tbody: '.sent-referrals-table-body', cols: 7 },
    'incoming_referrals':  { endpoint: 'fetch_incoming_referrals',   tbody: '.incoming-referrals-table-body', cols: 7 },
    'received_referrals':  { endpoint: 'fetch_received_referrals',   tbody: '.received-referrals-table-body', cols: 7 },
    'audit_logs':          { endpoint: 'fetch_audit_logs',           tbody: '.audit-logs-table-body',    cols: 7 }
  };

  document.querySelectorAll('form[data-ajax]').forEach(form => {
    const type = form.getAttribute('data-ajax');
    if (!mapping[type]) return;

    const cfg = mapping[type];

    // Special handling for audit_logs which has different form fields
    if (type === 'audit_logs') {
      const userIdInput = form.querySelector("input[name='user_id']");
      const actionInput = form.querySelector("input[name='action']");
      const tableInput = form.querySelector("select[name='table_name']");
      const fromInput = form.querySelector("input[name='from']");
      const toInput = form.querySelector("input[name='to']");

      const doFetch = debounce(() => {
        const userId = userIdInput ? userIdInput.value.trim() : '';
        const action = actionInput ? actionInput.value.trim() : '';
        const tableName = tableInput ? tableInput.value : '';
        const from = fromInput ? fromInput.value : '';
        const to = toInput ? toInput.value : '';

        const url = `/WEBSYS_FINAL_PROJECT/public/?route=ajax/${cfg.endpoint}&user_id=${encodeURIComponent(userId)}&action=${encodeURIComponent(action)}&table_name=${encodeURIComponent(tableName)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        const tbody = document.querySelector(cfg.tbody);
        if (tbody) tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted py-3"><i class="bi bi-arrow-repeat spinning"></i> Loading...</td></tr>`;

        fetch(url)
          .then(r => r.json())
          .then(data => {
            if (!tbody) return;
            if (!data || data.length === 0) {
              tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted">No results found.</td></tr>`;
              return;
            }

            let html = '';
            data.forEach(log => {
              let formattedChanges = '<span class="text-muted">—</span>';
              if (log.old_values || log.new_values) {
                formattedChanges = formatAuditChangesJS(log.old_values, log.new_values, log.action);
              }
              html += `<tr>
                <td>${escapeHtml(log.log_id)}</td>
                <td>${escapeHtml(log.user_id)}</td>
                <td>${escapeHtml(log.action)}</td>
                <td>${escapeHtml(log.table_name)}</td>
                <td>${escapeHtml(log.record_id ?? '')}</td>
                <td style="max-width: 300px;">${formattedChanges}</td>
                <td>${escapeHtml(log.created_at)}</td>
              </tr>`;
            });
            tbody.innerHTML = html;
            tbody.classList.add('fade-in');
          })
          .catch(err => {
            console.error('fetch error', err);
            const tbody = document.querySelector(cfg.tbody);
            if (tbody) tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-danger py-3">Error loading results</td></tr>`;
          });

        // Wire events for audit_logs special inputs
        if (userIdInput) userIdInput.addEventListener('input', doFetch);
        if (actionInput) actionInput.addEventListener('input', doFetch);
        if (tableInput) tableInput.addEventListener('change', doFetch);
        if (fromInput) fromInput.addEventListener('input', doFetch);
        if (toInput) toInput.addEventListener('input', doFetch);

        // Prevent form submit
        form.addEventListener('submit', e => { e.preventDefault(); });
      });

      // Initial data load - do this for audit_logs to handle URL parameters
      doFetch();

      return; // Don't process further for audit_logs
    }

    const qInput = form.querySelector("input[name='q']");
    // find brgy wrapper/input inside this form (searchable dropdown created earlier)
    const brgyWrapper = form.querySelector('.position-relative');
    const brgyInput = brgyWrapper ? brgyWrapper.querySelector('input.brgy-search-input') : null;
    const realSelect = form.querySelector("select[name='barangay']");
    const refBrgyWrapper = form.querySelectorAll('.position-relative')[1]; // second one for referring_barangay
    const refBrgyInput = refBrgyWrapper ? refBrgyWrapper.querySelector('input.brgy-search-input') : null;
    const refRealSelect = form.querySelector("select[name='referring_barangay']");

    const doFetch = debounce(() => {
      const q = qInput ? qInput.value.trim() : '';
      let barangay = '';
      // priority: typed value in brgyInput (live)
      if (brgyInput && brgyInput.value.trim() !== '') {
        barangay = brgyInput.value.trim();
      } else if (realSelect && realSelect.value) {
        // prefer actual select value if present
        barangay = realSelect.value;
      } else if (realSelect && realSelect.getAttribute('data-typed-value')) {
        // fallback typed attr
        barangay = realSelect.getAttribute('data-typed-value') || '';
      }

      let referring_barangay = '';
      // priority: typed value in refBrgyInput (live)
      if (refBrgyInput && refBrgyInput.value.trim() !== '') {
        referring_barangay = refBrgyInput.value.trim();
      } else if (refRealSelect && refRealSelect.value) {
        referring_barangay = refRealSelect.value;
      } else if (refRealSelect && refRealSelect.getAttribute('data-typed-value')) {
        referring_barangay = refRealSelect.getAttribute('data-typed-value') || '';
      }

      const url = `/WEBSYS_FINAL_PROJECT/public/?route=ajax/${cfg.endpoint}&q=${encodeURIComponent(q)}&barangay=${encodeURIComponent(barangay)}&referring_barangay=${encodeURIComponent(referring_barangay)}`;
      const tbody = document.querySelector(cfg.tbody);
      if (tbody) tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted py-3"><i class="bi bi-arrow-repeat spinning"></i> Loading...</td></tr>`;

      fetch(url)
        .then(r => r.json())
        .then(data => {
          if (!tbody) return;
          if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted">No results found.</td></tr>`;
            return;
          }
          // render per type
          let html = '';
          if (type === 'health_workers') {
            data.forEach(hw => {
              const v = (hw.is_verified == "1" || hw.is_verified == 1) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>';
              html += `<tr>
                <td class="text-center">${escapeHtml(hw.email)}</td>
                <td class="text-center">${v}</td>
                <td class="text-center">${escapeHtml(hw.barangay_assigned || '')}</td>
                <td class="text-center"><a href="/WEBSYS_FINAL_PROJECT/public/?route/user/delete_user&id=${hw.user_id}" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm">Delete</a></td>
              </tr>`;
            });
          } else if (type === 'patient_users') {
            data.forEach(u => {
              const v = (u.is_verified == "1" || u.is_verified == 1) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>';
              html += `<tr>
                <td class="text-center">${escapeHtml(u.email)}</td>
                <td class="text-center">${v}</td>
                <td class="text-center">${escapeHtml(u.patient_code || '')}</td>
                <td class="text-center">${escapeHtml(u.patient_barangay || '')}</td>
                <td class="text-center"><a href="/WEBSYS_FINAL_PROJECT/public/?route/user/delete_user&id=${u.user_id}" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm">Delete</a></td>
              </tr>`;
            });
          } else if (type === 'patients') {
            data.forEach(p => {
              let acct = '<span class="badge bg-success">Has Account</span>';
              if (!p.has_user) {
                if (window && window.USER_ROLE === 'super_admin') {
                  acct = `<a href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users&patient_id=${p.patient_id}" class="badge bg-secondary text-decoration-none">No Account — Create</a>`;
                } else {
                  acct = '<span class="badge bg-secondary">No Account</span>';
                }
              }
              // format PhilHealth ID - ensure it handles string/integer properly
              let philId = '';
              if (p.philhealth_id) {
                const pid = String(p.philhealth_id);
                if (pid.length >= 12) {
                  philId = `${pid.substring(0,2)}-${pid.substring(2,11)}-${pid.substring(11,12)}`;
                } else {
                  philId = pid;
                }
              } else {
                philId = '-';
              }
              // format treatment outcome badge
              const outcomes = {
                'active': 'Active',
                'cured': 'Cured',
                'treatment_completed': 'Completed',
                'died': 'Died',
                'lost_to_followup': 'Lost',
                'failed': 'Failed',
                'transferred_out': 'Transferred'
              };
              const status = outcomes[p.treatment_outcome] || p.treatment_outcome;
              let badgeClass = '';
              if (p.treatment_outcome === 'active') badgeClass = 'bg-primary';
              else if (['cured', 'treatment_completed'].includes(p.treatment_outcome)) badgeClass = 'bg-success';
              else if (p.treatment_outcome === 'died') badgeClass = 'bg-danger';
              else badgeClass = 'bg-warning';
              const statusBadge = `<span class="badge ${badgeClass} text-white">${escapeHtml(status)}</span>`;
              // render action buttons (delete only if window.USER_ROLE === 'super_admin', otherwise omit)
              let deleteBtn = '';
              if (window && window.USER_ROLE === 'super_admin') {
                deleteBtn = `<a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=${p.patient_id}" onclick="return confirm('Delete this patient?');" class="btn btn-sm btn-danger">Delete</a>`;
              }
              html += `<tr>
                <td class="text-center">${escapeHtml(p.patient_code || '')}</td>
                <td class="text-center">${escapeHtml(p.name || '')}</td>
                <td class="text-center">${escapeHtml(p.barangay || '')}</td>
                <td class="text-center">${escapeHtml(String(p.age || ''))}</td>
                <td class="text-center">${escapeHtml(p.sex || '')}</td>
                <td class="text-center">${escapeHtml(p.tb_case_number || '')}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">${escapeHtml(philId)}</td>
                <td class="text-center">${acct}</td>
                <td class="text-center"><div class="action-buttons d-flex justify-content-center gap-1"><a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=${p.patient_id}" class="btn btn-sm btn-outline-primary">View</a>${deleteBtn}</div></td>
              </tr>`;
            });
          } else if (type === 'contacts') {
            data.forEach(c => {
              html += `<tr>
                <td class="text-center">${escapeHtml(c.contact_code)}</td>
                <td class="text-center">${escapeHtml(c.barangay)}</td>
                <td class="text-center">
                  ${c.patient_id ? `<a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=${escapeHtml(c.patient_id)}" class="btn btn-sm btn-link">${escapeHtml(c.name || '')} (${escapeHtml(c.patient_code)})</a>` : '<em class="text-muted small">None</em>'}
                </td>
                <td class="text-center">${escapeHtml(c.age || '')}</td>
                <td class="text-center">${escapeHtml(c.sex || '')}</td>
                <td class="text-center">${escapeHtml(c.status)}</td>
                <td class="text-center">
                  ${c.status !== 'converted_patient' ? `<a href="/WEBSYS_FINAL_PROJECT/public/?route/contact/convert&id=${c.contact_id}" class="btn btn-sm btn-outline-warning">Convert</a>` : ''}
                </td>
              </tr>`;
            });
          } else if (type === 'medications') {
            data.forEach(m => {
              const deleteBtn = (window && window.USER_ROLE !== 'patient') ? `<a href="/WEBSYS_FINAL_PROJECT/public/?route/medication/delete&id=${m.medication_id}" onclick="return confirm('Delete medication?');" class="btn btn-sm btn-danger">Delete</a>` : '';
              const editBtn = (window && window.USER_ROLE !== 'patient') ? `<a href="/WEBSYS_FINAL_PROJECT/public/?route/medication/edit&id=${m.medication_id}" class="btn btn-sm btn-warning">Edit</a>` : '';
              html += `<tr>
                <td class="text-center">${escapeHtml(m.name || '')} (${escapeHtml(m.patient_code)})</td>
                <td class="text-center">${escapeHtml(m.drugs || '')}</td>
                <td class="text-center">${escapeHtml(m.start_date || '')}</td>
                <td class="text-center">${escapeHtml(m.end_date || '')}</td>
                <td class="text-center">${escapeHtml(m.notes || '')}</td>
                <td class="text-center">${escapeHtml(m.created_at || '')}</td>
                <td class="text-center">${editBtn} ${deleteBtn}</td>
              </tr>`;
            });
          } else if (type === 'referrals' || type === 'sent_referrals' || type === 'incoming_referrals' || type === 'received_referrals') {
            data.forEach(r => {
              const statusBadge = r.referral_status === 'received' ? '<span class="badge bg-success">Received</span>' : '<span class="badge bg-warning text-dark">Pending</span>';
              const viewBtn = `<a href="/WEBSYS_FINAL_PROJECT/public/?route/referral/view&id=${r.referral_id}" class="btn btn-sm btn-primary">View</a>`;
              const deleteBtn = (window && window.USER_ROLE === 'super_admin') ? `<a href="/WEBSYS_FINAL_PROJECT/public/?route/referral/delete&id=${r.referral_id}" onclick="return confirm('Delete this referral?');" class="btn btn-sm btn-danger">Delete</a>` : '';
              html += `<tr>
                <td class="text-center">${escapeHtml(r.referral_code)}</td>
                <td class="text-center">${escapeHtml(r.name || '')} (${escapeHtml(r.patient_code)})</td>
                <td class="text-center">${escapeHtml(r.referring_unit || '')}</td>
                <td class="text-center">${escapeHtml(r.receiving_barangay || '')}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">${escapeHtml(r.referral_date || '')}</td>
                <td class="text-center">${viewBtn} ${deleteBtn}</td>
              </tr>`;
            });
          } else if (type === 'audit_logs') {
            data.forEach(log => {
              let formattedChanges = '<span class="text-muted">—</span>';
              if (log.old_values || log.new_values) {
                // Call a simple function to format changes
                formattedChanges = formatAuditChangesJS(log.old_values, log.new_values, log.action);
              }
              html += `<tr>
                <td style="text-align: center !important;">${escapeHtml(log.log_id)}</td>
                <td style="text-align: center !important;">${escapeHtml(log.user_id)}</td>
                <td style="text-align: center !important;">${escapeHtml(log.action)}</td>
                <td style="text-align: center !important;">${escapeHtml(log.table_name)}</td>
                <td style="text-align: center !important;">${escapeHtml(log.record_id ?? '')}</td>
                <td style="text-align: center !important; max-width: 300px;">${formattedChanges}</td>
                <td style="text-align: center !important;">${escapeHtml(log.created_at)}</td>
              </tr>`;
            });
          }
          tbody.innerHTML = html;
          tbody.classList.add('fade-in');
        })
        .catch(err => {
          console.error('fetch error', err);
          if (tbody) tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-danger py-3">Error loading results</td></tr>`;
        });
    }, 220);

    // wire events
    if (qInput) qInput.addEventListener('input', doFetch);
    if (brgyInput) brgyInput.addEventListener('input', doFetch);
    if (realSelect) realSelect.addEventListener('change', doFetch);
    if (refBrgyInput) refBrgyInput.addEventListener('input', doFetch);
    if (refRealSelect) refRealSelect.addEventListener('change', doFetch);

    // this prevents normal form submit from reloading the page when JS is available
    form.addEventListener('submit', e => { e.preventDefault(); });

    // optional initial fetch to reflect GET params
    // doFetch();
  }); // end forms loop

  // 5) Add show password toggle for all password inputs
  document.querySelectorAll('input[type="password"]').forEach(pwd => {
    // Find wrapper more reliably - check common Bootstrap/form classes
    const wrapper = pwd.closest('.mb-3, .col-md-6, .input-group, .form-group, .form-floating') || pwd.parentElement;

    // Create a dedicated container for the toggle to prevent interference with feedback messages
    const inputContainer = document.createElement('div');
    inputContainer.className = 'input-group';
    inputContainer.style.position = 'relative';

    // Wrap the input within the container
    const originalParent = pwd.parentNode;
    originalParent.insertBefore(inputContainer, pwd);
    inputContainer.appendChild(pwd);

    const toggle = document.createElement('span');
    toggle.innerHTML = '<i class="bi bi-eye"></i>';
    toggle.className = 'show-password-toggle';
    toggle.style.cursor = 'pointer';
    toggle.style.position = 'absolute';
    toggle.style.right = '10px';
    toggle.style.top = '50%';
    toggle.style.transform = 'translateY(-50%)';
    toggle.style.zIndex = '10';
    toggle.style.color = '#6c757d';
    toggle.style.padding = '0 5px';
    toggle.style.userSelect = 'none';

    toggle.addEventListener('click', () => {
      const isPass = pwd.type === 'password';
      pwd.type = isPass ? 'text' : 'password';
      toggle.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });

    inputContainer.appendChild(toggle);
  });

  // 6) Secure password validation (no real-time current password checking)
  document.querySelectorAll('form[data-ajax="change_password"]').forEach(form => {
    const currentPassword = form.querySelector('input[name="current_password"]');
    const newPassword = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    const submitBtn = form.querySelector('button[type="submit"]');

    // Feedback elements (only for new password and confirm)
    const currentFeedback = form.querySelector('.current-password-feedback');
    const newFeedback = form.querySelector('.new-password-feedback');
    const confirmFeedback = form.querySelector('.confirm-password-feedback');

    let newValid = false;
    let confirmValid = false;

    // Remove current password feedback on input (security)
    if (currentFeedback) {
      currentFeedback.style.display = 'none';
    }

    const validateNewPassword = () => {
      const value = newPassword.value;
      const lengthValid = value.length >= 8;
      const notEmpty = value !== '';

      newValid = lengthValid && notEmpty;
      setFeedback(newFeedback, newValid ? '' : 'Password must be at least 8 characters', newValid);
      updateSubmitButton();
    };

    const validateConfirmPassword = () => {
      const value = confirmPassword.value;
      const matches = value === newPassword.value;
      const notEmpty = value !== '';

      confirmValid = matches && notEmpty;
      setFeedback(confirmFeedback, confirmValid ? 'Passwords match' : (value && !matches ? 'Passwords do not match' : ''), confirmValid);
      updateSubmitButton();
    };

    const setFeedback = (element, message, isValid) => {
      if (!element) return;

      element.textContent = message;
      element.style.display = message ? 'block' : 'none';

      if (isValid) {
        element.className = 'valid-feedback';
        element.innerHTML = `✅ ${message}`;
      } else {
        element.className = 'invalid-feedback';
        element.innerHTML = message ? `❌ ${message}` : '';
      }
    };

    const updateSubmitButton = () => {
      // Only enable button when basic validations are met
      const basicValid = newValid && confirmValid && currentPassword.value.length > 0;
      submitBtn.disabled = !basicValid;
      submitBtn.innerHTML = basicValid ? 'Update Password' : 'Complete All Fields';
    };

    // Remove current password validation listener (security)
    // Add validation listeners for new password fields only
    if (newPassword) {
      newPassword.addEventListener('input', () => {
        validateNewPassword();
        if (confirmPassword.value) validateConfirmPassword(); // Re-validate confirm
      });
    }
    if (confirmPassword) confirmPassword.addEventListener('input', validateConfirmPassword);
    if (currentPassword) currentPassword.addEventListener('input', updateSubmitButton); // Only for button state

    // Initial validation state
    updateSubmitButton();

    // Secure submit handler with server-side validation
    form.addEventListener('submit', e => {
      e.preventDefault();

      // Basic client-side validation only
      if (!newValid || !confirmValid || !currentPassword.value) {
        alert('Please complete all password fields and ensure they meet requirements');
        return;
      }

      // Server-side validation will handle security checks
      submitBtn.disabled = true;
      submitBtn.innerHTML = '🔄 Updating...';

      // Submit the form (server handles current password validation securely)
      form.submit();
    });
  });

  // 7) Password validation for setting new passwords (no current password required)
  document.querySelectorAll('form[data-ajax="set_password"]').forEach(form => {
    const newPassword = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    const submitBtn = form.querySelector('button[type="submit"]');

    // Feedback elements
    const newFeedback = form.querySelector('.new-password-feedback');
    const confirmFeedback = form.querySelector('.confirm-password-feedback');

    let newValid = false;
    let confirmValid = false;

    const validateNewPassword = () => {
      const value = newPassword.value;
      const lengthValid = value.length >= 8;
      const notEmpty = value !== '';

      newValid = lengthValid && notEmpty;
      setFeedback(newFeedback, newValid ? '' : 'Password must be at least 8 characters', newValid);
      updateSubmitButton();
    };

    const validateConfirmPassword = () => {
      const value = confirmPassword.value;
      const matches = value === newPassword.value;
      const notEmpty = value !== '';

      confirmValid = matches && notEmpty;
      setFeedback(confirmFeedback, confirmValid ? 'Passwords match' : (value && !matches ? 'Passwords do not match' : ''), confirmValid);
      updateSubmitButton();
    };

    const setFeedback = (element, message, isValid) => {
      if (!element) return;

      element.textContent = message;
      element.style.display = message ? 'block' : 'none';

      if (isValid) {
        element.className = 'valid-feedback';
        element.innerHTML = `✅ ${message}`;
      } else {
        element.className = 'invalid-feedback';
        element.innerHTML = message ? `❌ ${message}` : '';
      }
    };

    const updateSubmitButton = () => {
      // Only enable button when basic validations are met
      const basicValid = newValid && confirmValid;
      submitBtn.disabled = !basicValid;
      submitBtn.innerHTML = basicValid ? 'Set Password' : 'Complete All Fields';
    };

    // Add validation listeners for new password fields only
    if (newPassword) {
      newPassword.addEventListener('input', () => {
        validateNewPassword();
        if (confirmPassword.value) validateConfirmPassword(); // Re-validate confirm
      });
    }
    if (confirmPassword) confirmPassword.addEventListener('input', validateConfirmPassword);

    // Initial validation state
    updateSubmitButton();

    // Secure submit handler
    form.addEventListener('submit', e => {
      // Basic client-side validation only
      if (!newValid || !confirmValid) {
        alert('Please ensure all password fields are properly filled and match');
        return;
      }

      // Add loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '🔄 Setting Password...';

      // Submit the form - server handles all validation
      form.submit();
    });
  });
}); // end DOMContentLoaded

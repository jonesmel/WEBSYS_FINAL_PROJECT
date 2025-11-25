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
    'health_workers': { endpoint: 'fetch_health_workers', tbody: '.hw-table-body', cols: 4 },
    'patient_users':  { endpoint: 'fetch_patient_users',  tbody: '.patient-table-body', cols: 5 },
    'patients':       { endpoint: 'fetch_patients',       tbody: '.patients-table-body', cols: 7 }
  };

  document.querySelectorAll('form[data-ajax]').forEach(form => {
    const type = form.getAttribute('data-ajax');
    if (!mapping[type]) return;

    const cfg = mapping[type];
    const qInput = form.querySelector("input[name='q']");
    // find brgy wrapper/input inside this form (searchable dropdown created earlier)
    const brgyWrapper = form.querySelector('.position-relative');
    const brgyInput = brgyWrapper ? brgyWrapper.querySelector('input.brgy-search-input') : null;
    const realSelect = form.querySelector("select[name='barangay']");

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

      const url = `/WEBSYS_FINAL_PROJECT/public/?route=ajax/${cfg.endpoint}&q=${encodeURIComponent(q)}&barangay=${encodeURIComponent(barangay)}`;
      const tbody = document.querySelector(cfg.tbody);
      if (tbody) tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted py-3">Loading...</td></tr>`;

      fetch(url)
        .then(r => r.json())
        .then(data => {
          if (!tbody) return;
          if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${cfg.cols}" class="text-center text-muted">No results.</td></tr>`;
            return;
          }
          // render per type
          let html = '';
          if (type === 'health_workers') {
            data.forEach(hw => {
              const v = (hw.is_verified == "1" || hw.is_verified == 1) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>';
              html += `<tr>
                <td>${escapeHtml(hw.email)}</td>
                <td>${v}</td>
                <td>${escapeHtml(hw.barangay_assigned || '')}</td>
                <td><a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=${hw.user_id}" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm w-100">Delete</a></td>
              </tr>`;
            });
          } else if (type === 'patient_users') {
            data.forEach(u => {
              const v = (u.is_verified == "1" || u.is_verified == 1) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>';
              html += `<tr>
                <td>${escapeHtml(u.email)}</td>
                <td>${v}</td>
                <td>${escapeHtml(u.patient_code || '')}</td>
                <td>${escapeHtml(u.patient_barangay || '')}</td>
                <td><a href="/WEBSYS_FINAL_PROJECT/public/?route=user/delete_user&id=${u.user_id}" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm w-100">Delete</a></td>
              </tr>`;
            });
          } else if (type === 'patients') {
            data.forEach(p => {
              const acct = p.has_user ? '<span class="badge bg-success">Has Account</span>' : '<span class="badge bg-secondary">No Account</span>';
              // render action buttons (delete only if window.USER_ROLE === 'super_admin', otherwise omit)
              let deleteBtn = '';
              if (window && window.USER_ROLE === 'super_admin') {
                deleteBtn = `<a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/delete&id=${p.patient_id}" onclick="return confirm('Delete this patient?');" class="btn btn-sm btn-danger">Delete</a>`;
              }
              html += `<tr>
                <td>${escapeHtml(p.patient_code)}</td>
                <td>${escapeHtml(p.barangay || '')}</td>
                <td>${escapeHtml(p.age || '')}</td>
                <td>${escapeHtml(p.sex || '')}</td>
                <td>${escapeHtml(p.tb_case_number || '')}</td>
                <td>${acct}</td>
                <td><div class="action-buttons"><a href="/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=${p.patient_id}" class="btn btn-sm btn-outline-primary">View</a>${deleteBtn}</div></td>
              </tr>`;
            });
          }
          tbody.innerHTML = html;
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

    // this prevents normal form submit from reloading the page when JS is available
    form.addEventListener('submit', e => { e.preventDefault(); });

    // optional initial fetch to reflect GET params
    // doFetch();
  }); // end forms loop
}); // end DOMContentLoaded

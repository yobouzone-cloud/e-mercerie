document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('search-live');
  const list = document.getElementById('supplies-list');
  const loader = document.getElementById('search-loader');
  const quantities = {};
  const SEARCH_URL = window.SUPPLIES_SEARCH_URL || '/api/supplies/search';

  // Safety guards
  if (!list) return;

  // Initialize quantities from server-rendered inputs if any
  document.querySelectorAll('#supplies-list input[type="number"][name]').forEach(el => {
    const match = el.name.match(/items\[(\d+)\]\[quantity\]/);
    if (match) quantities[match[1]] = parseInt(el.value, 10) || 0;
  });

  function attachInputHandlers() {
    // handle numeric quantity inputs
    document.querySelectorAll('#supplies-list input[type="number"]').forEach(inputEl => {
      const id = (inputEl.dataset.id) ? inputEl.dataset.id : (inputEl.id || '').replace('quantity_', '');
      inputEl.dataset.id = id;
      inputEl.name = `items[${id}][quantity]`;
      inputEl.addEventListener('input', function() {
        quantities[id] = parseInt(this.value, 10) || 0;
      });
    });

    // handle measure text inputs
    document.querySelectorAll('#supplies-list input[data-measure="true"]').forEach(inputEl => {
      const id = (inputEl.dataset.id) ? inputEl.dataset.id : (inputEl.id || '').replace('measure_', '');
      inputEl.dataset.id = id;
      inputEl.name = `items[${id}][measure_requested]`;
    });

    // add-btn handler: increment quantity by 1
    document.querySelectorAll('.add-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const card = this.closest('.supply-card');
        if (!card) return;
        const id = card.dataset.id;
        const inputEl = card.querySelector('input[type="number"]');
        const cur = parseInt(quantities[id] || inputEl?.value || 0, 10) || 0;
        quantities[id] = cur + 1;
        if (inputEl) inputEl.value = quantities[id];
      });
    });
  }

  function renderSupplies(supplies) {
    list.innerHTML = '';
    if (!supplies.length) {
      list.innerHTML = `<div class="empty-message">Aucune fourniture trouvée.</div>`;
      return;
    }

    supplies.forEach(supply => {
      const qty = quantities[supply.id] || 0;
      // If the supply is sold by measure, render a text input for measure
      const isMeasure = (supply.sale_mode && supply.sale_mode === 'measure');
      const inputHtml = isMeasure
        ? `<label>Mesure</label><input type="text" value="" id="measure_${supply.id}" data-measure="true" />`
        : `<label>Qté</label><input type="number" min="0" value="${qty}" id="quantity_${supply.id}">`;

      const card = `
        <div class="supply-card" data-id="${supply.id}">
          <div class="supply-image">
            <img src="${supply.image_url ?? '/images/default.png'}" alt="${supply.name}">
          </div>
          <div class="supply-content">
            <h3>${supply.name}</h3>
            <p class="description">${supply.description ?? ''}</p>
            <div class="price-qty">
              <div class="quantity-group">
                ${inputHtml}
              </div>
            </div>
          </div>
        </div>`;
      list.insertAdjacentHTML('beforeend', card);
    });

    attachInputHandlers();
  }

  function fetchSupplies(query = '') {
    if (loader) loader.classList.remove('hidden');
    // build URL safely
    try {
      const base = new URL(SEARCH_URL, window.location.origin);
      console.debug('[supplies-selection] fetching', base.toString());
      if (query) base.searchParams.set('search', query);
      fetch(base.toString(), {
        method: 'GET',
        credentials: 'same-origin', // send cookies for auth-protected routes
        headers: {
          'Accept': 'application/json'
        }
      })
        .then(r => {
          if (!r.ok) {
            console.warn('[supplies-selection] non-ok response', r.status, r);
            throw new Error(`HTTP ${r.status}`);
          }
          return r.json();
        })
        .then(renderSupplies)
        .catch(err => {
          console.error('Erreur fetching supplies:', err);
          list.innerHTML = `<div class="error-message">Erreur lors de la recherche.</div>`;
        })
        .finally(() => { if (loader) loader.classList.add('hidden'); });
    } catch (e) {
      console.error('Invalid SEARCH_URL:', SEARCH_URL, e);
      if (loader) loader.classList.add('hidden');
    }
  }

  if (input) {
    input.addEventListener('input', () => {
      const query = input.value.trim();
      clearTimeout(window.searchTimer);
      window.searchTimer = setTimeout(() => fetchSupplies(query), 300);
    });
  }

  // Form submit: inject hidden inputs for positive quantities and validate
  const compareForm = document.getElementById('compare-form');
  if (compareForm) {
    compareForm.addEventListener('submit', function(e) {
      // remove old injected inputs
      document.querySelectorAll('input[data-preserve="true"]').forEach(el => el.remove());
        // gather measure inputs and quantity inputs
        const injected = [];

        // Inject measure_requested inputs present in DOM, validating format
        const measureRegex = /^\s*\d+(?:[.,]\d+)?\s*(m|cm|mm|pcs|lot|kg)?\s*$/i;
        document.querySelectorAll('#supplies-list input[data-measure="true"]').forEach(el => {
          const val = el.value && el.value.trim();
          if (val) {
            // validate
            if (!measureRegex.test(val)) {
              e.preventDefault();
              try {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                  window.Swal.fire({ icon: 'error', title: 'Format invalide', text: `La mesure « ${val} » n'est pas valide. Exemple: 2.5m ou 250cm.` });
                } else {
                  alert(`La mesure « ${val} » n'est pas valide. Exemple: 2.5m ou 250cm.`);
                }
              } catch (err) { alert(`La mesure « ${val} » n'est pas valide.`); }
              el.focus();
              return false;
            }

            const id = el.dataset.id;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `items[${id}][measure_requested]`;
            hidden.value = val;
            hidden.setAttribute('data-preserve', 'true');
            compareForm.appendChild(hidden);
            injected.push(id);
          }
        });

        // Inject positive numeric quantities
        const entries = Object.entries(quantities).map(([k,v]) => [k, parseInt(v,10)||0]);
        const positive = entries.filter(([id,qty]) => qty > 0 && !injected.includes(id));

        positive.forEach(([id, qty]) => {
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = `items[${id}][quantity]`;
          hidden.value = String(qty);
          hidden.setAttribute('data-preserve', 'true');
          compareForm.appendChild(hidden);
        });

        if (injected.length === 0 && positive.length === 0) {
          e.preventDefault();
          const showWarning = () => {
            try {
              if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                  icon: 'warning',
                  title: 'Attention',
                  text: 'Veuillez renseigner au moins une quantité ou une mesure.',
                  confirmButtonText: 'OK'
                }).then(() => {
                  const firstInput = document.querySelector('#supplies-list input');
                  if (firstInput) firstInput.focus();
                });
              } else {
                alert('Veuillez renseigner au moins une quantité ou une mesure.');
                const firstInput = document.querySelector('#supplies-list input');
                if (firstInput) firstInput.focus();
              }
            } catch (err) {
              console.error('Swal show failed:', err);
              alert('Veuillez renseigner au moins une quantité ou une mesure.');
            }
          };

          showWarning();
          return false;
        }
    });
  }

  // initial server-side DOM might already contain inputs; attach handlers and then optionally fetch to refresh
  attachInputHandlers();
  fetchSupplies();
});

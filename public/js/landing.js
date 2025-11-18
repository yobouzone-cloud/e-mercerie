// landing.js - extracted landing page JS
(function(){
  'use strict';
  const cfg = window.LANDING || {};
  const rootUrl = cfg.rootUrl || '';
  const routes = cfg.routes || {};
  const suppliesSearch = routes.suppliesSearch || '/api/fournitures/search';
  const merceriesSearch = routes.merceriesSearch || '/api/merceries/search';
  const cityQuarters = routes.cityQuarters || '/api/cities/:id/quarters';
  const pushSubscribe = routes.pushSubscribe || '';
  const assetDefaultImage = cfg.assetDefaultImage || (rootUrl + '/images/default.png');
  const csrfToken = cfg.csrfToken || '';

  // Small helpers
  function escapeHtml(text){ return String(text||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  // Intersection observer for animations
  const observer = new IntersectionObserver(entries=>{ entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('visible'); }); }, { threshold: 0.1 });
  document.querySelectorAll('.card').forEach(c=>observer.observe(c));

  // Header scroll effect: toggle .scrolled when page is scrolled past threshold
  function handleHeaderScroll() {
    try {
      const header = document.getElementById('header');
      if (!header) return;
      header.classList.toggle('scrolled', window.scrollY > 50);
    } catch (e) {}
  }
  // apply on load and on pageshow (back/forward)
  document.addEventListener('DOMContentLoaded', handleHeaderScroll);
  window.addEventListener('scroll', handleHeaderScroll, { passive: true });
  window.addEventListener('pageshow', handleHeaderScroll);

  // hide loaders (pageshow / initial)
  function hideLoaders(){ try{ ['compare-loader','merceries-loader-landing','search-loader','quarter-loader'].forEach(id=>{ const el=document.getElementById(id); if(!el) return; el.classList.remove('visible'); el.classList.add('hidden'); el.style.display='none'; }); }catch(e){} }
  hideLoaders();
  window.addEventListener('pageshow', hideLoaders);

  // persistent store backed by sessionStorage so values survive AJAX replaces and navigation
  window.SUPPLIES_STATE = (function(){
    const KEY = 'supplies_state_v1';
    let store = {};
    function load(){ try{ const raw = sessionStorage.getItem(KEY); store = raw ? JSON.parse(raw) : {}; }catch(e){ store = {}; } }
    function save(){ try{ sessionStorage.setItem(KEY, JSON.stringify(store)); }catch(e){} }
    load();
    return {
      setValue(supplyId, key, value){ if(!supplyId) return; store[supplyId] = store[supplyId] || {}; store[supplyId][key] = value; save(); },
      getValues(supplyId){ return store[supplyId] || null; },
      getAll(){ return Object.assign({}, store); },
      clear(){ store = {}; try{ sessionStorage.removeItem(KEY); }catch(e){} },
      applyToContainer(container){ try{
        const root = container || document;
        const inputs = root.querySelectorAll('.quantity-input');
        inputs.forEach(input=>{
          if(!input || !input.id) return;
          const parts = input.id.split('_');
          const key = parts[0];
          const id = parts.slice(1).join('_');
          if(!id) return;
          const vals = store[id];
          if(!vals) return;
          if(key === 'quantity' && typeof vals.quantity !== 'undefined') input.value = vals.quantity;
          if(key === 'measure' && typeof vals.measure !== 'undefined') input.value = vals.measure;
          const isMeasure = input.dataset.measure === 'true';
          const containerEl = input.closest('.quantity-controls');
          const minusBtn = containerEl && containerEl.querySelector('.quantity-btn.minus');
          const numeric = isMeasure ? parseFloat(input.value) : parseInt(input.value);
          if(minusBtn) minusBtn.disabled = !(numeric > 0);
        });
      } catch(e){} }
    };
  }());

  // DOM handlers
  document.addEventListener('DOMContentLoaded', function(){
    // quantity helpers
    function updateButtonStates(input,isMeasure){ const value=isMeasure?parseFloat(input.value):parseInt(input.value); const container=input.closest('.quantity-controls'); const minusBtn = container && container.querySelector('.quantity-btn.minus'); if(minusBtn) minusBtn.disabled = value <= 0; }

        // delegated plus/minus
        document.addEventListener('click', function(e){
          const btn = e.target.closest('.quantity-btn');
          if(!btn) return;
          const targetId = btn.dataset.target;
          const input = document.getElementById(targetId);
          if(!input) return;
          const isMeasure = input.dataset.measure === 'true';
          const step = parseFloat(btn.dataset.step) || 1;
          const isPlus = btn.classList.contains('plus');
          if(isMeasure){
            let current = parseFloat(input.value) || 0;
            const newVal = Math.max(0, current + (isPlus ? step : -step));
            input.value = newVal.toFixed(1);
          } else {
            let current = parseInt(input.value) || 0;
            const newVal = Math.max(0, current + (isPlus ? 1 : -1));
            input.value = newVal;
          }
          // visual pulse
          input.classList.add('updated'); setTimeout(()=>input.classList.remove('updated'),300);
          // persist immediately
          try{
            const parts = input.id.split('_'); const id = parts.slice(1).join('_');
            if(isMeasure) window.SUPPLIES_STATE.setValue(id,'measure', input.value);
            else window.SUPPLIES_STATE.setValue(id,'quantity', input.value);
          }catch(e){}
          // update button states immediately so minus is enabled after a plus
          try{ updateButtonStates(input, isMeasure); }catch(e){}
          // fire input so other listeners react (validation, subtotal)
          input.dispatchEvent(new Event('input', { bubbles:true }));
        });

    // input validation and persist
    document.addEventListener('input', function(e){ if(!e.target.classList.contains('quantity-input')) return; const input = e.target; const isMeasure = input.dataset.measure === 'true'; if(isMeasure){ let v = parseFloat(input.value); if(isNaN(v)||v<0) input.value='0'; else input.value = v.toFixed(1); } else { let v = parseInt(input.value); if(isNaN(v)||v<0) input.value='0'; else input.value = v; } updateButtonStates(input,isMeasure); try{ const parts=input.id.split('_'); const id=parts.slice(1).join('_'); if(id){ if(isMeasure) window.SUPPLIES_STATE.setValue(id,'measure',input.value); else window.SUPPLIES_STATE.setValue(id,'quantity',input.value); } }catch(err){} });

    // restore stored values on load
    document.querySelectorAll('.quantity-input').forEach(input=>{ const isMeasure = input.dataset.measure==='true'; updateButtonStates(input,isMeasure); try{ const parts = input.id.split('_'); const id = parts.slice(1).join('_'); const vals = window.SUPPLIES_STATE.getValues(id); if(vals){ if(isMeasure && vals.measure) input.value = vals.measure; if(!isMeasure && typeof vals.quantity!=='undefined') input.value = vals.quantity; updateButtonStates(input,isMeasure); } }catch(e){} });

    // compare loader & ensure persisted supplies across pages are submitted
    const compareForm = document.getElementById('compare-form');
    const compareLoader = document.getElementById('compare-loader');
    const submitBtn = compareForm ? compareForm.querySelector('.submit-btn') : null;
    if(compareForm && compareLoader && submitBtn){
      compareForm.addEventListener('submit', function(e){
        try{
          // Prevent double submits: if already submitting, ignore subsequent submits
          if (compareForm.dataset.submitting === '1') {
            e.preventDefault();
            return;
          }
          // prevent native submit so we can show the loader and allow the browser to paint
          e.preventDefault();
          // ensure current DOM inputs are saved to the persistent store
          document.querySelectorAll('.quantity-input').forEach(input=>{
            if(!input || !input.id) return;
            const parts = input.id.split('_'); const key = parts[0]; const id = parts.slice(1).join('_'); if(!id) return;
            if(key === 'quantity') window.SUPPLIES_STATE.setValue(id, 'quantity', input.value);
            if(key === 'measure') window.SUPPLIES_STATE.setValue(id, 'measure', input.value);
          });

          // create or reuse a hidden container to inject persisted inputs into the form
          let container = compareForm.querySelector('#persisted-supplies-inputs');
          if(!container){ container = document.createElement('div'); container.id = 'persisted-supplies-inputs'; container.style.display = 'none'; compareForm.appendChild(container); }
          container.innerHTML = '';

          const all = window.SUPPLIES_STATE.getAll();
          Object.keys(all).forEach(id => {
            const vals = all[id] || {};
            // quantity (integer)
            if(typeof vals.quantity !== 'undefined' && Number(vals.quantity) > 0){
              const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = `items[${encodeURIComponent(id)}][quantity]`; inp.value = String(vals.quantity); container.appendChild(inp);
            }
            // measure_requested (string) for measured items
            if(typeof vals.measure !== 'undefined' && parseFloat(vals.measure) > 0){
              const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = `items[${encodeURIComponent(id)}][measure_requested]`; inp.value = String(vals.measure); container.appendChild(inp);
            }
          });

          // show loader (remove .hidden and force visible style) then submit programmatically
          // mark form as submitting (prevent double submit) but keep the button enabled
          compareForm.dataset.submitting = '1';
          compareLoader.classList.remove('hidden');
          try{ compareLoader.style.display = 'flex'; }catch(e){}

          // Let the browser paint the loader then submit the form normally
          setTimeout(function(){
            try{
              compareForm.submit();
            }catch(err){
              console.warn('Failed to submit form programmatically', err);
              // clear submitting flag so fallback submit attempt isn't ignored
              try{ delete compareForm.dataset.submitting; }catch(e){}
              compareForm.dispatchEvent(new Event('submit'));
            }
          }, 25);
        }catch(err){ console.warn('Preparing persisted supplies for compare failed', err); }
      });
    }

    // Ensure we clear submitting flag when user navigates back/forward to this page
    window.addEventListener('pageshow', function(){ try{ if(compareForm && compareForm.dataset && compareForm.dataset.submitting==='1'){ delete compareForm.dataset.submitting; } }catch(e){} });

    // reset button
    const resetBtn = document.getElementById('reset-supplies-values');
    if(resetBtn){
      resetBtn.addEventListener('click', function(){
        try{
          // clear persistent store
          if(window.SUPPLIES_STATE && typeof window.SUPPLIES_STATE.clear === 'function'){
            window.SUPPLIES_STATE.clear();
          }
          // reset DOM inputs
          document.querySelectorAll('.quantity-input').forEach(input=>{
            const isMeasure = input.dataset.measure === 'true';
            input.value = isMeasure ? '0' : '0';
            input.dispatchEvent(new Event('change',{bubbles:true}));
            const card = input.closest('.supply-card');
            if(card){ const subtotal = card.querySelector('.subtotal-line'); if(subtotal) subtotal.textContent=''; }
            const container = input.closest('.quantity-controls'); const minusBtn = container && container.querySelector('.quantity-btn.minus'); if(minusBtn) minusBtn.disabled = true;
          });
        }catch(err){ console.warn('Reset supplies values failed', err); }
      });
    }

    // live search supplies
    (function(){ const suppliesInput = document.getElementById('search-live'); const suppliesList = document.getElementById('supplies-list'); const suppliesLoader = document.getElementById('search-loader'); if(!suppliesInput||!suppliesList) return; let debounce=null; suppliesInput.addEventListener('input', function(){ clearTimeout(debounce); debounce = setTimeout(async ()=>{ const q = suppliesInput.value.trim(); if(q.length===0){ window.location.reload(); return; } if(suppliesLoader) suppliesLoader.style.display='block'; try{ const url = suppliesSearch + '?search=' + encodeURIComponent(q); const res = await fetch(url, { headers: { 'Accept':'application/json' }, credentials: 'same-origin' }); if(!res.ok) throw new Error('Network'); const data = await res.json(); suppliesList.innerHTML=''; if(!data||data.length===0){ suppliesList.innerHTML = '<p class="empty-message">Aucune fourniture disponible pour le moment.</p>'; } else { data.forEach(s=>{ const isMeasure = s.sale_mode && s.sale_mode==='measure'; const unit = s.measure||'m'; const img = s.image_url || assetDefaultImage; const card = document.createElement('div'); card.className='supply-card'; card.dataset.id = s.id; card.innerHTML = '\n  <div class="supply-image"><img src="'+escapeHtml(img)+'" alt="'+escapeHtml(s.name)+'"></div>\n  <div class="supply-content">\n    <h3>'+escapeHtml(s.name)+'</h3>\n    <p class="description">'+escapeHtml(s.description||'')+'</p>\n    <div class="price-qty">\n      <div class="price"><span class="amount">'+Number(s.price).toLocaleString('fr-FR')+' FCFA'+(isMeasure?(' / '+unit):'')+'</span></div>\n      <div class="quantity-group">\n        '+(isMeasure?('<label for="measure_'+s.id+'">Mesure ('+unit+')</label><div class="quantity-controls"><button type="button" class="quantity-btn minus" data-target="measure_'+s.id+'" data-step="0.5"><i class="fas fa-minus"></i></button><input type="text" name="items['+s.id+'][measure_requested]" id="measure_'+s.id+'" value="0" data-measure="true" data-unit="'+unit+'" class="quantity-input" placeholder="0'+unit+'" /><button type="button" class="quantity-btn plus" data-target="measure_'+s.id+'" data-step="0.5"><i class="fas fa-plus"></i></button></div>') : ('<label for="quantity_'+s.id+'">Quantité</label><div class="quantity-controls"><button type="button" class="quantity-btn minus" data-target="quantity_'+s.id+'"><i class="fas fa-minus"></i></button><input type="number" name="items['+s.id+'][quantity]" id="quantity_'+s.id+'" value="0" min="0" class="quantity-input" /><button type="button" class="quantity-btn plus" data-target="quantity_'+s.id+'"><i class="fas fa-plus"></i></button></div>')) + '\n      </div>\n    </div>\n  </div>';
            suppliesList.appendChild(card); }); }
            document.querySelectorAll('.card').forEach(c=>observer.observe(c)); try{ window.SUPPLIES_STATE.applyToContainer(suppliesList);}catch(e){}
          }catch(err){ suppliesList.innerHTML = '<p class="empty-message" style="color:#b91c1c;">Erreur lors de la recherche.</p>'; } finally { if(suppliesLoader) suppliesLoader.style.display='none'; }
        },300); }); function escapeHtml(text){ const d=document.createElement('div'); d.textContent = text||''; return d.innerHTML; }
    }());

    // --- LIVE SEARCH FOR MERCERIES (landing) ---
    (function(){
      const input = document.getElementById('search-merceries-landing');
      const container = document.getElementById('merceries-container');
      const loader = document.getElementById('merceries-loader-landing');
      const endpoint = (routes && routes.merceriesSearch) ? routes.merceriesSearch : '/api/merceries/search';
      if(!input || !container) return;

      let t = null;
      input.addEventListener('input', function(){
        clearTimeout(t);
        t = setTimeout(async ()=>{
          const q = input.value.trim();
          // if empty, reload the page to restore server-rendered list
          if(q.length === 0) {
            window.location.reload();
            return;
          }

          if(loader) loader.style.display = 'inline-block';

            try{
            // server expects `search` parameter (see MerchantController::searchAjax)
            const url = endpoint + '?search=' + encodeURIComponent(q);
            const res = await fetch(url, { headers: { 'Accept':'application/json' }, credentials: 'same-origin' });
            if(!res.ok) throw new Error('Network');
            const data = await res.json();

            container.innerHTML = '';
            if(!data || data.length === 0) {
              container.innerHTML = '<div class="empty-message">Aucune mercerie trouvée.</div>';
              return;
            }

            // Render cards matching server-side blade markup
            data.forEach(m=>{
              const card = document.createElement('div');
              card.className = 'card';

              // choose avatar_url from server payload, fall back to default asset
              const imgSrc = m.avatar_url || assetDefaultImage;

              const city = m.city || '';
              const quarter = m.quarter || '';
              const locationText = city ? ('📍 ' + city + (quarter ? ' — ' + quarter : '')) : '';

              const link = rootUrl.replace(/\/$/, '') + '/merceries/' + encodeURIComponent(m.id || '');

              card.innerHTML = `
                <img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(m.name)}">
                <div class="card-content">
                  <h3>${escapeHtml(m.name)}</h3>
                  <div class="info">
                    <div class="location">${escapeHtml(locationText)}</div>
                  </div>
                  <a href="${escapeHtml(link)}" class="btn">Voir plus</a>
                </div>
              `;
              container.appendChild(card);
            });

            // re-observe for animations
            document.querySelectorAll('.card').forEach(c=>observer.observe(c));
          }catch(err){
            container.innerHTML = '<div class="empty-message" style="color:#b91c1c;">Erreur lors de la recherche.</div>';
          }finally{
            if(loader) loader.style.display = 'none';
          }
        }, 300);
      });
    }());

  }); // DOMContentLoaded end

  // AJAX pagination and delegation
  (function(){ function replaceSuppliesFromHtml(html){ try{ const parser = new DOMParser(); const doc = parser.parseFromString(html,'text/html'); const newList = doc.getElementById('supplies-list'); const newPagination = doc.querySelector('.pagination-block'); const suppliesList = document.getElementById('supplies-list');
      // DEBUG: log the extracted pagination fragment so we can see if the server returned numbered links
      try{
        if(newPagination){
          console.debug('[landing] extracted newPagination.innerHTML:', newPagination.innerHTML);
        } else {
          console.debug('[landing] no .pagination-block found in AJAX response');
        }
      }catch(de){ console.debug('[landing] failed to log newPagination', de); }

      if(newList && suppliesList) suppliesList.innerHTML = newList.innerHTML; const currentPagination = document.querySelector('.pagination-block'); if(newPagination){ if(currentPagination) currentPagination.innerHTML = newPagination.innerHTML; else { const wrapper = document.createElement('div'); wrapper.className='pagination-block'; wrapper.style.display='flex'; wrapper.style.justifyContent='center'; wrapper.style.marginTop='18px'; wrapper.innerHTML = newPagination.innerHTML; suppliesList.insertAdjacentElement('afterend', wrapper); } } else if(currentPagination) currentPagination.remove(); if(window.observer) document.querySelectorAll('.card').forEach(c=>observer.observe(c)); try{ window.SUPPLIES_STATE.applyToContainer(document.getElementById('supplies-list')); }catch(e){} suppliesList.scrollIntoView({ behavior: 'smooth', block:'start' }); }catch(e){ console.error('Failed to replace supplies list from HTML', e);} }
    async function fetchAndReplace(url,push=true){ try{ const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } }); if(!res.ok) throw new Error('Network'); const html = await res.text(); replaceSuppliesFromHtml(html); if(push) history.pushState({ajax:true}, '', url); }catch(e){ console.error('AJAX pagination error', e); window.location.href = url; }}
    document.addEventListener('click', function(e){ const a = e.target.closest('.pagination-block a'); if(!a) return; e.preventDefault(); fetchAndReplace(a.href, true); });
    window.addEventListener('popstate', function(e){ fetchAndReplace(location.href, false); });
  }());

  // small features: measure subtotal, quarter loader, profile fallback
  (function(){ function parseMeasureToMeters(str){ if(!str) return null; const s = (''+str).trim().toLowerCase(); const re = /^\s*(\d+(?:[.,]\d+)?)\s*(m|cm|mm)?\s*$/i; const m = s.match(re); if(!m) return null; const num = parseFloat(m[1].replace(',', '.')); const unit = (m[2]||'m').toLowerCase(); if(unit==='cm') return num/100.0; if(unit==='mm') return num/1000.0; return num; }
    function parsePriceFromAmountText(txt){ if(!txt) return 0; const cleaned = txt.replace(/FCFA/gi,'').replace(/[^0-9.,]/g,'').replace(/\s+/g,''); return Number(cleaned.replace(',','.'))||0; }
    function formatNumberWithSpaces(n){ return String(n).replace(/\B(?=(\d{3})+(?!\d))/g,' '); }
    function updateCardSubtotal(card){ if(!card) return; const measureInput = card.querySelector('input[data-measure="true"]'); if(!measureInput) return; const val = (measureInput.value||'').trim(); const meters = parseMeasureToMeters(val); const priceEl = card.querySelector('.price .amount'); if(!priceEl) return; const price = parsePriceFromAmountText(priceEl.textContent||priceEl.innerText||'0'); let subtotalEl = card.querySelector('.subtotal-line'); if(!subtotalEl){ subtotalEl = document.createElement('div'); subtotalEl.className='subtotal-line'; subtotalEl.style.marginTop='6px'; subtotalEl.style.fontWeight='600'; const priceQty = card.querySelector('.price-qty')||card; priceQty.appendChild(subtotalEl); } if(meters!==null && meters>0){ const subtotal = Math.round(price * meters); subtotalEl.textContent = `Sous-total : ${formatNumberWithSpaces(subtotal)} FCFA`; } else { subtotalEl.textContent = ''; } }
    document.querySelectorAll('input[data-measure="true"]').forEach(inp=>{ inp.addEventListener('input', function(){ const card = inp.closest('.supply-card'); updateCardSubtotal(card); }); });
    const suppliesList = document.getElementById('supplies-list'); if(suppliesList){ const mo = new MutationObserver(muts=>{ muts.forEach(m=>{ m.addedNodes && m.addedNodes.forEach(n=>{ if(n.nodeType===1 && n.matches('.supply-card')){ const inp = n.querySelector('input[data-measure="true"]'); if(inp) inp.addEventListener('input', ()=>updateCardSubtotal(n)); } }); }); }); mo.observe(suppliesList, { childList:true }); }
    // quarter loader
    try{ const citySelect = document.getElementById('city_id'); const quarterSelect = document.getElementById('quarter_id'); const quarterLoader = document.getElementById('quarter-loader'); if(citySelect && quarterSelect){ citySelect.addEventListener('change', async function(){ const id = this.value; quarterSelect.disabled = true; quarterLoader && quarterLoader.classList.remove('hidden'); quarterSelect.innerHTML = '<option value="">Chargement...</option>'; if(!id){ quarterSelect.innerHTML = '<option value="">Tous les quartiers</option>'; quarterSelect.disabled = true; quarterLoader && quarterLoader.classList.add('hidden'); return; } try{ const res = await fetch(`/api/cities/${encodeURIComponent(id)}/quarters`, { headers:{ 'Accept':'application/json' }, credentials:'same-origin' }); if(!res.ok) throw new Error('Network'); const data = await res.json(); quarterSelect.innerHTML = '<option value="">Tous les quartiers</option>' + data.map(q=>`<option value="${q.id}">${q.name}</option>`).join(''); }catch(err){ quarterSelect.innerHTML = '<option value="">Erreur</option>'; } finally { quarterSelect.disabled = false; quarterLoader && quarterLoader.classList.add('hidden'); } }); } }catch(e){}
    // profile fallback
    try{ const profileBtn = document.getElementById('profile'); if(profileBtn){ const menu = profileBtn.closest('.profile-box').querySelector('.dropdown-menu'); profileBtn.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); menu.classList.toggle('show'); }); document.addEventListener('click', function(e){ if(!profileBtn.contains(e.target) && !menu.contains(e.target)) menu.classList.remove('show'); }); } }catch(e){}
  }());

})();

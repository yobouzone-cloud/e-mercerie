// Simple client-side cart stored in localStorage under key 'em_cart'
(function(){
  const KEY = 'em_cart';

  function readCart(){
    try{
      const raw = localStorage.getItem(KEY);
      return raw ? JSON.parse(raw) : [];
    }catch(e){ console.error('cart read error', e); return []; }
  }
  function saveCart(cart){
    try{ localStorage.setItem(KEY, JSON.stringify(cart)); }catch(e){ console.error('cart save error', e); }
  }

  function findIndex(cart, item){
    // For measure items, include measure_requested in identity so different measures don't merge
    if (item.measure_requested !== undefined) {
      return cart.findIndex(c => c.id === item.id && c.merchant_id === item.merchant_id && (c.measure_requested ?? null) === (item.measure_requested ?? null));
    }
    return cart.findIndex(c => c.id === item.id && c.merchant_id === item.merchant_id);
  }

  function addToCart(item){
    const cart = readCart();
    const idx = findIndex(cart, item);
    if (idx === -1) {
      cart.push(item);
    } else {
      cart[idx].quantity = Number(cart[idx].quantity || 0) + Number(item.quantity || 0);
    }
    saveCart(cart);
    updateBadge();
  }

  function removeFromCart(id, merchant_id){
    let cart = readCart();
    cart = cart.filter(i => !(i.id == id && i.merchant_id == merchant_id));
    saveCart(cart);
    updateBadge();
    renderCart();
  }

  function clearCart(){ saveCart([]); updateBadge(); renderCart(); }

  function getCount(){
    return readCart().reduce((s,i)=> s + (Number(i.quantity)||0), 0);
  }

  function updateBadge(){
    const el = document.getElementById('cart-count');
    if (!el) return;
    const cnt = getCount();
    el.textContent = cnt; el.dataset.count = cnt;
    el.style.display = cnt > 0 ? 'inline-block' : 'none';
  }

  function renderCart(){
    const container = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    if (!container) return;
    const cart = readCart();
    container.innerHTML = '';
    if (!cart.length){
      container.innerHTML = '<div class="p-3 text-center text-muted">Le panier est vide.</div>';
      if (totalEl) totalEl.textContent = '0';
      return;
    }

    let total = 0;
    cart.forEach(item => {
      const line = document.createElement('div');
      line.className = 'd-flex align-items-center justify-content-between p-2 border-bottom';
      const left = document.createElement('div');
      // If item has a measure_requested, show it and compute subtotal if parsable
      let lineHtmlLeft = `<strong>${escapeHtml(item.name)}</strong><br>`;
      let linePrice = 0;
      if (item.measure_requested && String(item.measure_requested).trim().length > 0) {
        lineHtmlLeft += `<small class="text-muted">Mesure: ${escapeHtml(String(item.measure_requested))}${item.unit ? ' ' + escapeHtml(item.unit) : ''}</small>`;
        // try to parse measure to meters (supports m, cm, mm, comma decimals)
        const meters = parseMeasureToMeters(String(item.measure_requested));
        if (meters !== null) {
          linePrice = Number(item.price || 0) * meters;
          // show unit price (per meter) if unit present
          lineHtmlLeft += `<br><small class="text-muted">Prix unité: ${formatPrice(item.price)}${item.unit ? ' /' + escapeHtml(item.unit) : ''}</small>`;
        } else {
          // fallback: if quantity exists, use it
          linePrice = Number(item.price || 0) * Number(item.quantity || 0);
          lineHtmlLeft += `<br><small class="text-muted">Prix unité: ${formatPrice(item.price)}</small>`;
        }
      } else {
        lineHtmlLeft += `<small class="text-muted">Qté: ${item.quantity}</small>`;
        linePrice = Number(item.price || 0) * Number(item.quantity || 0);
        lineHtmlLeft += `<br><small class="text-muted">Prix unité: ${formatPrice(item.price)}</small>`;
      }
      const right = document.createElement('div');
      total += Math.round(linePrice || 0);
      right.innerHTML = `<div class="text-end">${formatPrice(Math.round(linePrice || 0))}<br><button class="btn btn-sm btn-link text-danger remove-cart" data-id="${item.id}" data-merchant="${item.merchant_id}" data-measure="${escapeHtml(item.measure_requested ?? '')}">Supprimer</button></div>`;
      left.innerHTML = lineHtmlLeft;
      line.appendChild(left); line.appendChild(right);
      container.appendChild(line);
    });
    if (totalEl) totalEl.textContent = formatPrice(total);

    // attach remove handlers
    container.querySelectorAll('.remove-cart').forEach(btn => {
      btn.addEventListener('click', function(){
        removeFromCart(this.dataset.id, this.dataset.merchant);
      });
    });
  }

  function formatPrice(v){
    const n = Math.round(Number(v) || 0);
    if (typeof Intl !== 'undefined') return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n) + ' FCFA';
    return n + ' FCFA';
  }

  // parse measure strings like '2.5m', '250cm', '1000mm' -> meters (float)
  function parseMeasureToMeters(str) {
    if (!str) return null;
    const s = String(str).trim().toLowerCase();
    const m = s.match(/^\s*(\d+(?:[\.,]\d+)?)\s*(m|cm|mm)?\s*$/i);
    if (!m) return null;
    const num = parseFloat(m[1].replace(',', '.'));
    const unit = (m[2] || 'm').toLowerCase();
    if (unit === 'cm') return num / 100.0;
    if (unit === 'mm') return num / 1000.0;
    return num;
  }

  function escapeHtml(str){ return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

  // Public bindings
  window.EmCart = {
    add: function(item){
      if (!item || !item.id) return;
      item.quantity = Number(item.quantity) || 0;
      if (item.quantity <= 0) return;
      addToCart(item);
      // show feedback
      try{
        if (window.Swal && typeof window.Swal.fire === 'function'){
          window.Swal.fire({ icon: 'success', title: 'Ajouté au panier', text: `${item.quantity} × ${item.name}` , timer: 1400, showConfirmButton: false });
        }
      }catch(e){}
      renderCart();
    },
    remove: removeFromCart,
    clear: clearCart,
    list: readCart,
    count: getCount,
    render: renderCart,
  };

  // Init on DOM ready
  document.addEventListener('DOMContentLoaded', function(){
    // bind add buttons (dynamic supplies can also call EmCart.add manually)
    document.body.addEventListener('click', function(e){
      const btn = e.target.closest?.('.add-to-cart');
      if (!btn) return;
      const id = btn.dataset.id;
      const merchant = btn.dataset.merchant;
      const name = btn.dataset.name || btn.dataset.label || '';
      const price = btn.dataset.price || 0;
      // try to find quantity input in the same card
        const card = btn.closest('.supply-card, .mercerie-card, .card');
      let qty = 1;
      let measureRequested = null;
      let measureUnit = null;
      if (card){
        const qinput = card.querySelector('input[type="number"]');
        if (qinput) qty = Number(qinput.value) || 0;
        const minput = card.querySelector('input[data-measure="true"], input.measure-input');
        if (minput) {
          measureRequested = (minput.value || '').toString().trim();
          measureUnit = minput.dataset.unit || null;
        }
      }
      if (!id){ console.warn('add-to-cart missing id'); return; }
      if (qty <= 0){
        try{
          if (window.Swal && typeof window.Swal.fire === 'function'){
            window.Swal.fire({ icon: 'warning', title: 'Quantité requise', text: 'Veuillez indiquer une quantité supérieure à zéro.' });
          } else { alert('Veuillez indiquer une quantité supérieure à zéro.'); }
        }catch(e){ alert('Veuillez indiquer une quantité supérieure à zéro.'); }
        return;
      }
      const itemObj = { id: id, name: name, quantity: qty, price: price, merchant_id: merchant };
      if (measureRequested) itemObj.measure_requested = measureRequested;
      if (measureUnit) itemObj.unit = measureUnit;
      EmCart.add(itemObj);
    });

    // cart button open modal
    const cartBtn = document.getElementById('cart-button');
    const cartModalEl = document.getElementById('cartModal');
    if (cartBtn && cartModalEl && typeof bootstrap !== 'undefined'){
      cartBtn.addEventListener('click', function(){
        renderCart();
        const modal = new bootstrap.Modal(cartModalEl);
        modal.show();
      });
    }

    // preview cart -> build a form and post to merchant preview route
    const previewBtn = document.getElementById('preview-cart-btn');
    if (previewBtn) {
      previewBtn.addEventListener('click', function() {
        const cart = readCart();
        if (!cart.length) {
          try { window.Swal && window.Swal.fire({ icon: 'info', title: 'Panier vide', text: 'Votre panier est vide.' }); } catch(e) {}
          return;
        }

        // ensure all items are from the same merchant
        const merchants = [...new Set(cart.map(i => String(i.merchant_id)))];
        if (merchants.length > 1) {
          try { window.Swal && window.Swal.fire({ icon: 'warning', title: 'Plusieurs merceries', text: 'Le panier contient des articles provenant de plusieurs merceries. Prévisualisez une mercerie à la fois.' }); } catch(e) {}
          return;
        }

        const merchantId = merchants[0];

        // build and submit a form to /couturier/merceries/{merchantId}/preview (POST)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/couturier/merceries/${merchantId}/preview`;

        // csrf token
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const csrfInput = document.createElement('input'); csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrf; form.appendChild(csrfInput);

        // append items as items[<idx>][merchant_supply_id] and either quantity or measure_requested
        cart.forEach((it, idx) => {
          const mi = document.createElement('input'); mi.type = 'hidden'; mi.name = `items[${idx}][merchant_supply_id]`; mi.value = String(it.id); form.appendChild(mi);
          if (it.measure_requested && String(it.measure_requested).trim().length > 0) {
            const mr = document.createElement('input'); mr.type = 'hidden'; mr.name = `items[${idx}][measure_requested]`; mr.value = String(it.measure_requested); form.appendChild(mr);
          } else {
            const qi = document.createElement('input'); qi.type = 'hidden'; qi.name = `items[${idx}][quantity]`; qi.value = String(it.quantity); form.appendChild(qi);
          }
        });

        document.body.appendChild(form);
        form.submit();
      });
    }

    updateBadge(); renderCart();
  });
})();

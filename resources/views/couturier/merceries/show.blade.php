@extends('layouts.app')

@section('content')
<!-- 🔹 En-tête principale pleine largeur -->
<div class="page-title text-center py-4">
    <h1>{{ $mercerie->name }}</h1>
</div>

<div class="supplies-container">
    <!-- Barre d'action -->
    <div class="header-section">
        <a href="{{ route('supplies.selection') }}" class="soft-btn light">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <!-- Barre de recherche -->
    <div class="search-wrapper">
        <div class="search-bar">
            <i class="fa fa-search"></i>
            <input type="text" id="search-live" placeholder="Rechercher une fourniture..." autocomplete="off" />
            <div id="search-loader" class="loader hidden"></div>
        </div>
    </div>

    <!-- Formulaire -->
    <form action="{{ route('merceries.preview', $mercerie->id) }}" method="POST" id="orderForm">
        @csrf

        <div class="supplies-list" id="supplies-list">
            @foreach($mercerie->merchantSupplies as $supply)
                <div class="supply-card" data-id="{{ $supply->id }}">
                    <div class="supply-image">
                        <img src="{{ $supply->supply->image_url ?? asset('images/default.png') }}" alt="{{ $supply->supply->name }}">
                    </div>

                    <div class="supply-content">
                        <h3>{{ $supply->supply->name }}</h3>
                        <p class="description">
                            {{ Str::limit($supply->supply->description ?? 'Aucune description disponible.', 100) }}
                        </p>

                        <div class="price-stock mb-3">
                            <div class="price">
                                @php
                                    $isMeasure = false;
                                    // merchant-level sale_mode takes precedence
                                    if (!empty($supply->sale_mode) && $supply->sale_mode === 'measure') {
                                        $isMeasure = true;
                                    }
                                    // fallback to global supply sale_mode
                                    if (! $isMeasure && !empty($supply->supply->sale_mode) && $supply->supply->sale_mode === 'measure') {
                                        $isMeasure = true;
                                    }
                                    $unit = $supply->measure ?? $supply->supply->measure ?? 'm';
                                @endphp
                                <span class="amount">{{ number_format($supply->price, 0, ',', ' ') }} FCFA{{ $isMeasure ? ' / ' . $unit : '' }}</span>
                            </div>
                            <div class="stock">
                                <i class="fas fa-box"></i> {{ $supply->stock_quantity }} en stock
                            </div>
                        </div>
                        @if($supply->stock_quantity > 0 && auth()->user()->isCouturier())
                        <div class="quantity-group">
                            @if($isMeasure)
                                <label for="measure_{{ $supply->id }}">Mesure ({{ $unit }})</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn minus" data-target="measure_{{ $supply->id }}" data-step="0.5">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" 
                                           name="items[{{ $loop->index }}][measure_requested]" 
                                           id="measure_{{ $supply->id }}" 
                                           value="0" 
                                           class="quantity-input"
                                           data-measure="true"
                                           data-unit="{{ $unit }}"
                                           placeholder="0{{ $unit }}" />
                                    <button type="button" class="quantity-btn plus" data-target="measure_{{ $supply->id }}" data-step="0.5">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            @else
                                <label for="quantity_{{ $supply->id }}">Quantité</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn minus" data-target="quantity_{{ $supply->id }}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           name="items[{{ $loop->index }}][quantity]" 
                                           id="quantity_{{ $supply->id }}" 
                                           value="0" 
                                           min="0" 
                                           max="{{ $supply->stock_quantity }}"
                                           class="quantity-input" />
                                    <button type="button" class="quantity-btn plus" data-target="quantity_{{ $supply->id }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            @endif
                            <input type="hidden" name="items[{{ $loop->index }}][merchant_supply_id]" value="{{ $supply->id }}">
                        </div>
                        
                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-primary add-to-cart" 
                                data-id="{{ $supply->id }}" 
                                data-merchant="{{ $mercerie->id }}" 
                                data-name="{{ addslashes($supply->supply->name) }}" 
                                data-price="{{ $supply->price }}">
                                <i class="fas fa-cart-plus me-1"></i> Ajouter au panier
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- <button type="submit" id="previewBtn" class="soft-btn submit-btn d-none">
            <i class="fas fa-eye"></i> Prévisualiser la commande
        </button> -->
    </form>
</div>

<!-- 🌈 STYLE MODERNE -->
<style>
:root {
    --primary-color: #4F0341;
    --primary-light: #7a1761;
    --secondary-color: #9333ea;
    --bg-light: #fefcff;
    --bg-white: #ffffff;
    --text-dark: #2a2a2a;
    --text-muted: #6b7280;
    --border-light: #f0f0f0;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 8px 25px rgba(0,0,0,0.1);
    --shadow-lg: 0 15px 40px rgba(0,0,0,0.15);
    --radius-sm: 12px;
    --radius-md: 20px;
    --radius-lg: 28px;
    --transition: all 0.3s ease;
}

/* --- TITRE PLEINE LARGEUR --- */
.page-title {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    color: white;
    padding: 3rem 1rem;
    margin-bottom: 0;
}

.page-title h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

/* --- CONTAINER --- */
.supplies-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

/* --- HEADER SECTION --- */
.header-section {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 2rem;
}

/* --- BARRE DE RECHERCHE --- */
.search-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 2.5rem;
}

.search-bar {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    width: 100%;
    max-width: 500px;
    display: flex;
    align-items: center;
    padding: 12px 20px;
    transition: var(--transition);
    border: 1px solid transparent;
}

.search-bar:focus-within {
    box-shadow: 0 10px 30px rgba(79, 3, 65, 0.15);
    border-color: rgba(79, 3, 65, 0.2);
    transform: translateY(-3px);
}

.search-bar i {
    color: var(--text-muted);
    margin-right: 12px;
    font-size: 1.1rem;
}

.search-bar input {
    flex: 1;
    border: 0;
    outline: 0;
    padding: 8px 12px;
    font-size: 1rem;
    color: var(--text-dark);
    background: transparent;
}

.search-bar input::placeholder {
    color: var(--text-muted);
}

.loader {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--primary-light);
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

.hidden { display: none !important; }

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* --- GRID --- */
.supplies-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    animation: fadeInUp 0.6s ease;
}

/* --- CARDS --- */
.supply-card {
    background: var(--bg-white);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--border-light);
    position: relative;
}

.supply-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.supply-image {
    overflow: hidden;
    background: var(--bg-light);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
    padding: 1.5rem;
}

.supply-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.supply-card:hover .supply-image img {
    transform: scale(1.08);
}

.supply-content {
    padding: 1.5rem;
}

.supply-content h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.supply-content .description {
    color: var(--text-muted);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    min-height: 45px;
}

.price-stock {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-light);
}

.amount {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--secondary-color);
}

.stock {
    font-size: 0.9rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* =========================================================
   QUANTITY CONTROLS - STYLES E-COMMERCE
========================================================= */
.quantity-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.quantity-group label {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 8px;
    font-weight: 600;
}

.quantity-controls {
    display: flex;
    align-items: center;
    background: var(--bg-white);
    border: 2px solid var(--border-light);
    border-radius: var(--radius-sm);
    overflow: hidden;
    transition: var(--transition);
}

.quantity-controls:focus-within {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.1);
}

.quantity-btn {
    background: var(--bg-light);
    border: none;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--text-muted);
}

.quantity-btn:hover {
    background: var(--primary-color);
    color: var(--bg-white);
}

.quantity-btn:active {
    transform: scale(0.95);
}

.quantity-btn:disabled {
    background: var(--border-light);
    color: var(--text-muted);
    cursor: not-allowed;
    transform: none;
}

.quantity-btn:disabled:hover {
    background: var(--border-light);
    color: var(--text-muted);
}

.quantity-input {
    width: 60px;
    height: 36px;
    border: none;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-dark);
    background: var(--bg-white);
    outline: none;
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input[data-measure="true"] {
    width: 80px;
}

/* Animation pour les changements de valeur */
@keyframes pulseUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.quantity-input.updated {
    animation: pulseUpdate 0.3s ease;
}

/* --- BOUTONS --- */
.soft-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.95rem;
    text-decoration: none;
}

.soft-btn.light {
    background: var(--bg-light);
    color: var(--primary-color);
    border: 2px solid var(--border-light);
}

.soft-btn.light:hover {
    background: var(--primary-color);
    color: var(--bg-white);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.soft-btn.submit-btn {
    background: var(--primary-color);
    color: var(--bg-white);
    margin: 2rem auto 0;
    display: block;
    box-shadow: 0 4px 12px rgba(79, 3, 65, 0.25);
}

.soft-btn.submit-btn:hover {
    background: var(--primary-light);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(79, 3, 65, 0.35);
}

.btn-primary {
    background: var(--primary-color);
    border: none;
    border-radius: var(--radius-sm);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: white;
    transition: var(--transition);
    width: 100%;
}

.btn-primary:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.d-grid {
    display: grid;
}

.mt-3 {
    margin-top: 1rem;
}

.me-1 {
    margin-right: 0.25rem;
}

.d-none {
    display: none !important;
}

/* --- ANIMATIONS --- */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- MESSAGE VIDE --- */
.empty-message {
    text-align: center;
    grid-column: 1 / -1;
    font-size: 1.1rem;
    color: var(--text-muted);
    padding: 40px 20px;
    background: var(--bg-white);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
    .page-title h1 {
        font-size: 2rem;
    }
    
    .supplies-container {
        padding: 0 1rem;
    }
    
    .supplies-list {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .quantity-controls {
        border-width: 1px;
    }
    
    .quantity-btn {
        width: 32px;
        height: 32px;
    }
    
    .quantity-input {
        width: 50px;
        height: 32px;
        font-size: 0.9rem;
    }
    
    .quantity-input[data-measure="true"] {
        width: 70px;
    }
    
    .search-bar {
        padding: 10px 16px;
    }
}

@media (max-width: 480px) {
    .page-title {
        padding: 2rem 1rem;
    }
    
    .page-title h1 {
        font-size: 1.8rem;
    }
    
    .price-stock {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<!-- ✅ JS AVEC CONTRÔLES DE QUANTITÉ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des contrôles de quantité avec boutons +/-
    function updateQuantity(input, change, isMeasure = false, step = 1) {
        let currentValue;
        
        if (isMeasure) {
            // Pour les mesures, on gère les décimales
            currentValue = parseFloat(input.value) || 0;
            const newValue = Math.max(0, currentValue + (change * step));
            input.value = newValue.toFixed(1);
        } else {
            // Pour les quantités, on gère les entiers
            currentValue = parseInt(input.value) || 0;
            const max = parseInt(input.max) || Infinity;
            const newValue = Math.max(0, Math.min(max, currentValue + change));
            input.value = newValue;
        }
        
        // Animation de feedback
        input.classList.add('updated');
        setTimeout(() => input.classList.remove('updated'), 300);
        
        // Mise à jour de l'état des boutons
        updateButtonStates(input, isMeasure);
        togglePreviewButton();
    }
    
    // Fonction pour mettre à jour l'état des boutons
    function updateButtonStates(input, isMeasure) {
        const value = isMeasure ? parseFloat(input.value) : parseInt(input.value);
        const container = input.closest('.quantity-controls');
        const minusBtn = container?.querySelector('.quantity-btn.minus');
        const plusBtn = container?.querySelector('.quantity-btn.plus');
        
        if (minusBtn) {
            minusBtn.disabled = value <= 0;
        }
        
        if (plusBtn && !isMeasure) {
            const max = parseInt(input.max) || Infinity;
            plusBtn.disabled = value >= max;
        }
    }
    
    // Gestion des clics sur les boutons +/-
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quantity-btn')) {
            const btn = e.target.closest('.quantity-btn');
            const targetId = btn.dataset.target;
            const input = document.getElementById(targetId);
            
            if (input) {
                const isMeasure = input.dataset.measure === 'true';
                const step = parseFloat(btn.dataset.step) || 1;
                const isPlus = btn.classList.contains('plus');
                
                updateQuantity(input, isPlus ? 1 : -1, isMeasure, step);
                
                // Déclencher l'événement change pour les écouteurs existants
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });
    
    // Validation des inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const input = e.target;
            const isMeasure = input.dataset.measure === 'true';
            
            if (isMeasure) {
                // Validation pour les mesures (nombres décimaux)
                let value = parseFloat(input.value);
                if (isNaN(value) || value < 0) {
                    input.value = '0';
                } else {
                    input.value = value.toFixed(1);
                }
            } else {
                // Validation pour les quantités (nombres entiers)
                let value = parseInt(input.value);
                const max = parseInt(input.max) || Infinity;
                if (isNaN(value) || value < 0) {
                    input.value = '0';
                } else if (value > max) {
                    input.value = max;
                } else {
                    input.value = value;
                }
            }
            
            updateButtonStates(input, isMeasure);
            togglePreviewButton();
        }
    });
    
    // Initialisation de l'état des boutons
    document.querySelectorAll('.quantity-input').forEach(input => {
        const isMeasure = input.dataset.measure === 'true';
        updateButtonStates(input, isMeasure);
    });

    // Gestion du bouton de prévisualisation
    const previewBtn = document.getElementById('previewBtn');
    
    function togglePreviewButton() {
        if (!previewBtn) return;
        
        const quantityInputs = Array.from(document.querySelectorAll('.quantity-input'));
        const measureInputs = Array.from(document.querySelectorAll('.quantity-input[data-measure="true"]'));
        const regularInputs = Array.from(document.querySelectorAll('.quantity-input:not([data-measure="true"])'));
        
        const hasQuantity = regularInputs.some(input => parseInt(input.value) > 0);
        const hasMeasure = measureInputs.some(input => parseFloat(input.value) > 0);
        
        const visible = hasQuantity || hasMeasure;
        previewBtn.classList.toggle('d-none', !visible);
    }

    // Recherche locale
    const searchInput = document.getElementById('search-live');
    const loader = document.getElementById('search-loader');
    const suppliesList = document.getElementById('supplies-list');
    let searchTimer = null;

    function setLoader(visible) {
        if (!loader) return;
        loader.classList.toggle('hidden', !visible);
    }

    function showEmptyMessage() {
        if (!suppliesList) return;
        const existing = suppliesList.querySelector('.empty-message');
        if (existing) return;
        const el = document.createElement('div');
        el.className = 'empty-message';
        el.textContent = 'Aucune fourniture trouvée.';
        suppliesList.appendChild(el);
    }

    function removeEmptyMessage() {
        if (!suppliesList) return;
        const existing = suppliesList.querySelector('.empty-message');
        if (existing) existing.remove();
    }

    function filterSupplies(query) {
        if (!suppliesList) return;
        const q = (query || '').trim().toLowerCase();
        const cards = Array.from(suppliesList.querySelectorAll('.supply-card'));
        let visibleCount = 0;
        cards.forEach(card => {
            const title = card.querySelector('h3')?.textContent?.toLowerCase() || '';
            const desc = card.querySelector('.description')?.textContent?.toLowerCase() || '';
            const match = q === '' || title.includes(q) || desc.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        if (visibleCount === 0) showEmptyMessage(); else removeEmptyMessage();
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value || '';
            setLoader(true);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                filterSupplies(q);
                setLoader(false);
            }, 200);
        });
    }

    // Initialisation
    togglePreviewButton();
});
</script>
@endsection
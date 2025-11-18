@extends('layouts.app')

@section('content')
<div class="compare-container">
    <div class="page-title">
        <h1>Comparaison des merceries</h1>
    </div>

    @if(isset($selectedCity) || isset($selectedQuarter))
        <div style="text-align:center;margin-bottom:1rem;color:#555;">
            <strong>Filtres appliqués :</strong>
            @if($selectedCity) {{ $selectedCity->name }} @endif
            @if($selectedQuarter) — {{ $selectedQuarter->name }} @endif
        </div>
    @endif

    <!-- Merceries disponibles -->
    <section class="section">
        <h2 class="section-title">Merceries disponibles</h2>
        <div class="cards-grid">
            @forelse($disponibles as $mercerie)
                <div class="mercerie-card available">
                    <div class="mercerie-header">
                        <h3>{{ $mercerie['mercerie']['name'] }}</h3>
                        @if(isset($mercerie['mercerie']['city_name']) || isset($mercerie['mercerie']['quarter_name']))
                            <div style="font-size:0.9rem;color:#666;margin-top:6px;">📍 {{ $mercerie['mercerie']['city_name'] ?? '' }}@if(!empty($mercerie['mercerie']['quarter_name'])) — {{ $mercerie['mercerie']['quarter_name'] }}@endif</div>
                        @endif
                        <span class="price">{{ number_format($mercerie['total_estime'], 0, ',', ' ') }} FCFA</span>
                    </div>

                    <ul class="details-list">
                        @foreach($mercerie['details'] as $detail)
                            <li>
                                <span class="supply">{{ $detail['supply'] }}</span>
                                @if(!empty($detail['measure_requested']))
                                    <span class="quantity">Mesure: <strong>{{ $detail['measure_requested'] }}</strong>
                                        @if(!empty($detail['quantite'])) &times; {{ $detail['quantite'] }}@endif
                                        — {{ number_format($detail['prix_unitaire'], 0, ',', ' ') }} FCFA
                                    </span>
                                @else
                                    <span class="quantity">{{ $detail['quantite'] ?? '-' }} × {{ number_format($detail['prix_unitaire'], 0, ',', ' ') }}</span>
                                @endif
                                <span class="subtotal">{{ number_format($detail['sous_total'], 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>

                    @auth
                        <form action="{{ route('orders.storeFromMerchant', $mercerie['mercerie']['id']) }}" method="POST">
                            @csrf
                            @foreach($mercerie['details'] as $index => $detail)
                                <input type="hidden" name="items[{{ $index }}][merchant_supply_id]" value="{{ $detail['merchant_supply_id'] }}">
                                @if(!empty($detail['measure_requested']))
                                    <input type="hidden" name="items[{{ $index }}][measure_requested]" value="{{ $detail['measure_requested'] }}">
                                @else
                                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $detail['quantite'] }}">
                                @endif
                            @endforeach
                            <button type="submit" class="soft-btn purple">
                                Valider cette mercerie
                            </button>
                        </form>
                    @else
                        <button type="button" class="soft-btn purple require-login" data-return="{{ request()->fullUrl() }}">
                            Se connecter pour commander
                        </button>
                    @endauth
                </div>
            @empty
                <p class="empty-text">Aucune mercerie disponible pour votre sélection.</p>
            @endforelse
        </div>
    </section>

    <!-- Merceries non disponibles -->
    <section class="section">
        <h2 class="section-title unavailable">Merceries non disponibles</h2>
        <div class="cards-grid">
            @forelse($non_disponibles as $mercerie)
                <div class="mercerie-card unavailable">
                    <div class="mercerie-header">
                        <h3>{{ $mercerie['mercerie']['name'] }}</h3>
                        @if(isset($mercerie['mercerie']['city_name']) || isset($mercerie['mercerie']['quarter_name']))
                            <div style="font-size:0.9rem;color:#666;margin-top:6px;">📍 {{ $mercerie['mercerie']['city_name'] ?? '' }}@if(!empty($mercerie['mercerie']['quarter_name'])) — {{ $mercerie['mercerie']['quarter_name'] }}@endif</div>
                        @endif
                        <span class="status-tag">Non disponible</span>
                    </div>
                    <ul class="details-list unavailable-list">
                        @foreach($mercerie['raisons'] as $raison)
                            <li>{{ $raison }}</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="empty-text">Toutes les merceries sont disponibles pour votre sélection.</p>
            @endforelse
        </div>
    </section>
</div>

<style>
:root {
    --primary-color: #4F0341;
    --secondary-color: #9333ea;
    --accent-color: #f3e8ff;
    --error-color: #e11d48;
    --background-color: #ffffff;
    --text-color: #2d2d2d;
    --light-text: #666;
    --border-color: #e5e7eb;
}

/* --- STRUCTURE --- */
.compare-container {
    max-width: 1100px;
    margin: 2.5rem auto;
    padding: 1rem 1.5rem;
    color: var(--text-color);
}

.page-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 2.5rem;
}

/* --- SECTIONS --- */
.section {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--secondary-color);
    border-left: 4px solid var(--secondary-color);
    padding-left: 0.6rem;
}

.section-title.unavailable {
    color: var(--error-color);
    border-left-color: var(--error-color);
}

/* --- GRILLE --- */
.cards-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
}

/* --- CARDS --- */
.mercerie-card {
    background: var(--background-color);
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    position: relative;
}

.mercerie-card.available:hover {
    transform: translateY(-5px);
    border-color: var(--secondary-color);
    box-shadow: 0 8px 22px rgba(147, 51, 234, 0.2);
}

.mercerie-card.unavailable {
    border-left: 5px solid var(--error-color);
    background: #fff7f8;
}

/* --- HEADER --- */
.mercerie-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.mercerie-header h3 {
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--primary-color);
}

.price {
    font-weight: 700;
    color: var(--secondary-color);
    font-size: 1rem;
}

.status-tag {
    background: #ffe6ea;
    color: var(--error-color);
    padding: 0.35rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* --- LISTE DES DÉTAILS --- */
.details-list {
    list-style: none;
    margin: 0 0 1.5rem 0;
    padding: 0;
}

.details-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px dashed var(--border-color);
    font-size: 0.93rem;
}

.details-list li:last-child {
    border-bottom: none;
}

.details-list .supply {
    font-weight: 500;
    flex: 1;
}

.details-list .quantity {
    color: var(--light-text);
    flex: 1;
    text-align: center;
    font-style: italic;
}

.details-list .subtotal {
    font-weight: 600;
    color: var(--primary-color);
    flex: 1;
    text-align: right;
}

/* --- BOUTONS --- */
.soft-btn {
    display: inline-block;
    width: 100%;
    text-align: center;
    padding: 0.8rem 1.4rem;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.3s;
    font-size: 0.95rem;
}

.soft-btn.purple {
    background: var(--primary-color);
    color: #fff;
    box-shadow: 0 4px 10px rgba(79, 3, 65, 0.2);
}

.soft-btn.purple:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

/* --- TEXTES --- */
.empty-text {
    text-align: center;
    color: var(--light-text);
    font-style: italic;
    margin-top: 2rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.require-login').forEach(function(btn) {
        btn.addEventListener('click', function () {
            try { localStorage.setItem('post_login_return', this.dataset.return || window.location.href); } catch (e) { /* ignore */ }
            window.location.href = "{{ route('login.form') }}";
        });
    });
});
</script>
@endsection

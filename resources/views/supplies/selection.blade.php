@extends('layouts.app')

@section('content')
<div class="supplies-container">
        <!-- Titre principal -->
        <div class="page-title text-center py-4">
                <h1>Sélection des fournitures</h1>
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
        <form id="compare-form" class="supplies-form" action="{{ route('merceries.compare') }}" method="POST">
          @csrf

          <button type="submit" class="soft-btn submit-btn mb-4">Comparer les merceries</button>

                          <!-- Optional: filter by city / quarter -->
                          <div style="display:flex;gap:12px;align-items:center;margin:12px 0;flex-wrap:wrap;">
                                  <div>
                                          <label for="city_id">Ville (optionnel)</label>
                                                                  <select id="city_id" name="city_id">
                                                                          <option value="">Toutes les villes</option>
                                                                          @foreach(\App\Models\City::orderBy('name')->get() as $city)
                                                                                  <option value="{{ $city->id }}" @if(old('city_id') == $city->id) selected @endif>{{ $city->name }}</option>
                                                                          @endforeach
                                                                  </select>
                                  </div>

                                  <div style="display:flex;align-items:center;gap:8px;">
                                          <div>
                                              <label for="quarter_id">Quartier (optionnel)</label>
                                                                          <select id="quarter_id" name="quarter_id" @if(!old('city_id')) disabled @endif>
                                                                                  <option value="">Tous les quartiers</option>
                                                                                  @if(old('city_id'))
                                                                                          @foreach(\App\Models\Quarter::where('city_id', old('city_id'))->orderBy('name')->get() as $q)
                                                                                                  <option value="{{ $q->id }}" @if(old('quarter_id') == $q->id) selected @endif>{{ $q->name }}</option>
                                                                                          @endforeach
                                                                                  @endif
                                                                          </select>
                                          </div>
                                          <div id="quarter-loader" class="loader hidden" style="width:18px;height:18px;margin-top:18px;margin-left:6px;"></div>
                                  </div>
                          </div>

          <div class="supplies-list" id="supplies-list">
                  @forelse($supplies as $supply)
                          <div class="supply-card" data-id="{{ $supply->id }}">
                                  <div class="supply-image">
                                          <img src="{{ $supply->image_url ?? asset('images/default.png') }}" alt="{{ $supply->name }}">
                                  </div>

                                  <div class="supply-content">
                                          <h3>{{ $supply->name }}</h3>
                                          <p class="description">{{ $supply->description }}</p>

            <div class="price-qty">
              <div class="price">
                <!-- <span class="amount">$09.00</span>
                <span class="label">Neuf seulement</span> -->
              </div>
                                                  <div class="quantity-group">
                                                        @php
                                                            $isMeasure = false;
                                                            // Admin-level sale_mode
                                                            if (!empty($supply->sale_mode) && $supply->sale_mode === 'measure') {
                                                                $isMeasure = true;
                                                            }
                                                            // Merchant-level override: if any merchant supplies sell by measure
                                                            if (! $isMeasure) {
                                                                $isMeasure = \App\Models\MerchantSupply::where('supply_id', $supply->id)->where('sale_mode', 'measure')->exists();
                                                            }
                                                        @endphp

                                                        @if($isMeasure)
                                                          <label for="measure_{{ $supply->id }}">Mesure</label>
                                                          <input type="text" name="items[{{ $supply->id }}][measure_requested]" id="measure_{{ $supply->id }}" placeholder="Ex: 2.5m ou 250cm" />
                                                        @else
                                                          <label for="quantity_{{ $supply->id }}">Qté</label>
                                                          <input type="number" min="0" name="items[{{ $supply->id }}][quantity]" id="quantity_{{ $supply->id }}" value="0" />
                                                        @endif
                                                  </div>
            </div>
                                  </div>
                          </div>
                  @empty
                          <p class="empty-message">Aucune fourniture disponible pour le moment.</p>
                  @endforelse
          </div>   
        </form>
        <!-- Loader de comparaison -->
        <div id="compare-loader" class="compare-loader hidden">
                <div class="loader-spinner"></div>
                <p>Comparaison en cours, veuillez patienter...</p>
        </div>

</div>

<style>
/* === VARIABLES GLOBALES === */
:root {
  --primary-color: #4F0341;
  --secondary-color: #7a1761;
  --gradient: linear-gradient(135deg, #4F0341, #7a1761);
  --radius: 16px;
  --shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  --transition: all 0.3s ease;
  --gray: #6b7280;
  --white: #fff;
}

/* === CONTAINER PRINCIPAL === */
.supplies-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
  animation: fadeDown 0.6s ease;
}

/* === BARRE DE RECHERCHE === */
.search-wrapper {
  display: flex;
  justify-content: center;
  margin: 1.5rem 0 2.5rem;
  padding: 0 1rem;
}

.search-bar {
  background: var(--white);
  border-radius: 50px;
  box-shadow: var(--shadow);
  width: 100%;
  max-width: 550px;
  display: flex;
  align-items: center;
  padding: 0.9rem 1.5rem;
  position: relative;
  transition: var(--transition);
}

.search-bar:focus-within {
  box-shadow: 0 0 0 3px rgba(122, 23, 97, 0.2);
}

.search-bar i {
  color: var(--primary-color);
  font-size: 1rem;
  margin-right: 0.8rem;
}

.search-bar input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 1rem;
  color: #111827;
  background: transparent;
}

.search-bar input::placeholder {
  color: #9ca3af;
}

/* === LISTE DES FOURNITURES === */
#supplies-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
  animation: fadeInUp 0.6s ease;
}

.supply-card {
  background: var(--white);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow);
  transition: var(--transition);
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.supply-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.supply-image {
  background: var(--gradient);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1.5rem;
}

.supply-image img {
  width: 80%;
  height: auto;
  transition: transform 0.4s ease;
}

.supply-card:hover img {
  transform: scale(1.05) rotate(-2deg);
}

.supply-content {
  padding: 1.2rem;
}

.supply-content h3 {
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--primary-color);
  margin-bottom: 0.4rem;
}

.supply-content .description {
  color: var(--gray);
  font-size: 0.9rem;
  margin-bottom: 1rem;
  min-height: 40px;
}

.price-qty {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.quantity-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 0.9rem;
  color: var(--gray);
}

.quantity-group input {
  width: 70px;
  text-align: center;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  padding: 0.4rem;
  transition: var(--transition);
}

.quantity-group input:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.15);
}

/* === FILTRES (Ville / Quartier) === */
form select {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 0.6rem 1rem;
  outline: none;
  transition: var(--transition);
  background: var(--white);
  font-size: 0.95rem;
}

form select:focus {
  border-color: var(--secondary-color);
  box-shadow: 0 0 0 3px rgba(122, 23, 97, 0.15);
}

/* === BOUTONS === */
.submit-btn {
  background: var(--gradient);
  border: none;
  border-radius: 30px;
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
  padding: 0.8rem 2rem;
  cursor: pointer;
  transition: var(--transition);
  display: block;
  margin: 1.5rem auto;
}

.submit-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(122, 23, 97, 0.25);
}

/* === MESSAGE VIDE === */
.empty-message {
  text-align: center;
  background: #f9f9fb;
  padding: 1.5rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  color: var(--gray);
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
  .page-title h1 { font-size: 1.6rem; }
  .search-bar { max-width: 100%; padding: 0.7rem 1.2rem; }
  form select { width: 100%; }
  .price-qty { flex-direction: column; align-items: center; }
}

@media (max-width: 480px) {
  .supply-content h3 { font-size: 1rem; }
  .quantity-group input { width: 60px; }
  .submit-btn { width: 100%; font-size: 0.95rem; padding: 0.7rem; }
}

/* === ANIMATIONS === */
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* === LOADER DE COMPARAISON === */
.compare-loader {
  position: fixed;
  inset: 0;
  background: linear-gradient(135deg, rgba(155, 92, 255, 0.9), rgba(168, 74, 255, 0.9), #4F0341);
  backdrop-filter: blur(8px);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  opacity: 1;
  transition: opacity 0.4s ease;
}
.compare-loader.hidden {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
}
.loader-spinner {
  border: 6px solid rgba(255, 255, 255, 0.3);
  border-top: 6px solid #fff;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  animation: spin 1.2s linear infinite;
  margin-bottom: 20px;
}
.compare-loader p {
  font-size: 1.2rem;
  color: #fff;
  font-weight: 600;
  animation: fadeInUp 0.8s ease;
  letter-spacing: 0.5px;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('compare-form');
        const loader = document.getElementById('compare-loader');

        if (!form || !loader) return;

        form.addEventListener('submit', (e) => {
                e.preventDefault(); // on empêche l’envoi immédiat
                loader.classList.remove('hidden');

                // Durée minimale de 3 secondes avant soumission
                setTimeout(() => {
                        form.submit();
                }, 3000);
        });
});
        // expose the API url to the external script
        window.SUPPLIES_SEARCH_URL = "{{ route('api.supplies.search') }}";
</script>

<script>
// Load quarters when city changes (with spinner)
document.addEventListener('DOMContentLoaded', function () {
                const citySelect = document.getElementById('city_id');
                const quarterSelect = document.getElementById('quarter_id');
                const quarterLoader = document.getElementById('quarter-loader');

                if (!citySelect || !quarterSelect) return;

                async function loadQuarters(cityId) {
                                quarterSelect.disabled = true;
                                quarterLoader.classList.remove('hidden');
                                quarterSelect.innerHTML = '<option value="">Chargement...</option>';
                                try {
                                                const res = await fetch(`/api/cities/${encodeURIComponent(cityId)}/quarters`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                                                if (!res.ok) throw new Error('Network');
                                                const data = await res.json();
                                                quarterSelect.innerHTML = '<option value="">Tous les quartiers</option>' + data.map(q => `<option value="${q.id}">${q.name}</option>`).join('');
                                } catch (e) {
                                                quarterSelect.innerHTML = '<option value="">Erreur</option>';
                                } finally {
                                                quarterSelect.disabled = false;
                                                quarterLoader.classList.add('hidden');
                                }
                }

                citySelect.addEventListener('change', function () {
                                const id = citySelect.value;
                                if (!id) {
                                                quarterSelect.innerHTML = '<option value="">Tous les quartiers</option>';
                                                quarterSelect.disabled = true;
                                                return;
                                }
                                loadQuarters(id);
                });
});
</script>

<script src="{{ asset('js/supplies-selection.js') }}?v={{ filemtime(public_path('js/supplies-selection.js')) }}"></script>
@endpush
@endsection

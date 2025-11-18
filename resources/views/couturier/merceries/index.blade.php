@extends('layouts.app')

@section('content')
<!-- TITRE PRINCIPAL -->
<div class="page-title text-center py-4">
    <h1>Liste des merceries</h1>
</div>

<div class="merceries-container">

    <!-- BARRE DE RECHERCHE (style modernisé) -->
    <div class="search-wrapper mb-5">
        <div class="search-bar">
            <i class="fa fa-search"></i>
            <input type="text" id="search-merceries" placeholder="Rechercher une mercerie..." autocomplete="off" />
            <div id="merceries-loader" class="loader hidden"></div>
        </div>
    </div>

    <!-- LISTE DES MERCERIES -->
    <div class="row" id="merceries-list">
        @forelse($merceries as $mercerie)
            <div class="col-md-4 mb-4 fade-in">
                <div class="mercerie-card">
                    <div class="card-image">
                        <img src="{{ $mercerie->avatar_url ?? asset('images/default-mercerie.jpg') }}" 
                             alt="{{ $mercerie->name }}">
                        <span class="card-badge">Mercerie</span>
                    </div>

                    <div class="card-content">
                        <h5 class="card-title">
                            <i class="bi bi-shop me-2 text-secondary"></i>{{ $mercerie->name }}
                        </h5>

                        <div class="card-info">
                            <p><i class="bi bi-geo-alt-fill me-2"></i>{{ $mercerie->city ?? 'Ville non spécifiée' }}@if($mercerie->quarter) — {{ $mercerie->quarter->name ?? $mercerie->quarter }}@endif</p>
                            <p><i class="bi bi-telephone-fill me-2"></i>{{ $mercerie->phone ?? 'Non renseigné' }}</p>
                        </div>

                        <a href="{{ route('merceries.show', $mercerie->id) }}" class="soft-btn purple">
                            <i class="bi bi-box-arrow-right me-1"></i> Voir les fournitures
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted mt-4">
                <em>Aucune mercerie trouvée.</em>
            </div>
        @endforelse
    </div>
</div>

<!-- === STYLES === -->
<style>
:root {
    --primary-color: #4F0341;
    --secondary-color: #9333ea;
    --background-color: #fff;
    --border-color: #f0f0f0;
    --text-color: #2d2d2d;
    --light-text: #777;
    --radius: 18px;
    --shadow: 0 6px 16px rgba(0,0,0,0.05);
    --transition: all 0.3s ease;
}

/* --- BARRE DE RECHERCHE --- */
.search-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.search-bar {
    background: #fff;
    border-radius: 50px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 500px;
    display: flex;
    align-items: center;
    padding: 0.8rem 1.5rem;
    position: relative;
    transition: var(--transition);
}

.search-bar i { color: var(--primary-color); font-size: 1rem; margin-right: 0.8rem; }
.search-bar input { flex: 1; border: none; outline: none; font-size: 1rem; color: #111827; }
.search-bar input::placeholder { color: #9ca3af; }

.loader {
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--secondary-color);
    border-radius: 50%;
    width: 18px;
    height: 18px;
    animation: spin 1s linear infinite;
    position: absolute;
    right: 1.2rem;
}
.hidden { display: none; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* --- CARTES MERCERIES --- */
.mercerie-card {
    background: var(--background-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--border-color);
    text-align: center;
}

.mercerie-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 22px rgba(79, 3, 65, 0.25);
}

.card-image {
    position: relative;
    height: 180px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.mercerie-card:hover img { transform: scale(1.05); }

.card-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--primary-color);
    color: #fff;
    padding: 0.35rem 0.9rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.card-content { padding: 1.2rem 1.5rem; }

.card-title {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
    margin-bottom: 0.8rem;
}

.card-info {
    color: var(--light-text);
    font-size: 0.95rem;
    margin-bottom: 1.2rem;
}

.card-info i { color: var(--secondary-color); }

.soft-btn {
    display: block;
    width: 100%;
    padding: 0.7rem 1.2rem;
    border-radius: 12px;
    font-weight: 600;
    text-align: center;
    font-size: 0.95rem;
    transition: var(--transition);
    text-decoration: none;
}

.soft-btn.purple {
    background: var(--primary-color);
    color: #fff;
}

.soft-btn.purple:hover {
    background: var(--secondary-color);
    transform: scale(1.03);
    box-shadow: 0 4px 10px rgba(147, 51, 234, 0.3);
}

/* --- ANIMATION --- */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.fade-in { animation: fadeInUp 0.6s ease forwards; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-merceries');
    const merceriesList = document.getElementById('merceries-list');
    const loader = document.getElementById('merceries-loader');
    let timer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const query = searchInput.value.trim();
            loader.classList.remove('hidden');

            fetch(`{{ route('api.merceries.search') }}?search=${encodeURIComponent(query)}`, { 
                credentials: 'same-origin', 
                headers: { 'Accept': 'application/json' } 
            })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(renderMerceries)
            .catch(() => {
                merceriesList.innerHTML = `
                    <div class="col-12 text-center mt-3">
                        <div class="alert alert-danger">Erreur lors de la recherche.</div>
                    </div>`;
            })
            .finally(() => loader.classList.add('hidden'));
        }, 300);
    });

    function renderMerceries(merceries) {
        merceriesList.innerHTML = '';
        if (merceries.length === 0) {
            merceriesList.innerHTML = `
                <div class="col-12 text-center mt-3">
                    <div class="alert alert-warning">Aucune mercerie trouvée.</div>
                </div>`;
            return;
        }

        merceries.forEach(mercerie => {
            merceriesList.innerHTML += `
                <div class="col-md-4 mb-4 fade-in">
                    <div class="mercerie-card">
                        <div class="card-image">
                            <img src="${mercerie.avatar_url || '/images/default-mercerie.jpg'}" alt="${mercerie.name}">
                            <span class="card-badge">Mercerie</span>
                        </div>
                        <div class="card-content">
                            <h5 class="card-title">
                                <i class="bi bi-shop me-2 text-secondary"></i>${mercerie.name}
                            </h5>
                            <div class="card-info">
                                <p><i class="bi bi-geo-alt-fill me-2"></i>${mercerie.city ?? 'Ville non spécifiée'}${mercerie.quarter ? ' — ' + mercerie.quarter : ''}</p>
                                <p><i class="bi bi-telephone-fill me-2"></i>${mercerie.phone ?? 'Non renseigné'}</p>
                            </div>
                            <a href="/couturier/merceries/${mercerie.id}" class="soft-btn purple">
                                <i class="bi bi-box-arrow-right me-1"></i> Voir les fournitures
                            </a>
                        </div>
                    </div>
                </div>`;
        });
    }
});
</script>
@endpush
@endsection

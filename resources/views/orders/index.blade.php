@extends('layouts.app')

@section('content')
<div class="orders-container">
    <div class="page-title">
        <h1>Mes commandes</h1>
    </div>

    <!-- <p class="page-subtitle">Gérez et suivez l'état de vos commandes</p> -->

    <!-- 🔍 Barre de recherche -->
    <form method="GET" action="{{ route('orders.index') }}" id="filterForm" class="filter-bar">
        <div class="filter-group">
            <div class="filter-item">
                <label for="search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Recherche
                </label>
                <input type="text" name="search" id="search" placeholder="Nom, ID ou statut..."
                       value="{{ request('search') }}">
            </div>
            <div class="filter-item">
                <label for="start_date">
                    <i class="fa-solid fa-calendar"></i>
                    Du
                </label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="filter-item">
                <label for="end_date">
                    <i class="fa-solid fa-calendar-day"></i>
                    Au
                </label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="soft-btn purple filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filtrer</span>
                </button>
                <button type="button" id="resetBtn" class="soft-btn outline reset-btn">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Réinitialiser</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Stats rapides -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ $orders->total() }}</span>
                <span class="stat-label">Total commandes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ $orders->where('status', 'pending')->count() }}</span>
                <span class="stat-label">En attente</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon confirmed">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ $orders->where('status', 'confirmed')->count() }}</span>
                <span class="stat-label">Confirmées</span>
            </div>
        </div>
    </div>

    <!-- 🧾 Liste des commandes -->
    <section class="section">
        <div class="section-header">
            <h2>Liste des commandes</h2>
            <span class="order-count">{{ $orders->count() }} commande(s)</span>
        </div>

        <div class="cards-grid">
            @forelse($orders as $order)
                <div class="order-card" data-status="{{ $order->status }}">
                    <div class="order-badge">
                        <span class="order-id">#{{ $order->id }}</span>
                        <div class="status-indicator {{ $order->status }}"></div>
                    </div>

                    <div class="order-header">
                        <div class="order-info">
                            <h3>Commande #{{ $order->id }}</h3>
                            <div class="order-meta">
                                <span class="date">
                                    <i class="fa-solid fa-calendar"></i>
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </span>
                                <span class="price">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="status-container">
                        <span class="status-badge {{ $order->status }}">
                            <i class="status-icon {{ $order->status }}"></i>
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    {{-- Afficher les infos de l'autre partie --}}
                    @php
                        $viewer = auth()->user();
                        $other = $viewer->isCouturier() ? $order->mercerie : $order->couturier;
                    @endphp

                    @if($other)
                        <div class="other-info">
                            <div class="other-avatar">
                                <img src="{{ $other->avatar_url }}" alt="avatar" class="avatar-img">
                                <div class="online-indicator"></div>
                            </div>
                            <div class="other-details">
                                <h4 class="other-name">{{ $other->name }}</h4>
                                <div class="other-contact">
                                    <span class="contact-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        {{ $other->email }}
                                    </span>
                                    @if($other->phone)
                                        <span class="contact-item">
                                            <i class="fa-solid fa-phone"></i>
                                            {{ $other->phone }}
                                        </span>
                                    @endif
                                    @if($other->city || $other->city_id)
                                        <span class="contact-item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $other->city }}{{ $other->quarter ? ' — ' . $other->quarter->name : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isMercerie() && $order->status === 'pending')
                        <div class="actions">
                            @if($order->canBeAccepted())
                                <form action="{{ route('merchant.orders.accept', $order->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="soft-btn success-btn">
                                        <i class="fa-solid fa-check"></i>
                                        Accepter
                                    </button>
                                </form>
                            @else
                                <div class="warning-alert">
                                    <i class="fa-solid fa-exclamation-triangle"></i>
                                    Stock insuffisant
                                </div>
                            @endif

                            <form action="{{ route('merchant.orders.reject', $order->id) }}" method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="soft-btn danger-btn">
                                    <i class="fa-solid fa-xmark"></i>
                                    Rejeter
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="card-footer">
                        <a href="{{ route('orders.show', $order->id) }}" class="soft-btn outline view-details">
                            <i class="fa-solid fa-eye"></i>
                            Voir les détails
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h3>Aucune commande trouvée</h3>
                    <p>Aucune commande ne correspond à vos critères de recherche.</p>
                    <button type="button" id="clearFilters" class="soft-btn purple">
                        <i class="fa-solid fa-rotate-left"></i>
                        Réinitialiser les filtres
                    </button>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Pagination -->
    @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->total() > 0)
        <div class="pagination-container">
            <div class="pagination-info">
                Affichage de {{ $orders->firstItem() }} à {{ $orders->lastItem() }} sur {{ $orders->total() }} commandes
            </div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- 🔁 Script pour reset -->
<script>
document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('search').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('filterForm').submit();
});

document.getElementById('clearFilters')?.addEventListener('click', function() {
    document.getElementById('search').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('filterForm').submit();
});
</script>

<style>
/* --- PALETTE AMÉLIORÉE --- */
:root {
    --primary-color: #4F0341;
    --primary-light: #7a1761;
    --secondary-color: #9333ea;
    --background-color: #ffffff;
    --surface-color: #f8fafc;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --border-color: #e2e8f0;
    --border-light: #f1f5f9;
    --danger-color: #dc2626;
    --danger-light: #fef2f2;
    --success-color: #16a34a;
    --success-light: #f0fdf4;
    --warning-color: #d97706;
    --warning-light: #fffbeb;
    --info-color: #0369a1;
    --info-light: #f0f9ff;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
    --shadow-md: 0 8px 25px rgba(0,0,0,0.08);
    --shadow-lg: 0 15px 40px rgba(0,0,0,0.12);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* --- CONTAINER --- */
.orders-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
    color: var(--text-primary);
}

.page-subtitle {
    font-size: 1.1rem;
    color: var(--text-secondary);
    font-weight: 400;
}

/* --- FILTRE AMÉLIORÉ --- */
.filter-bar {
    background: var(--background-color);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    padding: 2rem;
    margin-bottom: 2.5rem;
    border: 1px solid var(--border-light);
    backdrop-filter: blur(10px);
}

.filter-group {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    align-items: end;
}

.filter-item label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.filter-item label i {
    color: var(--primary-color);
    font-size: 0.8rem;
}

.filter-item input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-lg);
    border: 2px solid var(--border-color);
    font-size: 0.95rem;
    color: var(--text-primary);
    background: var(--surface-color);
    transition: var(--transition);
}

.filter-item input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.1);
    background: var(--background-color);
}

.filter-actions {
    display: flex;
    align-items: end;
    gap: 0.75rem;
}

.filter-btn, .reset-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
    flex: 1;
    justify-content: center;
}

.filter-btn {
    background: var(--primary-color);
    color: var(--background-color);
    box-shadow: 0 4px 12px rgba(79, 3, 65, 0.2);
}

.filter-btn:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 3, 65, 0.3);
}

.reset-btn {
    background: transparent;
    color: var(--text-secondary);
    border: 2px solid var(--border-color);
}

.reset-btn:hover {
    background: var(--surface-color);
    border-color: var(--text-secondary);
    transform: translateY(-2px);
}

/* --- STATISTIQUES --- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: var(--background-color);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-light);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.total { background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); }
.stat-icon.pending { background: linear-gradient(135deg, var(--warning-color), #f59e0b); }
.stat-icon.confirmed { background: linear-gradient(135deg, var(--success-color), #22c55e); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

/* --- SECTION HEADER --- */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-light);
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.order-count {
    background: var(--surface-color);
    color: var(--text-secondary);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-lg);
    font-size: 0.9rem;
    font-weight: 600;
}

/* --- GRILLE DE CARTES AMÉLIORÉE --- */
.cards-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
}

/* --- CARTE DE COMMANDE AMÉLIORÉE --- */
.order-card {
    background: var(--background-color);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    padding: 0;
    transition: var(--transition);
    border: 1px solid var(--border-light);
    overflow: hidden;
    position: relative;
}

.order-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.order-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
}

.order-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-id {
    background: var(--surface-color);
    color: var(--text-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-lg);
    font-size: 0.8rem;
    font-weight: 600;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-indicator.pending { background: var(--warning-color); }
.status-indicator.confirmed { background: var(--success-color); }
.status-indicator.rejected { background: var(--danger-color); }

.order-header {
    padding: 1.5rem 1.5rem 1rem;
}

.order-info h3 {
    color: var(--text-primary);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.order-meta .date,
.order-meta .price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.order-meta .price {
    color: var(--primary-color);
    font-weight: 700;
}

.order-meta i {
    width: 14px;
    color: var(--text-muted);
}

/* --- STATUT AMÉLIORÉ --- */
.status-container {
    padding: 0 1.5rem 1.5rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 0.85rem;
    border: 1px solid transparent;
}

.status-badge.pending {
    background: var(--warning-light);
    color: var(--warning-color);
    border-color: #fed7aa;
}

.status-badge.confirmed {
    background: var(--success-light);
    color: var(--success-color);
    border-color: #bbf7d0;
}

.status-badge.rejected {
    background: var(--danger-light);
    color: var(--danger-color);
    border-color: #fecaca;
}

.status-icon {
    font-size: 0.8rem;
}

/* --- INFO AUTRE PARTIE AMÉLIORÉE --- */
.other-info {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1.5rem;
    margin: 0 1.5rem 1.5rem;
    background: var(--surface-color);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
}

.other-avatar {
    position: relative;
    flex-shrink: 0;
}

.avatar-img {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-lg);
    object-fit: cover;
    border: 3px solid white;
    box-shadow: var(--shadow-sm);
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: var(--success-color);
    border: 2px solid white;
    border-radius: 50%;
}

.other-details {
    flex: 1;
}

.other-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.other-contact {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.contact-item i {
    width: 14px;
    color: var(--text-muted);
}

/* --- ACTIONS AMÉLIORÉES --- */
.actions {
    display: flex;
    gap: 0.75rem;
    padding: 0 1.5rem 1.5rem;
    flex-wrap: wrap;
}

.action-form {
    flex: 1;
    min-width: 120px;
}

.warning-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--warning-light);
    color: var(--warning-color);
    padding: 0.75rem 1rem;
    border-radius: var(--radius-lg);
    font-size: 0.85rem;
    font-weight: 600;
    border: 1px solid #fed7aa;
    flex: 1;
}

/* --- BOUTONS AMÉLIORÉS --- */
.soft-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
    text-decoration: none;
    width: 100%;
}

.success-btn {
    background: var(--success-color);
    color: white;
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2);
}

.success-btn:hover {
    background: #15803d;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.danger-btn {
    background: var(--danger-color);
    color: white;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
}

.danger-btn:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.view-details {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.view-details:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

/* --- PIED DE CARTE --- */
.card-footer {
    padding: 1rem 1.5rem 1.5rem;
    border-top: 1px solid var(--border-light);
}

/* --- ÉTAT VIDE AMÉLIORÉ --- */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: var(--surface-color);
    border-radius: var(--radius-xl);
    border: 2px dashed var(--border-color);
}

.empty-icon {
    font-size: 4rem;
    color: var(--border-color);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

/* --- PAGINATION AMÉLIORÉE --- */
.pagination-container {
    margin-top: 3rem;
    text-align: center;
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

/* --- RESPONSIVE AMÉLIORÉ --- */
@media (max-width: 768px) {
    .orders-container {
        padding: 0 1rem;
        margin: 1rem auto;
    }

    .page-title h1 {
        font-size: 2rem;
    }

    .filter-group {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .cards-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .order-header {
        padding: 1.25rem 1.25rem 0.75rem;
    }

    .other-info {
        flex-direction: column;
        text-align: center;
        margin: 0 1.25rem 1.25rem;
        padding: 1.25rem;
    }

    .other-avatar {
        align-self: center;
    }

    .actions {
        flex-direction: column;
        padding: 0 1.25rem 1.25rem;
    }

    .action-form {
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .order-card {
        margin: 0 -0.5rem;
    }

    .order-header,
    .status-container,
    .other-info,
    .actions,
    .card-footer {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .order-badge {
        right: 1rem;
        top: 0.75rem;
    }
}
</style>
@endsection
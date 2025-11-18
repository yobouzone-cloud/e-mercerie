@extends('layouts.app')

@section('content')
<div class="order-details-container">
    <div class="page-title">
        <h1>Détails de la commande #{{ $order->id }}</h1>
    </div>

    <div class="order-card">

        <!-- ✅ Résumé de la commande -->
        <div class="order-summary">
            <div class="summary-item">
                <span class="label">Date de commande :</span>
                <span class="value">{{ $order->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="summary-item">
                <span class="label">Statut :</span>
                <span class="value status-tag {{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="summary-item">
                <span class="label">Nombre d’articles :</span>
                <span class="value">{{ $order->items->count() }}</span>
            </div>
            <div class="summary-item">
                <span class="label">Montant total :</span>
                <span class="value total-text">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- ✅ Informations principales -->
        <div class="order-header">
            <div>
                @if(auth()->user()->isMercerie())
                    <p class="info"><strong>Couturier :</strong> {{ $order->couturier->name }}</p>
                @else
                    <p class="info"><strong>Mercerie :</strong> {{ $order->mercerie->name }}</p>
                @endif
            </div>
        </div>

        <!-- ✅ Tableau des articles -->
        <div class="table-wrapper">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Fourniture</th>
                        <th>Quantité</th>
                        <th>Mesure demandée</th>
                        <th>Prix Unitaire (FCFA)</th>
                        <th>Sous-total (FCFA)</th>
                        <th>Stock actuel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->merchantSupply->supply->name ?? $item->merchantSupply->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->measure_requested ?? '-' }}</td>
                            <td>{{ number_format($item->price, 0, ',', ' ') }}</td>
                            <td class="highlight">{{ number_format($item->subtotal, 0, ',', ' ') }}</td>
                            <td>{{ $item->merchantSupply->stock_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ✅ Actions -->
        @if(auth()->user()->isMercerie() && $order->status === 'pending')
            <div class="actions">
                <form action="{{ route('merchant.orders.accept', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="soft-btn success">
                        <i class="lni lni-checkmark-circle"></i> Accepter
                    </button>
                </form>

                <form action="{{ route('merchant.orders.reject', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="soft-btn danger">
                        <i class="lni lni-close"></i> Rejeter
                    </button>
                </form>
            </div>
        @endif

        <!-- ✅ Retour -->
        <div class="back-btn">
            <a href="{{ route('orders.index') }}" class="soft-btn light">
                <i class="lni lni-arrow-left"></i> Retour aux commandes
            </a>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4F0341;
    --secondary-color: #9333ea;
    --success-color: #10b981;
    --danger-color: #e11d48;
    --warning-color: #facc15;
    --background-color: #fff;
    --text-color: #2d2d2d;
    --light-text: #777;
    --border-color: #f0f0f0;
}

/* --- CONTENEUR GÉNÉRAL --- */
.order-details-container {
    max-width: 1100px;
    margin: 2rem auto;
    padding: 1rem;
    color: var(--text-color);
}

/* --- CARD PRINCIPALE --- */
.order-card {
    background: var(--background-color);
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    padding: 2rem;
}

/* --- RÉSUMÉ DE COMMANDE --- */
.order-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    background: #f9f5ff;
    border-radius: 12px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--secondary-color);
}

.summary-item {
    display: flex;
    flex-direction: column;
}

.summary-item .label {
    font-weight: 500;
    color: var(--light-text);
    font-size: 0.9rem;
}

.summary-item .value {
    font-weight: 600;
    color: var(--text-color);
    font-size: 1rem;
}

.total-text {
    color: var(--secondary-color);
    font-weight: 700;
}

/* --- STATUT --- */
.status-tag {
    display: inline-block;
    padding: 0.35rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
    width: fit-content;
}

.status-tag.pending {
    background: #fff8e1;
    color: var(--warning-color);
}

.status-tag.confirmed {
    background: #ecfdf5;
    color: var(--success-color);
}

.status-tag.rejected,
.status-tag.cancelled {
    background: #ffe6e9;
    color: var(--danger-color);
}

/* --- TABLEAU --- */
.table-wrapper {
    overflow-x: auto;
    margin-bottom: 1.5rem;
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.styled-table th, .styled-table td {
    padding: 0.8rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.styled-table th {
    color: var(--primary-color);
    font-weight: 600;
}

.highlight {
    color: var(--primary-color);
    font-weight: 600;
}

/* --- BOUTONS --- */
.actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.soft-btn {
    display: inline-block;
    padding: 0.7rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.3s;
    font-size: 0.95rem;
    text-align: center;
}

.soft-btn.success {
    background: var(--success-color);
    color: var(--background-color);
}

.soft-btn.success:hover {
    background: #059669;
    transform: scale(1.03);
}

.soft-btn.danger {
    background: var(--danger-color);
    color: var(--background-color);
}

.soft-btn.danger:hover {
    background: #b91c1c;
    transform: scale(1.03);
}

.soft-btn.light {
    background: var(--primary-color);
    color: #fff;
}

.soft-btn.light:hover {
    background: var(--secondary-color);
    transform: scale(1.03);
}

.back-btn {
    text-align: center;
}
</style>
@endsection

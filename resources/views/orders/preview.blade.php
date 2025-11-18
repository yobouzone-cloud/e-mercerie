@extends('layouts.app')

@section('content')
<style>
    /* ===== Titre principal ===== */
    .page-title {
        background-color: #4F0341;
        color: white;
        padding: 2rem 1rem;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(79, 3, 65, 0.3);
        margin-bottom: 2rem;
    }

    .page-title h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        word-break: break-word;
    }

    /* ===== Carte principale ===== */
    .card-custom {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    /* ===== Tableau ===== */
    table.table {
        border-radius: 10px;
        overflow: hidden;
        min-width: 600px;
    }

    table thead {
        background-color: #4F0341;
        color: #fff;
    }

    table tbody tr:hover {
        background-color: #f9f2fb;
    }

    /* ===== Boutons ===== */
    .btn-primary-custom {
        background-color: #4F0341;
        border: none;
        color: #fff;
        transition: background-color 0.3s ease;
    }

    .btn-primary-custom:hover {
        background-color: #7c0666;
    }

    .btn-secondary-custom {
        background-color: #f1f1f1;
        color: #4F0341;
        border: 1px solid #4F0341;
        transition: all 0.3s ease;
    }

    .btn-secondary-custom:hover {
        background-color: #4F0341;
        color: white;
    }

    .text-total {
        color: #4F0341;
        font-weight: bold;
    }

    /* ===== Responsivité ===== */
    @media (max-width: 992px) {
        .page-title h1 {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 768px) {
        .page-title {
            padding: 1.5rem 1rem;
        }

        .page-title h1 {
            font-size: 1.4rem;
        }

        .card-custom {
            padding: 1.2rem;
        }

        .text-total {
            text-align: center !important;
            margin-top: 1.5rem;
        }

        form#confirmOrderForm {
            flex-direction: column !important;
            align-items: center !important;
        }

        form#confirmOrderForm .btn {
            width: 100%;
            max-width: 320px;
        }
    }

    @media (max-width: 576px) {
        .page-title h1 {
            font-size: 1.2rem;
        }
    }
</style>

<div class="container my-5">
    <!-- Titre principal -->
    <div class="page-title">
        <h1>Prévisualisation de la commande - {{ $mercerie->name }}</h1>
    </div>

    <!-- Carte principale -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>Fourniture</th>
                        <th>Quantité</th>
                        <th>Mesure demandée</th>
                        <th>Prix Unitaire</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $item)
                    @php
                        // Normalize display: prefer explicit measure_requested for the measure column.
                        // If measure_requested is empty but quantity contains letters (e.g. "2.5m"),
                        // treat that quantity value as the measure and hide it from the quantity column.
                        $qty = $item['quantity'] ?? null;
                        $measure = $item['measure_requested'] ?? null;

                        if (empty($measure) && is_string($qty) && preg_match('/[a-z]/i', $qty)) {
                            $measure = $qty;
                            $qty = null;
                        }
                    @endphp
                    <tr>
                        <td>{{ $item['supply'] }}</td>
                        <td>{{ $qty !== null ? $qty : '-' }}</td>
                        <td>{{ $measure ?? '-' }}</td>
                        <td>{{ number_format($item['price'], 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h4 class="text-end mt-4 text-total">
            Total : {{ number_format($total, 0, ',', ' ') }} FCFA
        </h4>

        <!-- Formulaire de validation -->
        <form id="confirmOrderForm" 
              action="{{ route('merceries.order', $mercerie->id) }}" 
              method="POST" 
              class="mt-4 d-flex justify-content-end gap-3 flex-wrap">
            @csrf
            @foreach($details as $index => $item)
                <input type="hidden" name="items[{{ $index }}][merchant_supply_id]" value="{{ $item['merchant_supply_id'] ?? '' }}">
                <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}">
                <input type="hidden" name="items[{{ $index }}][measure_requested]" value="{{ $item['measure_requested'] ?? '' }}">
            @endforeach

            <button type="button" id="confirmOrderBtn" class="btn btn-primary-custom px-4">Valider la commande</button>
            <a href="{{ route('merceries.show', $mercerie->id) }}" class="btn btn-secondary-custom px-4">Modifier</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Import SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmOrderBtn');
    const form = document.getElementById('confirmOrderForm');

    confirmBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Confirmer la commande ?',
            text: "Souhaitez-vous vraiment valider cette commande ?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4F0341',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            background: '#fff',
            color: '#4F0341',
            customClass: {
                popup: 'rounded-4 shadow-lg',
                confirmButton: 'btn btn-primary-custom',
                cancelButton: 'btn btn-secondary-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush

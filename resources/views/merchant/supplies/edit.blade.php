@extends('layouts.app')

@section('content')
<style>
/* === STYLES GLOBAUX MODERNISÉS === */
:root {
    --primary: #4F0341;
    --secondary: #8b166a;
    --bg-light: #faf5fb;
    --text-dark: #2d2d2d;
    --radius: 16px;
    --shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}


/* === CONTENEUR PRINCIPAL === */
.card-style {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 2.5rem;
    max-width: 700px;
    margin: 0 auto;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-style:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* === FORMULAIRES === */
label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 6px;
    display: block;
}

input[type="text"],
input[type="number"] {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 6px rgba(79, 3, 65, 0.3);
    outline: none;
}

/* === BOUTONS === */
.btn-primary-custom {
    background-color: var(--primary);
    color: #fff;
    border: none;
    padding: 0.8rem 1.8rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-primary-custom:hover {
    background-color: var(--secondary);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(79, 3, 65, 0.25);
}

.btn-secondary-custom {
    background-color: #f5f5f5;
    color: var(--primary);
    border: 1px solid #d3d3d3;
    padding: 0.8rem 1.8rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-secondary-custom:hover {
    background-color: #efe5ef;
    color: var(--secondary);
}

/* === ALERTES === */
.alert-errors {
    background-color: #fff3f3;
    border-left: 5px solid #dc3545;
    color: #a00;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

/* === RESPONSIVE DESIGN === */
@media (max-width: 768px) {

    .card-style {
        padding: 1.5rem;
        margin: 0 1rem;
    }

    .btn-primary-custom,
    .btn-secondary-custom {
        width: 100%;
        justify-content: center;
        font-size: 1rem;
    }

    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<!-- === EN-TÊTE === -->
<div class="page-title">
    <h1>Modifier la Fourniture</h1>
</div>

<!-- === CONTENU PRINCIPAL === -->
<div class="card-style">
    @if ($errors->any())
        <div class="alert-errors">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="update-form" action="{{ route('merchant.supplies.update', $merchantSupply->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="supply">Fourniture :</label>
            <input type="text" id="supply" value="{{ $merchantSupply->supply->name }}" disabled>
        </div>

        <div class="mb-3">
            <label for="price">Prix (FCFA) :</label>
            <input type="number" id="price" name="price" step="0.01" min="0" 
                   value="{{ $merchantSupply->price }}" required>
        </div>

        <div class="mb-4">
            <label for="stock_quantity">Quantité en stock :</label>
            <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="0.01"
                   value="{{ $merchantSupply->stock_quantity }}" required>
        </div>

        <div class="mb-4">
            <label>Mesure (définie par l'administrateur)</label>
            <input type="text" id="measure" value="{{ $merchantSupply->measure ?? ($merchantSupply->supply->measure ?? '') }}" disabled class="form-control" />
            <small class="text-muted">Cette valeur est déterminée par l'administrateur et n'est pas modifiable par la mercerie.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Mode de vente</label>
            @php
                $mode = $merchantSupply->sale_mode ?? ($merchantSupply->supply->sale_mode ?? 'quantity');
            @endphp
            <div>
                @if($mode === 'measure')
                    <span class="badge bg-info text-dark">Par mesure</span>
                @else
                    <span class="badge bg-secondary">Par quantité</span>
                @endif
                <small class="text-muted ms-2">Défini par l'administrateur.</small>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('merchant.supplies.index') }}" class="btn btn-secondary-custom">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
            <button type="button" id="submit-btn" class="btn btn-primary-custom">
                <i class="fa-solid fa-save"></i> Mettre à jour
            </button>
        </div>
    </form>
</div>

<!-- === SCRIPT SWEETALERT2 === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('update-form');

    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Confirmer la mise à jour ?',
            text: "Les informations de cette fourniture seront modifiées.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4F0341',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, mettre à jour',
            cancelButtonText: 'Annuler',
            customClass: { popup: 'rounded-4 shadow-lg' }
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection

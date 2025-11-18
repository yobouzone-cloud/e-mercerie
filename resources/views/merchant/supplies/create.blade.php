@extends('layouts.app')

@section('content')
<!-- === TITRE PRINCIPAL === -->
<div class="page-title text-center py-4 py-md-5" style="background: #4F0341; color: #fff;">
  <div class="container">
    <h1 class="fw-bold m-0 display-6">Ajouter une Fourniture</h1>
    <p class="mt-2 mb-0 opacity-75">Gérez votre inventaire de fournitures</p>
  </div>
</div>

<!-- === CONTENU PRINCIPAL === -->
<div class="container-fluid px-3 px-md-4 px-lg-5 my-4 my-md-5">
  <div class="card-style shadow-sm rounded-4 mx-auto" style="max-width: 700px;">
    
    <!-- === ERREURS DE VALIDATION === -->
    @if ($errors->any())
      <div class="alert-errors mb-4">
        <div class="d-flex align-items-center mb-2">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>
          <strong>Veuillez corriger les erreurs suivantes :</strong>
        </div>
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- === FORMULAIRE === -->
    <form id="add-supply-form" action="{{ route('merchant.supplies.store') }}" method="POST">
      @csrf

      <div class="form-group mb-4">
        <label for="supply_id" class="form-label">
          <i class="fa-solid fa-box me-2"></i>Fourniture :
        </label>
        <select name="supply_id" id="supply_id" class="form-select" required>
          <option value="">— Sélectionner une fourniture —</option>
        </select>
        <div class="form-text">Recherchez et sélectionnez une fourniture dans la liste</div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group mb-4">
            <label for="price" class="form-label">
              <i class="fa-solid fa-tag me-2"></i>Prix (FCFA) :
            </label>
            <div class="input-group">
              <input type="number" id="price" name="price" step="0.01" min="0" 
                     placeholder="2500.00" class="form-control" required>
              <span class="input-group-text">FCFA</span>
            </div>
            <div class="form-text">Prix unitaire de vente</div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="form-group mb-4">
            <label for="stock_quantity" class="form-label">
              <i class="fa-solid fa-layer-group me-2"></i>Quantité en stock :
            </label>
            <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="0.01" 
                   placeholder="50.00" class="form-control" required>
            <div class="form-text">Quantité disponible en stock</div>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <!-- measure and sale_mode are admin-only; merchants cannot set them -->
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3 border-top">
        <a href="{{ route('merchant.supplies.index') }}" class="soft-btn secondary-btn flex-grow-1 flex-md-grow-0">
          <i class="fa-solid fa-arrow-left me-2"></i> Retour à la liste
        </a>
        <button type="button" id="submit-btn" class="soft-btn primary-btn flex-grow-1 flex-md-grow-0">
          <i class="fa-solid fa-plus me-2"></i> Ajouter la fourniture
        </button>
      </div>
    </form>
  </div>
</div>

<!-- === STYLE MODERNE === -->
<style>
:root {
  --primary-color: #4F0341;
  --primary-light: #7a1761;
  --secondary-color: #9333ea;
  --white: #fff;
  --gray-light: #f8f9fa;
  --gray-medium: #e9ecef;
  --gray-dark: #6b7280;
  --danger: #dc3545;
  --success: #198754;
  --radius: 16px;
  --radius-lg: 20px;
  --shadow: 0 8px 30px rgba(0,0,0,0.08);
  --shadow-hover: 0 15px 40px rgba(0,0,0,0.12);
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* === CONTENEUR PRINCIPAL === */
.card-style {
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  padding: 2.5rem;
  transition: var(--transition);
  border: 1px solid rgba(0,0,0,0.05);
}
.card-style:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-hover);
}

/* === TYPOGRAPHIE === */
.page-title h1 {
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.5px;
}

.page-title p {
  font-size: clamp(0.9rem, 2vw, 1.1rem);
}

/* === FORMULAIRES === */
.form-group {
  position: relative;
}

.form-label {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 8px;
  display: block;
  font-size: 0.95rem;
}

.form-control, .form-select {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  transition: var(--transition);
  background: var(--white);
  color: #2d3748;
}

.form-control:focus,
.form-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.1);
  outline: none;
  background: var(--white);
}

.form-control::placeholder {
  color: #a0aec0;
}

.input-group {
  border-radius: 12px;
  overflow: hidden;
}

.input-group .form-control {
  border-radius: 12px 0 0 12px;
  border-right: none;
}

.input-group-text {
  background: var(--gray-medium);
  border: 2px solid #e2e8f0;
  border-left: none;
  border-radius: 0 12px 12px 0;
  color: var(--gray-dark);
  font-weight: 500;
  padding: 0.875rem 1rem;
}

.form-text {
  font-size: 0.825rem;
  color: var(--gray-dark);
  margin-top: 6px;
  display: block;
}

/* === BOUTONS === */
.soft-btn {
  border: none;
  border-radius: 50px;
  font-weight: 600;
  padding: 0.875rem 2rem;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  text-decoration: none;
  text-align: center;
  white-space: nowrap;
  font-size: 0.95rem;
  position: relative;
  overflow: hidden;
}

.soft-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.soft-btn:hover::before {
  left: 100%;
}

.primary-btn {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
  color: var(--white);
  box-shadow: 0 4px 15px rgba(79, 3, 65, 0.3);
}

.primary-btn:hover {
  background: linear-gradient(135deg, var(--primary-light), var(--secondary-color));
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4);
  color: var(--white);
}

.secondary-btn {
  background: var(--gray-light);
  color: #4a5568;
  border: 2px solid var(--gray-medium);
  transition: var(--transition);
}

.secondary-btn:hover {
  background: var(--gray-medium);
  color: #2d3748;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* === ALERTES === */
.alert-errors {
  background: linear-gradient(135deg, #fff5f5, #fed7d7);
  border: 1px solid #feb2b2;
  color: #c53030;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  border-left: 4px solid var(--danger);
}

.alert-errors strong {
  color: #9b2c2c;
}

.alert-errors ul {
  margin-bottom: 0;
}

.alert-errors li {
  margin-bottom: 0.25rem;
}

.alert-errors li:last-child {
  margin-bottom: 0;
}

/* === ANIMATIONS === */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-style {
  animation: fadeInUp 0.6s ease-out;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
  .container-fluid {
    padding: 0 1rem;
  }
  
  .card-style {
    padding: 2rem 1.5rem;
    border-radius: 14px;
  }
  
  .card-style:hover {
    transform: translateY(-2px);
  }
  
  .soft-btn {
    width: 100%;
    padding: 1rem 1.5rem;
    font-size: 1rem;
  }
  
  .form-control, .form-select {
    padding: 0.75rem 0.875rem;
    font-size: 16px; /* Prevent zoom on iOS */
  }
  
  .page-title {
    padding: 2rem 1rem !important;
  }
}

@media (max-width: 576px) {
  .card-style {
    padding: 1.5rem 1.25rem;
    border-radius: 12px;
  }
  
  .page-title h1 {
    font-size: 1.5rem;
  }
  
  .page-title p {
    font-size: 0.9rem;
  }
  
  .form-control, .form-select {
    padding: 0.75rem;
    border-radius: 10px;
  }
  
  .soft-btn {
    padding: 0.875rem 1.25rem;
    font-size: 0.9rem;
  }
  
  .row.g-3 {
    gap: 1rem !important;
  }
}

@media (max-width: 400px) {
  .card-style {
    padding: 1.25rem 1rem;
    margin: 0 -0.5rem;
  }
  
  .container-fluid {
    padding: 0 0.5rem;
  }
  
  .soft-btn {
    font-size: 0.875rem;
    padding: 0.75rem 1rem;
  }
}

/* === SELECT2 CUSTOMIZATION === */
.select2-container--bootstrap-5 .select2-selection {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  min-height: 52px;
  transition: var(--transition);
}

.select2-container--bootstrap-5 .select2-selection:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.1);
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
  padding: 0;
  color: #2d3748;
  font-size: 1rem;
}

.select2-container--bootstrap-5 .select2-dropdown {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: var(--shadow);
}

/* === ACCESSIBILITY === */
@media (prefers-reduced-motion: reduce) {
  .card-style,
  .soft-btn,
  .form-control,
  .form-select {
    transition: none;
    animation: none;
  }
  
  .soft-btn::before {
    display: none;
  }
}

/* === FOCUS VISUEL AMÉLIORÉ === */
.soft-btn:focus-visible,
.form-control:focus-visible,
.form-select:focus-visible {
  outline: 2px solid var(--primary-color);
  outline-offset: 2px;
}
</style>

<!-- === SCRIPT SWEETALERT2 + SELECT2 === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const submitBtn = document.getElementById('submit-btn');
  const form = document.getElementById('add-supply-form');

  submitBtn.addEventListener('click', function(e) {
    e.preventDefault();
    
    // Validation basique des champs requis
    const supplyId = document.getElementById('supply_id').value;
    const price = document.getElementById('price').value;
    const quantity = document.getElementById('stock_quantity').value;
    
    if (!supplyId || !price || !quantity) {
      Swal.fire({
        title: 'Champs manquants',
        text: 'Veuillez remplir tous les champs obligatoires.',
        icon: 'warning',
        confirmButtonColor: '#4F0341',
        customClass: { popup: 'rounded-4 shadow-lg' }
      });
      return;
    }
    
    Swal.fire({
      title: 'Confirmer l\'ajout ?',
      html: `
        <div class="text-start">
          <p><strong>Cette fourniture sera ajoutée à votre stock :</strong></p>
          <div class="mt-3 p-3 rounded-3" style="background: #f8f9fa;">
            <div class="mb-2"><i class="fa-solid fa-box me-2"></i> Fourniture sélectionnée</div>
            <div class="mb-2"><i class="fa-solid fa-tag me-2"></i> Prix: ${parseFloat(price).toLocaleString('fr-FR')} FCFA</div>
            <div><i class="fa-solid fa-layer-group me-2"></i> Quantité: ${quantity}</div>
          </div>
        </div>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4F0341',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fa-solid fa-check me-2"></i>Confirmer',
      cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Annuler',
      customClass: { 
        popup: 'rounded-4 shadow-lg',
        confirmButton: 'btn primary-btn',
        cancelButton: 'btn secondary-btn'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        // Ajouter un indicateur de charnement
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Ajout en cours...';
        submitBtn.disabled = true;
        
        form.submit();
      }
    });
  });

  // Initialisation Select2
  if (typeof $.fn.select2 !== 'undefined') {
    $('#supply_id').select2({
      theme: 'bootstrap-5',
      placeholder: 'Rechercher une fourniture...',
      language: 'fr',
      width: '100%',
      minimumInputLength: 2,
      dropdownCssClass: 'shadow-lg border-0 rounded-4',
      containerCssClass: 'form-select',
      ajax: {
        url: "{{ route('merchant.supplies.search') }}",
        type: 'GET',
        dataType: 'json',
        delay: 300,
        data: params => ({ q: params.term }),
        processResults: data => ({ results: data.results })
      },
      templateResult: supply => supply.loading ? 
        '<div class="text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Recherche...</div>' : 
        $('<div class="d-flex align-items-center"><i class="fa-solid fa-box me-2 text-primary"></i><span>' + supply.text + '</span></div>'),
      escapeMarkup: markup => markup
    });
  }
});
</script>
@endsection
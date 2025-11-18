@extends('layouts.app')

@section('content')
<!-- === TITRE PRINCIPAL === -->
<div class="page-title">
  <h1>Mes Fournitures</h1>
</div>

<div class="container-fluid px-3 px-md-5">
  <div class="tables-wrapper">
    <div class="card-style mb-30 shadow-sm rounded-4">

      <!-- En-tête de la carte -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold text-dark m-0">Liste de mes fournitures</h4>

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-center">
          <input id="merchant-search" name="search" value="{{ old('search', $search ?? '') }}"
            type="search" placeholder="🔍 Rechercher une fourniture..."
            class="form-control form-control-sm rounded-pill shadow-sm border-0 w-auto flex-grow-1"
            style="min-width: 230px;" autocomplete="off" />
          <a href="{{ route('merchant.supplies.create') }}" class="soft-btn primary-btn">
            <i class="lni lni-plus"></i> Ajouter
          </a>
        </div>
      </div>

      <!-- Tableau responsive -->
      <div class="table-wrapper table-responsive">
        <table class="table align-middle text-center">
          <thead>
            <tr>
              <th><h6>Fourniture</h6></th>
              <th><h6>Prix (FCFA)</h6></th>
              <th><h6>Quantité</h6></th>
              <th><h6>Actions</h6></th>
            </tr>
          </thead>
          <tbody id="merchant-supplies-rows">
            @include('merchant.supplies._rows')
          </tbody>
        </table>
      </div>

      @include('merchant.supplies._pagination')

    </div>
  </div>
</div>

<!-- === STYLE PERSONNALISÉ === -->
<style>
:root {
  --primary-color: #4F0341;
  --secondary-color: #9333ea;
  --gradient: linear-gradient(135deg, #4F0341, #9333ea);
  --white: #fff;
  --gray: #6b7280;
  --radius: 18px;
  --shadow: 0 8px 18px rgba(0,0,0,0.08);
  --transition: all 0.3s ease;
}

/* CARTE */
.card-style {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 2rem;
}

/* TABLEAU */
.table-wrapper {
  overflow-x: auto;
}
.table thead {
  background: rgba(79, 3, 65, 0.08);
}
.table th h6 {
  color: var(--primary-color);
  font-weight: 600;
  margin: 0;
}
.table tbody tr {
  transition: var(--transition);
}
.table tbody tr:hover {
  background-color: #faf5ff;
}

/* BOUTONS */
.soft-btn {
  border: none;
  border-radius: 50px;
  font-weight: 600;
  padding: 0.7rem 1.8rem;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  text-align: center;
  white-space: nowrap;
}

.primary-btn {
  background: var(--primary-color);
  color: white;
  box-shadow: 0 4px 12px rgba(79, 3, 65, 0.2);
}
.primary-btn:hover {
  background: var(--secondary-color);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(147,51,234,0.3);
}

/* ICONES */
.edit-icone {
  color: var(--primary-color);
  font-size: 1.1rem;
  transition: var(--transition);
}
.edit-icone:hover {
  color: var(--secondary-color);
  transform: scale(1.1);
}
.btn-delete i {
  color: #e11d48;
  transition: var(--transition);
}
.btn-delete i:hover {
  transform: scale(1.1);
  color: #b91c1c;
}

/* RESPONSIVE DESIGN */
@media (max-width: 992px) {
  .d-flex.justify-content-between {
    flex-direction: column;
    align-items: stretch !important;
  }
  .d-flex.flex-wrap.gap-2 {
    justify-content: space-between;
  }
}

@media (max-width: 768px) {
  .card-style {
    padding: 1.2rem;
  }
  .soft-btn {
    padding: 0.6rem 1.3rem;
    width: 100%;
    justify-content: center;
  }
  .page-title {
    padding: 1.8rem 1rem;
  }
  .table th h6 {
    font-size: 0.9rem;
  }
  .table td {
    font-size: 0.9rem;
  }
}
</style>

<!-- === SWEETALERT2 === -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      Swal.fire({
        title: 'Supprimer cette fourniture ?',
        text: "Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4F0341',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        customClass: { popup: 'rounded-4 shadow-lg' }
      }).then((result) => {
        if (result.isConfirmed) form.submit();
      });
    });
  });
});
</script>

<!-- === RECHERCHE AJAX === -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('merchant-search');
  const rowsContainer = document.getElementById('merchant-supplies-rows');

  function debounce(fn, delay) {
    let t;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  async function fetchResults(url) {
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Erreur réseau');
      const data = await res.json();
      if (data.rows) rowsContainer.innerHTML = data.rows;
      if (data.pagination) {
        const pagWrap = document.createElement('div');
        pagWrap.innerHTML = data.pagination;
        const existing = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3');
        if (existing) existing.replaceWith(pagWrap.firstElementChild);
      }
      attachPaginationHandlers();
      attachDeleteHandlers();
    } catch (e) {
      console.error(e);
    }
  }

  const debouncedFetch = debounce(() => {
    const q = searchInput.value;
    const url = new URL(window.location.href);
    url.searchParams.set('search', q);
    url.searchParams.delete('page');
    fetchResults(url.toString());
  }, 350);

  searchInput.addEventListener('input', debouncedFetch);

  function attachPaginationHandlers() {
    document.querySelectorAll('.pagination a').forEach(a => {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        fetchResults(this.href);
      });
    });
  }

  function attachDeleteHandlers() {
    document.querySelectorAll('.btn-delete').forEach(button => {
      button.onclick = function (e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
          title: 'Supprimer cette fourniture ?',
          text: "Cette action est irréversible.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#4F0341',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Oui, supprimer',
          cancelButtonText: 'Annuler',
          customClass: { popup: 'rounded-4 shadow-lg' }
        }).then((result) => {
          if (result.isConfirmed) form.submit();
        });
      };
    });
  }

  attachPaginationHandlers();
  attachDeleteHandlers();
});
</script>
@endsection

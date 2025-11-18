@extends('layouts.app')

@section('content')
<style>
  /* Carte principale */
  .reset-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    max-width: 480px;
    margin: 0 auto;
    padding: 40px;
  }

  /* Labels et inputs */
  .form-label {
    font-weight: 600;
    color: #4F0341;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    border-color: #4F0341;
    box-shadow: 0 0 5px rgba(79, 3, 65, 0.3);
  }

  /* Bouton violet/bordeaux */
  .btn-primary {
    background-color: #4F0341;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    width: 100%;
  }

  .btn-primary:hover {
    background-color: #73185C;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(79, 3, 65, 0.3);
  }

  .text-danger {
    font-size: 0.9em;
    margin-top: 4px;
  }

  .alert {
    border-radius: 10px;
  }
</style>

<div class="page-title text-center py-4">
  <h1 class="fw-bold">Réinitialiser le mot de passe</h1>
</div>

<div class="container">
  <div class="reset-card">

    @if(session('status'))
      <div class="alert alert-success text-center">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="resetLinkForm">
      @csrf
      <div class="mb-3">
        <label for="email" class="form-label">Adresse e-mail</label>
        <input id="email" type="email" name="email" class="form-control" required autofocus>
        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary">Envoyer le lien de réinitialisation</button>
    </form>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('resetLinkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;

    Swal.fire({
      title: 'Confirmation',
      text: 'Souhaitez-vous recevoir le lien de réinitialisation du mot de passe ?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui, envoyer',
      cancelButtonText: 'Annuler',
      confirmButtonColor: '#4F0341',
      cancelButtonColor: '#888'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
</script>
@endsection

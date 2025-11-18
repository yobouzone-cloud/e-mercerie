@extends('layouts.app')

@section('content')
<style>
  /* Titre pleine largeur */
  .page-header {
    background-color: #4F0341;
    color: #fff;
    text-align: center;
    padding: 30px 0;
    margin-bottom: 40px;
    border-radius: 0;
  }

  /* Carte du formulaire */
  .reset-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    max-width: 480px;
    margin: 0 auto;
    padding: 40px;
  }

  /* Labels & inputs */
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

  /* Bouton principal */
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
</style>

<div class="page-header">
  <h1 class="fw-bold">Réinitialiser le mot de passe</h1>
</div>

<div class="container">
  <div class="reset-card">
    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="mb-3">
        <label for="email" class="form-label">Adresse e-mail</label>
        <input id="email" type="email" name="email" class="form-control" required autofocus value="{{ old('email') }}">
        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Nouveau mot de passe</label>
        <input id="password" type="password" name="password" class="form-control" required>
        @error('password')<div class="text-danger">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary">Réinitialiser</button>
    </form>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('resetForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;

    Swal.fire({
      title: 'Confirmation',
      text: 'Voulez-vous vraiment réinitialiser votre mot de passe ?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Oui, continuer',
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

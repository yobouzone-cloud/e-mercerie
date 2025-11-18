@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Vérifier votre adresse email</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <p>Un email de vérification a été envoyé à votre adresse. Cliquez sur le lien dans l'email pour vérifier votre compte.</p>

  <form method="POST" action="{{ route('verification.resend') }}">
    @csrf
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input id="email" type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-secondary">Renvoyer l'email de vérification</button>
  </form>
</div>
@endsection

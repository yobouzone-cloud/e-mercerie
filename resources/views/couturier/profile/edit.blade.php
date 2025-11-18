@extends('layouts.app')

@section('title', 'Mon profil - Couturier')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 text-center">
                    <h4 class="fw-bold mb-0">Mon profil couturier</h4>
                    <p class="text-muted small mb-0">Mettez à jour vos informations personnelles</p>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="avatar" class="form-label fw-semibold">Avatar</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url ?? asset('images/defaults/user.png') }}" alt="avatar" width="72" class="rounded-circle">
                                <input type="file" id="avatar" name="avatar" class="form-control-file">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="city_id" class="form-label fw-semibold">Ville</label>
                                <select id="city_id" name="city_id" class="form-select">
                                    <option value="">-- Choisir une ville (optionnel) --</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ (string)old('city_id', $user->city_id ?? '') === (string)$city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="quarter_id" class="form-label fw-semibold">Quartier</label>
                                <select id="quarter_id" name="quarter_id" class="form-select" {{ empty(old('city_id', $user->city_id ?? '')) ? 'disabled' : '' }}>
                                    <option value="">-- Choisir un quartier --</option>
                                    @foreach($quarters as $q)
                                        <option value="{{ $q->id }}" {{ (string)old('quarter_id', $user->quarter_id ?? '') === (string)$q->id ? 'selected' : '' }}>{{ $q->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nom complet</label>
                            <input id="name" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Numéro de téléphone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" placeholder="Ex: +229 90 00 00 00">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Adresse</label>
                            <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const citySelect = document.getElementById('city_id');
    const quarterSelect = document.getElementById('quarter_id');

    async function loadQuarters(cityId, preselectId) {
        quarterSelect.innerHTML = '<option>Chargement...</option>';
        quarterSelect.disabled = true;
        try {
            const res = await fetch(`/api/cities/${cityId}/quarters`);
            if (!res.ok) throw new Error('Échec récupération');
            const data = await res.json();
            quarterSelect.innerHTML = '<option value="">-- Choisir un quartier --</option>';
            data.forEach(q => {
                const opt = document.createElement('option');
                opt.value = q.id;
                opt.textContent = q.name;
                if (preselectId && (String(preselectId) === String(q.id))) opt.selected = true;
                quarterSelect.appendChild(opt);
            });
            quarterSelect.disabled = false;
        } catch (e) {
            quarterSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            quarterSelect.disabled = true;
            console.error(e);
        }
    }

    citySelect && citySelect.addEventListener('change', function () {
        const cityId = this.value;
        if (!cityId) {
            quarterSelect.innerHTML = '<option value="">-- Choisir un quartier --</option>';
            quarterSelect.disabled = true;
            return;
        }
        loadQuarters(cityId, null);
    });
});
</script>
@endpush

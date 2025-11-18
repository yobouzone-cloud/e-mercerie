@extends('layouts.app')

@section('title', 'Mon profil - Mercerie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 text-center">
                    <h4 class="fw-bold mb-0">Mon profil mercerie</h4>
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

                        {{-- Avatar --}}
                        <div class="mb-4 text-center">
                            <div class="position-relative d-inline-block">
                                <img id="avatarPreview" src="{{ $mercerie->avatar ? asset('storage/' . $mercerie->avatar) : asset('images/defaults/mercerie-avatar.png') }}"
                                     alt="Avatar"
                                     class="rounded-circle border shadow-sm"
                                     width="120"
                                     height="120"
                                     style="object-fit: cover;">

                                <label for="avatar" 
                                       class="position-absolute bottom-0 end-0"
                                       style="cursor:pointer;
                                       background-color: blue;
                                       color: white;
                                       border-radius: 50%;
                                       width: 35px;
                                       height: 35px;
                                       display: flex;
                                       align-items: center;
                                       justify-content: center;"
                                       title="Changer l'avatar">
                                    <i class="fa-solid fa-camera"></i>
                                </label>
                                <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*">
                            </div>
                            @error('avatar')
                                <p class="text-danger small mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ville et Quartier (select) --}}
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="city_id" class="form-label fw-semibold">Ville</label>
                                <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
                                    <option value="">Sélectionnez une ville...</option>
                                    @foreach(
                                        // Assumes a City model exists and contains seeded villes
                                        \App\Models\City::orderBy('name')->get() as $city
                                    )
                                        <option value="{{ $city->id }}" {{ (old('city_id', $mercerie->city_id ?? '') == $city->id) ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="quarter_id" class="form-label fw-semibold">Quartier</label>
                                <select id="quarter_id" name="quarter_id" class="form-select @error('quarter_id') is-invalid @enderror" required>
                                    <option value="">Sélectionnez d'abord une ville</option>
                                    {{-- Options will be loaded dynamically via JS. If there's an existing value, we'll load it on DOMContentLoaded --}}
                                </select>
                                @error('quarter_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Téléphone --}}
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Numéro de téléphone</label>
                            <input type="text" id="phone" name="phone" 
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $mercerie->phone ?? '') }}" 
                                   placeholder="Ex: +229 90 00 00 00" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Adresse complète (libre) --}}
                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Adresse complète</label>
                            <textarea id="address" name="address"
                                      class="form-control @error('address') is-invalid @enderror"
                                      rows="3" placeholder="Entrez votre adresse complète (rue, numero, etc.)" required>{{ old('address', $mercerie->address ?? '') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

{{-- Preview JS --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('avatar');
    const preview = document.getElementById('avatarPreview');

    input.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (ev) {
            preview.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const citySelect = document.getElementById('city_id');
        const quarterSelect = document.getElementById('quarter_id');
        const selectedQuarterId = '{{ old('quarter_id', $mercerie->quarter_id ?? '') }}';

        if (!citySelect || !quarterSelect) {
            console.warn('city_id or quarter_id select not found');
            return;
        }

        async function loadQuarters(cityId, preselectId = null) {
            quarterSelect.disabled = true;
            quarterSelect.innerHTML = '<option>Chargement...</option>';
            if (!cityId) {
                quarterSelect.disabled = false;
                quarterSelect.innerHTML = '<option value="">Sélectionnez d\'abord une ville</option>';
                return;
            }

            const encodedId = encodeURIComponent(cityId);
            const url = "{{ rtrim(url('/api/cities'), '/') }}" + '/' + encodedId + '/quarters';
            console.debug('Loading quarters from', url);

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network: ' + res.status);
                const data = await res.json();
                if (!Array.isArray(data) || data.length === 0) {
                    quarterSelect.innerHTML = '<option value="">Aucun quartier disponible</option>';
                    quarterSelect.disabled = false;
                    return;
                }

                quarterSelect.innerHTML = '<option value="">Sélectionnez un quartier...</option>';
                data.forEach(q => {
                    const opt = document.createElement('option');
                    opt.value = q.id;
                    opt.textContent = q.name;
                    if (preselectId && String(preselectId) === String(q.id)) opt.selected = true;
                    quarterSelect.appendChild(opt);
                });
                quarterSelect.disabled = false;
            } catch (e) {
                quarterSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                quarterSelect.disabled = false;
                console.error('Failed to load quarters', e);
            }
        }

        // attach change handler robustly
        citySelect.addEventListener('change', function () {
            const val = this.value;
            // clear previous selection
            quarterSelect.innerHTML = '';
            loadQuarters(val);
        });

        // Preload if editing existing mercerie
        if (citySelect.value) {
            loadQuarters(citySelect.value, selectedQuarterId);
        }
    });
    </script>
@endpush

@endsection

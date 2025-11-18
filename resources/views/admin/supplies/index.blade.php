@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestion des fournitures</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.supplies.create') }}" class="btn btn-primary">Nouvelle fourniture</a>
            <form action="{{ route('admin.push.send-test') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="title" value="Annonce admin">
                <input type="hidden" name="body" value="Message de test envoyé depuis l'admin">
                <button class="btn btn-outline-success">Envoyer notif test</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Unité</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($supplies as $supply)
            <tr>
                <td>{{ $supply->name }}</td>
                <td>{{ $supply->category }}</td>
                <td>{{ $supply->unit }}</td>
                <td style="width:100px"><img src="{{ $supply->image_url }}" alt="" style="max-width:80px;max-height:50px;object-fit:contain"></td>
                <td>
                    <a href="{{ route('admin.supplies.edit', $supply->id) }}" class="btn btn-sm btn-secondary">Modifier</a>
                    <form action="{{ route('admin.supplies.destroy', $supply->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Supprimer cette fourniture ?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $supplies->links() }}
</div>
@endsection

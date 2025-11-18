@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la fourniture</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    @include('admin.supplies._form', ['action' => route('admin.supplies.update', $supply->id), 'method' => 'PUT', 'supply' => $supply])
</div>
@endsection

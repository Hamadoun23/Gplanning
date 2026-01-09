@extends('layouts.app')

@section('title', 'Modifier client')

@section('content')
    <h2 style="margin-bottom: 2rem; color: #303030;">Modifier client</h2>
    
    <div class="card">
        <form action="{{ route('clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="nom_entreprise">Nom de l'entreprise *</label>
                <input type="text" id="nom_entreprise" name="nom_entreprise" value="{{ old('nom_entreprise', $client->nom_entreprise) }}" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Modifier</button>
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

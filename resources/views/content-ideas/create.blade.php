@extends('layouts.app')

@section('title', 'Nouvelle idée de contenu')

@section('content')
    <div class="page-header" data-gsap="fadeIn">
        <h2 style="color: #303030; margin-bottom: 0.5rem;">Nouvelle idée de contenu</h2>
        <p style="color: #666;">Cette idée sera disponible pour tous les clients</p>
    </div>
    
    <div class="card" data-gsap="fadeInUp">
        <form action="{{ route('content-ideas.store') }}" method="POST" id="content-idea-form">
            @csrf
            
            @if(request()->has('return_to'))
                <input type="hidden" name="return_to" value="{{ request()->input('return_to') }}">
            @endif
            
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="{{ old('titre') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="type">Type *</label>
                <select id="type" name="type" required>
                    <option value="">Sélectionner un type</option>
                    <option value="vidéo" {{ old('type') == 'vidéo' ? 'selected' : '' }}>Vidéo</option>
                    <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>Image</option>
                    <option value="texte" {{ old('type') == 'texte' ? 'selected' : '' }}>Texte</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Créer</button>
                <a href="{{ request()->has('return_to') ? request()->input('return_to') : route('content-ideas.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

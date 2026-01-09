@extends('layouts.app')

@section('title', 'Modifier idée de contenu')

@section('content')
    <div class="page-header" data-gsap="fadeIn">
        <h2 style="color: #303030; margin-bottom: 0.5rem;">Modifier idée de contenu</h2>
        <p style="color: #666;">Cette idée est partagée entre tous les clients</p>
    </div>
    
    <div class="card" data-gsap="fadeInUp">
        <form action="{{ route('content-ideas.update', $contentIdea) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="{{ old('titre', $contentIdea->titre) }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="type">Type *</label>
                <select id="type" name="type" required>
                    <option value="">Sélectionner un type</option>
                    <option value="vidéo" {{ old('type', $contentIdea->type) == 'vidéo' ? 'selected' : '' }}>Vidéo</option>
                    <option value="image" {{ old('type', $contentIdea->type) == 'image' ? 'selected' : '' }}>Image</option>
                    <option value="texte" {{ old('type', $contentIdea->type) == 'texte' ? 'selected' : '' }}>Texte</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Modifier</button>
                <a href="{{ route('content-ideas.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

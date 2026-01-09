@extends('layouts.app')

@section('title', $contentIdea->titre)

@section('content')
    <div class="page-header" data-gsap="fadeIn">
        <div>
            <h2 style="color: #303030; margin-bottom: 0.5rem;">{{ $contentIdea->titre }}</h2>
            <p style="color: #666;">Idée de contenu partagée</p>
        </div>
        <div>
            <a href="{{ route('content-ideas.edit', $contentIdea) }}" class="btn btn-secondary" style="margin-right: 0.5rem;">Modifier</a>
            <a href="{{ route('content-ideas.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    
    <div class="card" data-gsap="fadeInUp">
        <div class="form-group">
            <label>Type</label>
            <p><span class="badge badge-info">{{ $contentIdea->type }}</span></p>
        </div>
    </div>
@endsection

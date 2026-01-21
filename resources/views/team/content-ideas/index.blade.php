@extends('layouts.app')

@section('title', 'Idées de contenu')

@section('content')
    <div class="page-header" data-gsap="fadeIn">
        <div>
            <h2 style="color: #303030; margin-bottom: 0.5rem;">Idées de contenu</h2>
            <p style="color: #666;">Idées partagées entre tous les clients</p>
        </div>
    </div>
    
    @if($contentIdeas->count() > 0)
        <div class="card" data-gsap="fadeInUp">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contentIdeas as $idea)
                        <tr data-gsap="fadeInUp" data-gsap-delay="{{ $loop->index * 0.05 }}">
                            <td><strong>{{ $idea->titre }}</strong></td>
                            <td>
                                <span class="badge badge-info">{{ $idea->type }}</span>
                            </td>
                            <td>
                                <a href="{{ route('content-ideas.show', $idea) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Voir</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card" data-gsap="fadeInUp">
            <div class="empty-state">
                <p>Aucune idée de contenu</p>
            </div>
        </div>
    @endif
@endsection

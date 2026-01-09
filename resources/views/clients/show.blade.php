@extends('layouts.app')

@section('title', $client->nom_entreprise)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: #303030;">{{ $client->nom_entreprise }}</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('clients.dashboard', $client) }}" class="btn btn-primary">📊 Espace Client</a>
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-secondary">Modifier</a>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" data-gsap="fadeInUp">
            <div class="card-header">Règles de publication</div>
            <p style="font-size: 2rem; font-weight: 700; color: #FF6A3A; margin-bottom: 1rem;">{{ $client->publicationRules->count() }}</p>
            <a href="{{ route('clients.publication-rules.index', $client) }}" class="btn btn-primary">Gérer les règles</a>
        </div>
        
        <div class="card" data-gsap="fadeInUp" data-gsap-delay="0.1">
            <div class="card-header">Tournages</div>
            <p style="font-size: 2rem; font-weight: 700; color: #FF6A3A; margin-bottom: 1rem;">{{ $client->shootings->count() }}</p>
            <a href="{{ route('shootings.create', ['client_id' => $client->id]) }}" class="btn btn-primary">Nouveau tournage</a>
        </div>
        
        <div class="card" data-gsap="fadeInUp" data-gsap-delay="0.2">
            <div class="card-header">Publications</div>
            <p style="font-size: 2rem; font-weight: 700; color: #FF6A3A; margin-bottom: 1rem;">{{ $client->publications->count() }}</p>
            <a href="{{ route('publications.create', ['client_id' => $client->id]) }}" class="btn btn-primary">Nouvelle publication</a>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">Derniers tournages</div>
        @if($client->shootings->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Idées associées</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->shootings->sortByDesc('date')->take(5) as $shooting)
                        <tr>
                            <td>{{ $shooting->date->format('d/m/Y') }}</td>
                            <td>{{ $shooting->contentIdeas->count() }} idée(s)</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('shootings.show', $shooting) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Voir</a>
                                    <form action="{{ route('shootings.destroy', $shooting) }}?return_to_client=1" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce tournage ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>Aucun tournage pour ce client</p>
            </div>
        @endif
    </div>
    
    <div class="card">
        <div class="card-header">Dernières publications</div>
        @if($client->publications->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Idée de contenu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->publications->sortByDesc('date')->take(5) as $publication)
                        <tr>
                            <td>{{ $publication->date->format('d/m/Y') }}</td>
                            <td>{{ $publication->contentIdea->titre }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Voir</a>
                                    <form action="{{ route('publications.destroy', $publication) }}?return_to_client=1" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>Aucune publication pour ce client</p>
            </div>
        @endif
    </div>
@endsection

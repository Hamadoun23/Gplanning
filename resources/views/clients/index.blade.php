@extends('layouts.app')

@section('title', 'Clients')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: #303030;">Clients</h2>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">+ Nouveau client</a>
    </div>
    
    @if($clients->count() > 0)
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Nom de l'entreprise</th>
                        <th>Idées de contenu</th>
                        <th>Tournages</th>
                        <th>Publications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td><strong>{{ $client->nom_entreprise }}</strong></td>
                            <td>{{ $client->content_ideas_count }}</td>
                            <td>{{ $client->shootings_count }}</td>
                            <td>{{ $client->publications_count }}</td>
                            <td>
                                <a href="{{ route('clients.dashboard', $client) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; margin-right: 0.5rem;" title="Espace Client">📊 Dashboard</a>
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; margin-right: 0.5rem;">Voir</a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; margin-right: 0.5rem;">Modifier</a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <p>Aucun client enregistré</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Créer le premier client</a>
            </div>
        </div>
    @endif
@endsection

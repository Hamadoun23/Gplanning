@extends('layouts.app')

@section('title', 'Règles de publication - ' . $client->nom_entreprise)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="color: #303030; margin-bottom: 0.5rem;">Règles de publication</h2>
            <p style="color: #666;">Client : {{ $client->nom_entreprise }}</p>
            <p style="color: #999; font-size: 0.9rem; margin-top: 0.5rem;">Les jours non recommandés afficheront un avertissement lors de la création d'une publication</p>
        </div>
        <div>
            <a href="{{ route('clients.dashboard', $client) }}" class="btn btn-secondary" style="margin-right: 0.5rem;">Retour au client</a>
            @if(!$isTeamReadOnly && count($rules) < 7)
                <a href="{{ route('clients.publication-rules.create', $client) }}" class="btn btn-primary">+ Ajouter une règle</a>
            @endif
        </div>
    </div>
    
    @if($rules->count() > 0)
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Jour de la semaine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $rule)
                        <tr>
                            <td><strong>{{ ucfirst($rule->day_of_week) }}</strong></td>
                            <td>
                                @if(!$isTeamReadOnly)
                                    <form action="{{ route('clients.publication-rules.destroy', [$client, $rule]) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette règle ?')">Supprimer</button>
                                    </form>
                                @else
                                    <span style="color: #999;">Lecture seule</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <p>Aucune règle de publication pour ce client</p>
                @if(!$isTeamReadOnly)
                    <a href="{{ route('clients.publication-rules.create', $client) }}" class="btn btn-primary" style="margin-top: 1rem;">Créer la première règle</a>
                @endif
            </div>
        </div>
    @endif
@endsection

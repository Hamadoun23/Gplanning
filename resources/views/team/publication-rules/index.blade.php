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
        </div>
    </div>
    
    @if($rules->count() > 0)
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Jour de la semaine</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $rule)
                        <tr>
                            <td><strong>{{ ucfirst($rule->day_of_week) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <p>Aucune règle de publication pour ce client</p>
            </div>
        </div>
    @endif
@endsection

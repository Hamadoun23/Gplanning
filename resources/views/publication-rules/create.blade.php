@extends('layouts.app')

@section('title', 'Nouvelle règle de publication')

@section('content')
    <h2 style="margin-bottom: 2rem; color: #303030;">Nouvelle règle de publication</h2>
    <p style="color: #666; margin-bottom: 2rem;">Client : {{ $client->nom_entreprise }}</p>
    
    @if(empty($availableDays))
        <div class="alert alert-warning">
            Tous les jours de la semaine ont déjà une règle définie.
        </div>
        <a href="{{ route('clients.publication-rules.index', $client) }}" class="btn btn-secondary">Retour</a>
    @else
        <div class="card">
            <form action="{{ route('clients.publication-rules.store', $client) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="day_of_week">Jour non recommandé *</label>
                    <select id="day_of_week" name="day_of_week" required>
                        <option value="">Sélectionner un jour</option>
                        @foreach($availableDays as $day)
                            <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                    <p style="margin-top: 0.5rem; color: #666; font-size: 0.9rem;">Ce jour affichera un avertissement lors de la création d'une publication, mais ne bloquera pas la création.</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Créer</button>
                    <a href="{{ route('clients.publication-rules.index', $client) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    @endif
@endsection

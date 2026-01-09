@extends('layouts.app')

@section('title', 'Publication du ' . $publication->date->format('d/m/Y'))

@section('content')
    @php
        $date = \Carbon\Carbon::parse($publication->date);
        $days = ['Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'];
        $dayOfWeek = $days[$date->format('l')] ?? strtolower($date->format('l'));
        $hasWarning = $publication->client->isDayNotRecommended($dayOfWeek);
    @endphp
    
    @if($publication->isOverdue())
        <div class="alert alert-danger" data-alert>
            <span class="alert-icon">🚨</span>
            <div class="alert-content">
                <strong>En retard</strong>
                <div>Cette publication était prévue le {{ $publication->date->format('d/m/Y') }} et n'a pas encore été complétée.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($publication->isUpcoming())
        <div class="alert alert-warning" data-alert>
            <span class="alert-icon">⏰</span>
            <div class="alert-content">
                <strong>Approche</strong>
                <div>Cette publication est prévue dans {{ now()->diffInDays($publication->date, false) }} jour(s) ({{ $publication->date->format('d/m/Y') }}).</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($publication->isCompleted())
        <div class="alert alert-success" data-alert>
            <span class="alert-icon">✅</span>
            <div class="alert-content">
                <strong>Complétée</strong>
                <div>Cette publication a été marquée comme complétée.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($publication->status === 'cancelled')
        <div class="alert alert-danger" data-alert>
            <span class="alert-icon">❌</span>
            <div class="alert-content">
                <strong>Échec/Annulée</strong>
                <div>Cette publication a été marquée comme échec ou annulée.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @endif
    
    @if($hasWarning)
        <div class="alert alert-warning" data-alert>
            <span class="alert-icon">⚠️</span>
            <div class="alert-content">
                <strong>Avertissement</strong>
                <div>Ce jour ({{ ucfirst($dayOfWeek) }}) est non recommandé pour la publication pour ce client.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @endif
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="color: #303030; margin-bottom: 0.5rem;">Publication</h2>
            <p style="color: #666;">{{ $publication->date->format('d/m/Y') }} - {{ $publication->client->nom_entreprise }}</p>
            <p>
                @if($publication->status === 'completed')
                    <span class="badge badge-success">✅ Complétée</span>
                @elseif($publication->status === 'cancelled')
                    <span class="badge badge-danger">❌ Échec/Annulée</span>
                @else
                    <span class="badge badge-warning">⏳ En attente</span>
                @endif
            </p>
        </div>
        <div>
            <form action="{{ route('publications.toggle-status', $publication) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn {{ $publication->status === 'completed' ? 'btn-secondary' : 'btn-success' }}">
                    {{ $publication->status === 'completed' ? '⏳ Marquer en attente' : '✅ Marquer comme complétée' }}
                </button>
            </form>
            @if($publication->status !== 'cancelled')
                <form action="{{ route('publications.toggle-status', $publication) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir marquer cette publication comme échec/annulée ?')">
                        ❌ Marquer comme échec
                    </button>
                </form>
            @endif
            <a href="{{ route('publications.edit', $publication) }}" class="btn btn-secondary" style="margin-right: 0.5rem;">Modifier</a>
            <a href="{{ route('publications.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    
    <div class="card">
        <div class="form-group">
            <label>Client</label>
            <p><strong>{{ $publication->client->nom_entreprise }}</strong></p>
        </div>
        
        <div class="form-group">
            <label>Date</label>
            <p><strong>{{ $publication->date->format('d/m/Y') }}</strong></p>
        </div>
        
        @if($publication->description)
        <div class="form-group">
            <label>Description</label>
            <p>{{ $publication->description }}</p>
        </div>
        @endif
        
        <div class="form-group">
            <label>Idée de contenu</label>
            <p>
                <strong>{{ $publication->contentIdea->titre }}</strong>
                <span class="badge badge-info" style="margin-left: 0.5rem;">{{ $publication->contentIdea->type }}</span>
            </p>
        </div>
        
        <div class="form-group">
            <label>Tournage lié</label>
            <p>
                @if($publication->shooting)
                    <a href="{{ route('shootings.show', $publication->shooting) }}">
                        Tournage du {{ $publication->shooting->date->format('d/m/Y') }}
                    </a>
                @else
                    <span style="color: #999;">Aucun tournage lié</span>
                @endif
            </p>
        </div>
    </div>
    
    @if($publication->status === 'cancelled')
        <div class="card" style="margin-bottom: 1.5rem; background-color: #f8f9fa;">
            <div class="card-header">Reprogrammer cette publication</div>
            <p style="margin-bottom: 1rem; color: #666;">Cette publication a été marquée comme échec. Vous pouvez la reprogrammer avec une nouvelle date.</p>
            <form action="{{ route('publications.reschedule', $publication) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="new_date">Nouvelle date *</label>
                    <input type="date" id="new_date" name="new_date" value="{{ old('new_date', now()->addDays(7)->format('Y-m-d')) }}" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">📅 Reprogrammer</button>
                </div>
            </form>
        </div>
    @endif
@endsection

@extends('layouts.app')

@section('title', 'Tournage du ' . $shooting->date->format('d/m/Y'))

@section('content')
    @if($shooting->isOverdue())
        <div class="alert alert-danger" data-alert>
            <span class="alert-icon">🚨</span>
            <div class="alert-content">
                <strong>En retard</strong>
                <div>Ce tournage était prévu le {{ $shooting->date->format('d/m/Y') }} et n'a pas encore été complété.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($shooting->isUpcoming())
        <div class="alert alert-warning" data-alert>
            <span class="alert-icon">⏰</span>
            <div class="alert-content">
                <strong>Approche</strong>
                <div>Ce tournage est prévu dans {{ now()->diffInDays($shooting->date, false) }} jour(s) ({{ $shooting->date->format('d/m/Y') }}).</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($shooting->isCompleted())
        <div class="alert alert-success" data-alert>
            <span class="alert-icon">✅</span>
            <div class="alert-content">
                <strong>Complété</strong>
                <div>Ce tournage a été marqué comme complété.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @elseif($shooting->status === 'cancelled')
        <div class="alert alert-danger" data-alert>
            <span class="alert-icon">❌</span>
            <div class="alert-content">
                <strong>Échec/Annulé</strong>
                <div>Ce tournage a été marqué comme échec ou annulé.</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @endif
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="color: #303030; margin-bottom: 0.5rem;">Tournage</h2>
            <p style="color: #666;">{{ $shooting->date->format('d/m/Y') }} - {{ $shooting->client->nom_entreprise }}</p>
            <p>
                @if($shooting->status === 'completed')
                    <span class="badge badge-success">✅ Complété</span>
                @elseif($shooting->status === 'cancelled')
                    <span class="badge badge-danger">❌ Échec/Annulé</span>
                @else
                    <span class="badge badge-warning">⏳ En attente</span>
                @endif
            </p>
        </div>
        <div>
            <form action="{{ route('shootings.toggle-status', $shooting) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn {{ $shooting->status === 'completed' ? 'btn-secondary' : 'btn-success' }}">
                    {{ $shooting->status === 'completed' ? '⏳ Marquer en attente' : '✅ Marquer comme complété' }}
                </button>
            </form>
            @if($shooting->status !== 'cancelled')
                <form action="{{ route('shootings.toggle-status', $shooting) }}" method="POST" style="display: inline; margin-right: 0.5rem;">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir marquer ce tournage comme échec/annulé ?')">
                        ❌ Marquer comme échec
                    </button>
                </form>
            @endif
            <a href="{{ route('shootings.edit', $shooting) }}" class="btn btn-secondary" style="margin-right: 0.5rem;">Modifier</a>
            <a href="{{ route('shootings.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="form-group">
            <label>Client</label>
            <p><strong>{{ $shooting->client->nom_entreprise }}</strong></p>
        </div>
        
        <div class="form-group">
            <label>Date</label>
            <p><strong>{{ $shooting->date->format('d/m/Y') }}</strong></p>
        </div>
        
        @if($shooting->description)
        <div class="form-group">
            <label>Description</label>
            <p>{{ $shooting->description }}</p>
        </div>
        @endif
    </div>
    
    @if($shooting->status === 'cancelled')
        <div class="card" style="margin-bottom: 1.5rem; background-color: #f8f9fa;">
            <div class="card-header">Reprogrammer ce tournage</div>
            <p style="margin-bottom: 1rem; color: #666;">Ce tournage a été marqué comme échec. Vous pouvez le reprogrammer avec une nouvelle date.</p>
            <form action="{{ route('shootings.reschedule', $shooting) }}" method="POST">
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
    
    <div class="card">
        <div class="card-header">Idées de contenu associées</div>
        @if($shooting->contentIdeas->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shooting->contentIdeas as $idea)
                        <tr>
                            <td><strong>{{ $idea->titre }}</strong></td>
                            <td><span class="badge badge-info">{{ $idea->type }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>Aucune idée de contenu associée</p>
            </div>
        @endif
    </div>
@endsection

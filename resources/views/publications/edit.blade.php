@extends('layouts.app')

@section('title', 'Modifier publication')

@section('content')
    <h2 style="margin-bottom: 2rem; color: #303030;">Modifier publication</h2>
    
    @if(!empty($warnings))
        <div class="alert alert-warning" data-alert>
            <span class="alert-icon">⚠️</span>
            <div class="alert-content">
                <strong>Avertissements</strong>
                <ul>
                    @foreach($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.9;">Vous pouvez continuer malgré ces avertissements.</p>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
        </div>
    @endif
    
    <div class="card">
        <form action="{{ route('publications.update', $publication) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="client_id">Client *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $publication->client_id) == $c->id ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Date *</label>
                <input type="date" id="date" name="date" value="{{ old('date', $publication->date->format('Y-m-d')) }}" required data-realtime-check data-check-type="publication" data-exclude-id="{{ $publication->id }}">
                <div id="date-warnings" style="margin-top: 0.5rem;"></div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Description de la publication...">{{ old('description', $publication->description) }}</textarea>
            </div>
            
            <div class="form-group">
                <label for="content_idea_id">Idée de contenu *</label>
                <select id="content_idea_id" name="content_idea_id" required>
                    <option value="">Sélectionner une idée</option>
                    @foreach($contentIdeas as $idea)
                        <option value="{{ $idea->id }}" {{ old('content_idea_id', $publication->content_idea_id) == $idea->id ? 'selected' : '' }}>{{ $idea->titre }} ({{ $idea->type }})</option>
                    @endforeach
                </select>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                    <a href="{{ route('content-ideas.create') }}" target="_blank" style="color: #FF6A3A;">+ Créer une nouvelle idée de contenu</a>
                </p>
            </div>
            
            <div class="form-group">
                <label for="shooting_id">Tournage lié (optionnel)</label>
                <select id="shooting_id" name="shooting_id">
                    <option value="">Aucun tournage</option>
                    @foreach($shootings as $shooting)
                        <option value="{{ $shooting->id }}" {{ old('shooting_id', $publication->shooting_id) == $shooting->id ? 'selected' : '' }}>
                            {{ $shooting->date->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Modifier</button>
                <a href="{{ route('publications.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clientSelect = document.getElementById('client_id');
            const dateInput = document.getElementById('date');
            const warningsDiv = document.getElementById('date-warnings');
            const excludeId = dateInput.getAttribute('data-exclude-id');
            
            // Vérification en temps réel de la date
            async function checkDate() {
                const clientId = clientSelect.value;
                const date = dateInput.value;
                
                if (!date) {
                    warningsDiv.innerHTML = '';
                    return;
                }
                
                try {
                    const url = `/api/check-date?date=${date}&type=publication${clientId ? '&client_id=' + clientId : ''}`;
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    warningsDiv.innerHTML = '';
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        // Filtrer le conflit actuel (celui qu'on modifie)
                        const otherConflicts = data.conflicts.filter(c => c.id != excludeId);
                        
                        otherConflicts.forEach(conflict => {
                            const conflictDiv = document.createElement('div');
                            conflictDiv.className = conflict.isSameClient ? 'alert alert-warning' : 'alert alert-info';
                            conflictDiv.style.cssText = 'padding: 0.75rem; margin-top: 0.5rem;';
                            const icon = conflict.isSameClient ? '⚠️' : 'ℹ️';
                            const eventIcon = conflict.eventType === 'publication' ? '📢' : '📹';
                            conflictDiv.innerHTML = `
                                <span style="font-size: 1.2rem; margin-right: 0.5rem;">${icon}</span>
                                <span>${eventIcon} ${conflict.message}</span>
                                <a href="${conflict.url}" target="_blank" style="margin-left: 0.5rem; color: inherit; text-decoration: underline; font-weight: 600;">Voir</a>
                            `;
                            warningsDiv.appendChild(conflictDiv);
                        });
                    }
                    
                    if (data.warnings && data.warnings.length > 0) {
                        data.warnings.forEach(warning => {
                            const warningDiv = document.createElement('div');
                            warningDiv.className = 'alert alert-warning';
                            warningDiv.style.cssText = 'padding: 0.75rem; margin-top: 0.5rem;';
                            warningDiv.innerHTML = `
                                <span style="font-size: 1.2rem; margin-right: 0.5rem;">⚠️</span>
                                <span>${warning}</span>
                            `;
                            warningsDiv.appendChild(warningDiv);
                        });
                    } else if (!data.conflicts || data.conflicts.filter(c => c.id != excludeId).length === 0) {
                        const successDiv = document.createElement('div');
                        successDiv.style.cssText = 'padding: 0.5rem; margin-top: 0.5rem; color: #28a745; font-size: 0.9rem;';
                        successDiv.innerHTML = '✅ Aucun conflit détecté pour cette date';
                        warningsDiv.appendChild(successDiv);
                    }
                } catch (error) {
                    console.error('Date check error:', error);
                }
            }
            
            dateInput.addEventListener('change', checkDate);
            clientSelect.addEventListener('change', () => {
                setTimeout(checkDate, 500);
            });
        });
    </script>
@endsection

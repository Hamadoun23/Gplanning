@extends('layouts.app')

@section('title', 'Nouveau tournage')

@section('content')
    <h2 style="margin-bottom: 2rem; color: #303030;">Nouveau tournage</h2>
    
    <div class="card">
        <form action="{{ route('shootings.create') }}" method="GET" style="display: none;" id="client-form">
            <input type="hidden" name="client_id" id="hidden_client_id">
            <input type="hidden" name="date" value="{{ old('date', $selectedDate) }}">
        </form>
        
        <form action="{{ route('shootings.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="client_id">Client *</label>
                <select id="client_id" name="client_id" required onchange="document.getElementById('hidden_client_id').value=this.value; document.getElementById('client-form').submit();">
                    <option value="">Sélectionner un client</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $selectedClient) == $c->id ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Date *</label>
                <input type="date" id="date" name="date" value="{{ old('date', $selectedDate) }}" required data-realtime-check data-check-type="shooting">
                <div id="date-warnings" style="margin-top: 0.5rem;"></div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Description du tournage...">{{ old('description') }}</textarea>
            </div>
            
            <div class="form-group">
                <label>Idées de contenu *</label>
                @if($contentIdeas->count() > 0)
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 1rem; border-radius: 4px;">
                        @foreach($contentIdeas as $idea)
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: normal; padding: 0.5rem; border-radius: 4px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='transparent'">
                                <input type="checkbox" name="content_idea_ids[]" value="{{ $idea->id }}" {{ in_array($idea->id, old('content_idea_ids', [])) ? 'checked' : '' }}>
                                {{ $idea->titre }} <span class="badge badge-info">{{ $idea->type }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                        <a href="{{ route('content-ideas.create', ['return_to' => route('shootings.create', ['client_id' => $selectedClient, 'date' => $selectedDate])]) }}" target="_blank" style="color: #FF6A3A;">+ Créer une nouvelle idée de contenu</a>
                    </p>
                @else
                    <p style="color: #999;">Aucune idée de contenu disponible. <a href="{{ route('content-ideas.create', ['return_to' => route('shootings.create', ['client_id' => $selectedClient, 'date' => $selectedDate])]) }}" target="_blank" style="color: #FF6A3A;">Créer une idée</a></p>
                @endif
            </div>
            
            @if(request()->has('date'))
                <input type="hidden" name="return_to_calendar" value="1">
            @endif
            
            <div class="form-actions">
                <button type="submit" name="action" value="create" class="btn btn-primary">Créer</button>
                <button type="submit" name="action" value="create_and_publish" class="btn btn-primary" style="background: linear-gradient(135deg, #FF6A3A 0%, #ff8c5a 100%);">Créer et créer la publication</button>
                <a href="{{ route('shootings.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action*="shootings.store"]');
            const clientSelect = document.getElementById('client_id');
            const dateInput = document.getElementById('date');
            const warningsDiv = document.getElementById('date-warnings');
            
            // Vérification en temps réel de la date
            async function checkDate() {
                const clientId = clientSelect.value;
                const date = dateInput.value;
                
                if (!date) {
                    warningsDiv.innerHTML = '';
                    return;
                }
                
                try {
                    const url = `/api/check-date?date=${date}&type=shooting${clientId ? '&client_id=' + clientId : ''}`;
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    warningsDiv.innerHTML = '';
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        data.conflicts.forEach(conflict => {
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
                    } else if (!data.conflicts || data.conflicts.length === 0) {
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
            
            // Vérifier aussi quand la date change sans client sélectionné
            dateInput.addEventListener('change', function() {
                if (!clientSelect.value) {
                    checkDate();
                }
            });
        });
    </script>
@endsection

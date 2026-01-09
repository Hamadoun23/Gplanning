@extends('layouts.app')

@section('title', 'Comparaison des plannings')

@section('content')
    <div class="page-header" data-gsap="fadeIn">
        <h2 style="color: #303030; margin-bottom: 0.5rem;">Comparaison des plannings</h2>
        <p style="color: #666;">Comparez les plannings de plusieurs clients côte à côte</p>
    </div>
    
    <div class="card" style="margin-bottom: 2rem;" data-gsap="fadeInUp">
        <form method="GET" action="{{ route('planning-comparison.index') }}" id="comparison-form" data-no-draft="true">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="month">Mois</label>
                    <select id="month" name="month">
                        @php
                            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                        @endphp
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ $months[$i] }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Année</label>
                    <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2030">
                </div>
            </div>
            
            <div class="form-group">
                <label>Sélectionner les clients à comparer *</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; margin-top: 0.5rem;">
                    @foreach($clients as $client)
                        <label style="display: flex; align-items: center; padding: 0.75rem; border: 2px solid #ddd; border-radius: 4px; cursor: pointer; transition: all 0.2s; margin: 0;" 
                               onmouseover="this.style.borderColor='#FF6A3A'; this.style.backgroundColor='#fff5f2';" 
                               onmouseout="this.style.borderColor='#ddd'; this.style.backgroundColor='white';"
                               for="client-checkbox-{{ $client->id }}">
                            <input type="checkbox" 
                                   name="clients[]" 
                                   value="{{ $client->id }}" 
                                   {{ in_array((int)$client->id, array_map('intval', $selectedClientIds), true) ? 'checked' : '' }}
                                   id="client-checkbox-{{ $client->id }}"
                                   style="margin-right: 0.5rem; width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; pointer-events: auto;">
                            <span style="flex: 1; user-select: none; pointer-events: none;">{{ $client->nom_entreprise }}</span>
                        </label>
                    @endforeach
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Sélectionnez au moins 2 clients pour comparer</p>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Comparer</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Retour au dashboard</a>
            </div>
        </form>
    </div>
    
    @if(!empty($selectedClientIds) && count($selectedClientIds) >= 2)
        <div class="card" style="overflow-x: auto;" data-gsap="fadeInUp">
            <h3 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; color: #303030;">
                @php
                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                @endphp
                {{ $months[$month] }} {{ $year }}
            </h3>
            
            <table style="width: 100%; border-collapse: collapse; min-width: {{ 200 + (count($selectedClientIds) * 300) }}px;">
                <thead>
                    <tr>
                        <th style="padding: 0.75rem; background-color: #303030; color: white; border: 1px solid #ddd; text-align: center; width: 200px; position: sticky; left: 0; z-index: 10;">Date</th>
                        @foreach($selectedClientIds as $clientId)
                            @php
                                $client = $clients->find($clientId);
                            @endphp
                            <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; min-width: 300px;">
                                {{ $client ? $client->nom_entreprise : 'Client #' . $clientId }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $week)
                        @foreach($week as $day)
                            @php
                                $isWeekend = in_array($day['date']->format('l'), ['Saturday', 'Sunday']);
                            @endphp
                            <tr>
                                <td style="padding: 0.75rem; border: 1px solid #ddd; background-color: {{ $isWeekend ? '#e9e9e9' : 'white' }}; position: sticky; left: 0; z-index: 5; font-weight: 600; {{ $isWeekend ? 'opacity: 0.7;' : '' }}">
                                    {{ $day['date']->format('d/m') }}<br>
                                    <span style="font-size: 0.85rem; font-weight: normal; color: #666;">
                                        @php
                                            $days = ['Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mer', 'Thursday' => 'Jeu', 'Friday' => 'Ven', 'Saturday' => 'Sam', 'Sunday' => 'Dim'];
                                        @endphp
                                        {{ $days[$day['date']->format('l')] ?? '' }}
                                    </span>
                                </td>
                                @foreach($selectedClientIds as $clientId)
                                    <td style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; min-height: 150px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? '#fff' : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}">
                                        @php
                                            $dayData = $day['data'][$clientId] ?? ['shootings' => collect(), 'publications' => collect()];
                                        @endphp
                                        
                                        @if($dayData['shootings']->count() > 0 || $dayData['publications']->count() > 0)
                                            <div style="max-height: 140px; overflow-y: auto;">
                                                @foreach($dayData['shootings'] as $shooting)
                                                    @php
                                                        $bgColor = '#FF6A3A';
                                                        $icon = '📹';
                                                        if ($shooting->status === 'cancelled') {
                                                            $bgColor = '#6c757d';
                                                            $icon = '❌';
                                                        } elseif ($shooting->isCompleted()) {
                                                            $bgColor = '#28a745';
                                                            $icon = '✅';
                                                        } elseif ($shooting->isOverdue()) {
                                                            $bgColor = '#dc3545';
                                                            $icon = '🚨';
                                                        } elseif ($shooting->isUpcoming()) {
                                                            $bgColor = '#ffc107';
                                                            $icon = '⏰';
                                                        }
                                                    @endphp
                                                    <div style="background-color: {{ $bgColor }}; color: white; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.75rem; cursor: pointer; border-left: 3px solid {{ $bgColor }};" 
                                                         onclick="window.location.href='{{ route('shootings.show', $shooting) }}'"
                                                         title="Tournage - {{ $shooting->client->nom_entreprise }}">
                                                        <span>{{ $icon }}</span> Tournage
                                                    </div>
                                                @endforeach
                                                
                                                @foreach($dayData['publications'] as $publication)
                                                    @php
                                                        $bgColor = '#28a745';
                                                        $icon = '📢';
                                                        if ($publication->status === 'cancelled') {
                                                            $bgColor = '#6c757d';
                                                            $icon = '❌';
                                                        } elseif ($publication->isCompleted()) {
                                                            $bgColor = '#28a745';
                                                            $icon = '✅';
                                                        } elseif ($publication->isOverdue()) {
                                                            $bgColor = '#dc3545';
                                                            $icon = '🚨';
                                                        } elseif ($publication->isUpcoming()) {
                                                            $bgColor = '#ffc107';
                                                            $icon = '⏰';
                                                        }
                                                    @endphp
                                                    <div style="background-color: {{ $bgColor }}; color: white; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.75rem; cursor: pointer; border-left: 3px solid {{ $bgColor }};" 
                                                         onclick="window.location.href='{{ route('publications.show', $publication) }}'"
                                                         title="Publication - {{ $publication->contentIdea->titre }}">
                                                        <span>{{ $icon }}</span> {{ mb_substr($publication->contentIdea->titre, 0, 20) }}{{ mb_strlen($publication->contentIdea->titre) > 20 ? '...' : '' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div style="color: #999; font-size: 0.85rem; text-align: center; padding: 0.5rem;">
                                                -
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif(!empty($selectedClientIds) && count($selectedClientIds) < 2)
        <div class="card" data-gsap="fadeInUp">
            <div class="alert alert-warning">
                <span class="alert-icon">⚠️</span>
                <div class="alert-content">
                    <strong>Attention</strong>
                    <div>Veuillez sélectionner au moins 2 clients pour comparer leurs plannings.</div>
                </div>
            </div>
        </div>
    @endif
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('comparison-form');
            const checkboxes = form.querySelectorAll('input[type="checkbox"][name="clients[]"]');
            
            // S'assurer que toutes les checkboxes fonctionnent
            checkboxes.forEach(checkbox => {
                // Forcer l'activation de la checkbox
                checkbox.disabled = false;
                checkbox.readOnly = false;
                
                // Vérifier que la checkbox est bien accessible
                checkbox.addEventListener('change', function(e) {
                    console.log('Checkbox changée:', this.value, this.checked, this.id);
                });
                
                // Vérifier que le clic fonctionne
                checkbox.addEventListener('click', function(e) {
                    console.log('Checkbox cliquée:', this.value, this.id);
                    e.stopPropagation();
                });
            });
            
            // Nettoyer le localStorage pour ce formulaire si nécessaire
            const formId = form.id || 'comparison-form';
            const draftKey = `draft-${formId}`;
            const draft = localStorage.getItem(draftKey);
            if (draft) {
                // Ne pas restaurer automatiquement, laisser l'utilisateur choisir
                console.log('Brouillon trouvé mais ignoré pour ce formulaire');
            }
            
            // Validation : au moins 2 clients sélectionnés
            form.addEventListener('submit', function(e) {
                const checked = Array.from(checkboxes).filter(cb => cb.checked);
                console.log('Clients sélectionnés:', checked.map(cb => ({value: cb.value, id: cb.id, name: cb.closest('label').querySelector('span').textContent})));
                if (checked.length < 2) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins 2 clients pour comparer.');
                    return false;
                }
            });
            
            // Chargement progressif lors du changement
            const monthSelect = document.getElementById('month');
            const yearInput = document.getElementById('year');
            
            [monthSelect, yearInput].forEach(element => {
                element.addEventListener('change', function() {
                    if (window.gplanningUX && typeof window.gplanningUX.showLoading === 'function') {
                        const loader = window.gplanningUX.showLoading();
                        setTimeout(() => {
                            if (window.gplanningUX.hideLoading) {
                                window.gplanningUX.hideLoading(loader);
                            }
                        }, 500);
                    }
                });
            });
        });
    </script>
@endsection

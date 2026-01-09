@extends('layouts.app')

@section('title', 'Planning de publication')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: #303030;">Planning de publication</h2>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            @php
                $prevMonth = $startDate->copy()->subMonth();
                $nextMonth = $startDate->copy()->addMonth();
            @endphp
            <a href="{{ route('publications.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-secondary">← Mois précédent</a>
            <a href="{{ route('publications.create') }}" class="btn btn-primary">+ Nouvelle publication</a>
            <a href="{{ route('publications.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-secondary">Mois suivant →</a>
            <a href="{{ route('publications.export-calendar', ['month' => $month, 'year' => $year]) }}" class="btn btn-primary" style="white-space: nowrap;">
                📊 Exporter en Excel
            </a>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('publications.index') }}" id="calendar-filter-form" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="flex: 1;">
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
            <div class="form-group" style="flex: 1;">
                <label for="year">Année</label>
                <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2030">
            </div>
            <div class="form-group" style="flex: 2;">
                <label for="client_filter">Filtrer par client</label>
                <select id="client_filter" data-filter="client">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Aller</button>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtrage instantané par client
            const clientFilter = document.getElementById('client_filter');
            if (clientFilter) {
                clientFilter.addEventListener('change', function() {
                    const clientId = this.value;
                    document.querySelectorAll('[data-client-id]').forEach(cell => {
                        if (!clientId || cell.getAttribute('data-client-id') === clientId) {
                            cell.style.display = '';
                        } else {
                            cell.style.display = 'none';
                        }
                    });
                });
            }
            
            // Chargement progressif lors du changement de mois
            const filterForm = document.getElementById('calendar-filter-form');
            const monthSelect = document.getElementById('month');
            const yearInput = document.getElementById('year');
            
            let loadingTimeout;
            [monthSelect, yearInput].forEach(element => {
                element.addEventListener('change', function() {
                    if (window.gplanningUX) {
                        clearTimeout(loadingTimeout);
                        loadingTimeout = setTimeout(() => {
                            const loader = window.gplanningUX.showLoading();
                            filterForm.submit();
                        }, 300);
                    }
                });
            });
        });
    </script>
    
    <div class="card" style="overflow-x: auto;">
        <h3 style="text-align: center; margin-bottom: 1rem; font-size: 1.5rem;">
            @php
                $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            @endphp
            {{ $months[$month] }} {{ $year }}
        </h3>
        
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr>
                    <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%;">Lundi</th>
                    <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%;">Mardi</th>
                    <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%;">Mercredi</th>
                    <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%;">Jeudi</th>
                    <th style="padding: 0.75rem; background-color: #FF6A3A; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%;">Vendredi</th>
                    <th style="padding: 0.75rem; background-color: #9e9e9e; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%; opacity: 0.7;">Samedi</th>
                    <th style="padding: 0.75rem; background-color: #9e9e9e; color: white; border: 1px solid #ddd; text-align: center; width: 14.28%; opacity: 0.7;">Dimanche</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calendar as $week)
                    <tr>
                        @foreach($week as $day)
                            @php
                                $days = ['Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'];
                                $dayOfWeek = $days[$day['date']->format('l')] ?? strtolower($day['date']->format('l'));
                                $isWeekend = in_array($day['date']->format('l'), ['Saturday', 'Sunday']);
                                
                                // Vérifier si le jour a des avertissements
                                $hasDayWarning = false;
                                foreach ($day['publications'] as $pub) {
                                    if ($pub->client->isDayNotRecommended($dayOfWeek)) {
                                        $hasDayWarning = true;
                                        break;
                                    }
                                }
                            @endphp
                            <td style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; height: 120px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? ($hasDayWarning ? '#fff3cd' : '#fff') : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}">
                                <div style="font-weight: bold; margin-bottom: 0.5rem; color: {{ $day['isCurrentMonth'] ? '#303030' : '#999' }}; display: flex; align-items: center; gap: 0.5rem;">
                                    <span>{{ $day['date']->day }}</span>
                                    @if($hasDayWarning)
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">⚠️</span>
                                    @endif
                                </div>
                                <div style="max-height: 80px; overflow-y: auto;">
                                    @foreach($day['publications'] as $publication)
                                        @php
                                            $pubDayOfWeek = $days[\Carbon\Carbon::parse($publication->date)->format('l')] ?? '';
                                            $hasWarning = $publication->client->isDayNotRecommended($pubDayOfWeek);
                                            
                                            $bgColor = '#28a745';
                                            $borderColor = '#1e7e34';
                                            $icon = '📢';
                                            $textColor = 'white';
                                            
                                            if ($publication->status === 'cancelled') {
                                                $bgColor = '#6c757d';
                                                $borderColor = '#5a6268';
                                                $icon = '❌';
                                            } elseif ($publication->isCompleted()) {
                                                $bgColor = '#28a745';
                                                $borderColor = '#1e7e34';
                                                $icon = '✅';
                                            } elseif ($publication->isOverdue()) {
                                                $bgColor = '#dc3545';
                                                $borderColor = '#c82333';
                                                $icon = '🚨';
                                            } elseif ($publication->isUpcoming()) {
                                                $bgColor = '#ffc107';
                                                $borderColor = '#ff9800';
                                                $icon = '⏰';
                                                $textColor = '#000';
                                            } elseif ($hasWarning) {
                                                $bgColor = '#ffc107';
                                                $borderColor = '#ff9800';
                                                $textColor = '#000';
                                            }
                                        @endphp
                                        <div style="background-color: {{ $bgColor }}; color: {{ $textColor }}; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.75rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                             onclick="window.location.href='{{ route('publications.show', $publication) }}'"
                                             title="{{ $publication->client->nom_entreprise }} - {{ $publication->contentIdea->titre }} - {{ $publication->status === 'completed' ? 'Complétée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'Approche' : 'En attente')) }}">
                                            <strong>{{ $icon }} {{ $publication->client->nom_entreprise }}</strong>
                                            <br><small>{{ mb_strlen($publication->contentIdea->titre) > 20 ? mb_substr($publication->contentIdea->titre, 0, 20) . '...' : $publication->contentIdea->titre }}</small>
                                            @if($hasWarning && !$publication->isUpcoming())
                                                <br><small style="font-weight: bold;">⚠️ Jour non recommandé</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1rem; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
        <h4 style="margin-bottom: 0.5rem;">Légende :</h4>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background-color: #28a745; border-radius: 3px; border-left: 3px solid #1e7e34;"></div>
                <span>📢 Publication normale</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background-color: #ffc107; border-radius: 3px; border-left: 3px solid #ff9800;"></div>
                <span>⏰ Approche (dans 3 jours)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background-color: #dc3545; border-radius: 3px; border-left: 3px solid #c82333;"></div>
                <span>🚨 En retard</span>
            </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background-color: #28a745; border-radius: 3px; border-left: 3px solid #1e7e34;"></div>
                    <span>✅ Complétée</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 20px; height: 20px; background-color: #6c757d; border-radius: 3px; border-left: 3px solid #5a6268;"></div>
                    <span>❌ Échec/Annulée</span>
                </div>
        </div>
    </div>
@endsection

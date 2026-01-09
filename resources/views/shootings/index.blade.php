@extends('layouts.app')

@section('title', 'Planning de tournage')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: #303030;">Planning de tournage</h2>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            @php
                $prevMonth = $startDate->copy()->subMonth();
                $nextMonth = $startDate->copy()->addMonth();
            @endphp
            <a href="{{ route('shootings.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-secondary">← Mois précédent</a>
            <a href="{{ route('shootings.create') }}" class="btn btn-primary">+ Nouveau tournage</a>
            <a href="{{ route('shootings.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-secondary">Mois suivant →</a>
            <a href="{{ route('shootings.export-calendar', ['month' => $month, 'year' => $year]) }}" class="btn btn-primary" style="white-space: nowrap;">
                📊 Exporter en Excel
            </a>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('shootings.index') }}" style="display: flex; gap: 1rem; align-items: end;">
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
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Aller</button>
            </div>
        </form>
    </div>
    
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
                                $isWeekend = in_array($day['date']->format('l'), ['Saturday', 'Sunday']);
                            @endphp
                            <td style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; height: 120px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? '#fff' : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}">
                                <div style="font-weight: bold; margin-bottom: 0.5rem; color: {{ $day['isCurrentMonth'] ? '#303030' : '#999' }};">
                                    {{ $day['date']->day }}
                                </div>
                                <div style="max-height: 80px; overflow-y: auto;">
                                    @foreach($day['shootings'] as $shooting)
                                        @php
                                            $bgColor = '#FF6A3A';
                                            $borderColor = '#e55a2a';
                                            $icon = '📹';
                                            if ($shooting->status === 'cancelled') {
                                                $bgColor = '#6c757d';
                                                $borderColor = '#5a6268';
                                                $icon = '❌';
                                            } elseif ($shooting->isCompleted()) {
                                                $bgColor = '#28a745';
                                                $borderColor = '#1e7e34';
                                                $icon = '✅';
                                            } elseif ($shooting->isOverdue()) {
                                                $bgColor = '#dc3545';
                                                $borderColor = '#c82333';
                                                $icon = '🚨';
                                            } elseif ($shooting->isUpcoming()) {
                                                $bgColor = '#ffc107';
                                                $borderColor = '#ff9800';
                                                $icon = '⏰';
                                            }
                                        @endphp
                                        <div style="background-color: {{ $bgColor }}; color: white; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.75rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                             onclick="window.location.href='{{ route('shootings.show', $shooting) }}'"
                                             title="{{ $shooting->client->nom_entreprise }} - {{ $shooting->status === 'completed' ? 'Complété' : ($shooting->isOverdue() ? 'En retard' : ($shooting->isUpcoming() ? 'Approche' : 'En attente')) }}">
                                            <strong>{{ $icon }} {{ $shooting->client->nom_entreprise }}</strong>
                                            @if($shooting->contentIdeas->count() > 0)
                                                <br><small>{{ $shooting->contentIdeas->count() }} idée(s)</small>
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
@endsection

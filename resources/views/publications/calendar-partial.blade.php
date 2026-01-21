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
                    <td style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; height: 120px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? ($hasDayWarning ? '#fff3cd' : '#fff') : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}" data-client-id="{{ $day['publications']->pluck('client_id')->unique()->implode(',') }}">
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
                                    
                                    if ($publication->status === 'not_realized') {
                                        $bgColor = '#6c757d';
                                        $borderColor = '#5a6268';
                                        $icon = '❌';
                                    } elseif ($publication->status === 'cancelled') {
                                        $bgColor = '#6c757d';
                                        $borderColor = '#5a6268';
                                        $icon = '🚫';
                                    } elseif ($publication->status === 'rescheduled') {
                                        $bgColor = '#17a2b8';
                                        $borderColor = '#138496';
                                        $icon = '📅';
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

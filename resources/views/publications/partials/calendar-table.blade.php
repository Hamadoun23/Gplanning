<table class="calendar-table">
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
                    @endphp
                    <td class="calendar-day-cell" 
                        data-date="{{ $day['date']->format('Y-m-d') }}"
                        data-current-month="{{ $day['isCurrentMonth'] ? '1' : '0' }}"
                        data-client-id="{{ $day['publications']->isNotEmpty() ? $day['publications']->pluck('client_id')->unique()->implode(',') : '' }}"
                        style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; height: 150px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? (isset($day['hasWarnings']) && $day['hasWarnings'] ? '#fff3cd' : '#fff') : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}; cursor: pointer; position: relative;">
                        <div style="font-weight: bold; margin-bottom: 0.5rem; color: {{ $day['isCurrentMonth'] ? '#303030' : '#999' }}; display: flex; align-items: center; gap: 0.5rem;">
                            <span>{{ $day['date']->day }}</span>
                            @if(isset($day['hasWarnings']) && $day['hasWarnings'])
                                <span class="badge badge-warning" style="font-size: 0.7rem;">⚠️</span>
                            @endif
                            @if(isset($isTeamReadOnly) && !$isTeamReadOnly)
                                <a href="{{ route('publications.create', ['date' => $day['date']->format('Y-m-d')]) }}" class="calendar-day-add-btn" onclick="event.stopPropagation();" title="Ajouter une publication" style="margin-left: auto; background: #FF6A3A; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s; text-decoration: none;">+</a>
                            @endif
                        </div>
                        <div class="calendar-day-events" style="max-height: 110px; overflow-y: auto;" onclick="event.stopPropagation();">
                            <!-- Publications uniquement -->
                            @foreach($day['publications'] ?? [] as $publication)
                                @php
                                    $pubDayOfWeek = $days[\Carbon\Carbon::parse($publication->date)->format('l')] ?? '';
                                    $hasWarning = false;
                                    if ($publication->client) {
                                        $hasWarning = $publication->client->isDayNotRecommended($pubDayOfWeek);
                                    }
                                    
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
                                <div class="calendar-event" 
                                     data-event-type="publication"
                                     data-event-id="{{ $publication->id }}"
                                     data-client-id="{{ $publication->client_id }}"
                                     style="background-color: {{ $bgColor }}; color: {{ $textColor }}; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.7rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                     onclick="event.stopPropagation(); window.location.href='{{ route('publications.show', $publication) }}'"
                                     title="Publication - {{ $publication->client ? $publication->client->nom_entreprise : 'N/A' }} - {{ $publication->date->format('d/m/Y H:i') }} - {{ $publication->contentIdea ? $publication->contentIdea->titre : 'N/A' }} - {{ $publication->status === 'completed' ? 'Complétée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'Approche' : 'En attente')) }}">
                                    <strong>{{ $icon }} {{ $publication->client ? $publication->client->nom_entreprise : 'N/A' }}</strong>
                                    @if($publication->contentIdea)
                                    <br><small>{{ mb_strlen($publication->contentIdea->titre) > 15 ? mb_substr($publication->contentIdea->titre, 0, 15) . '...' : $publication->contentIdea->titre }}</small>
                                    @endif
                                    @if($hasWarning && !$publication->isUpcoming())
                                        <br><small style="font-weight: bold;">⚠️</small>
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

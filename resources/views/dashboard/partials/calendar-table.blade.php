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
                        style="padding: 0.5rem; border: 1px solid #ddd; vertical-align: top; height: 150px; background-color: {{ $isWeekend ? '#e9e9e9' : ($day['isCurrentMonth'] ? ($day['hasWarnings'] ? '#fff3cd' : '#fff') : '#f5f5f5') }}; {{ $isWeekend ? 'opacity: 0.7;' : '' }}; cursor: pointer; position: relative;">
                        <div style="font-weight: bold; margin-bottom: 0.5rem; color: {{ $day['isCurrentMonth'] ? '#303030' : '#999' }}; display: flex; align-items: center; gap: 0.5rem;">
                            <span>{{ $day['date']->day }}</span>
                            @if($day['hasWarnings'])
                                <span class="badge badge-warning" style="font-size: 0.7rem;">⚠️</span>
                            @endif
                            <button class="calendar-day-add-btn" onclick="event.stopPropagation(); openDateModal('{{ $day['date']->format('Y-m-d') }}');" title="Gérer les événements" style="margin-left: auto; background: #FF6A3A; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s;">+</button>
                        </div>
                        <div class="calendar-day-events" style="max-height: 110px; overflow-y: auto;" onclick="event.stopPropagation();">
                            <!-- Tournages -->
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
                                <div class="calendar-event" 
                                     data-event-type="shooting"
                                     data-event-id="{{ $shooting->id }}"
                                     style="background-color: {{ $bgColor }}; color: white; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.7rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                     onclick="event.stopPropagation(); window.location.href='{{ route('shootings.show', $shooting) }}'"
                                     title="Tournage - {{ $shooting->client->nom_entreprise }} - {{ $shooting->status === 'completed' ? 'Complété' : ($shooting->isOverdue() ? 'En retard' : ($shooting->isUpcoming() ? 'Approche' : 'En attente')) }}">
                                    <strong>{{ $icon }} {{ $shooting->client->nom_entreprise }}</strong>
                                    @if($shooting->contentIdeas->count() > 0)
                                        <br><small>{{ $shooting->contentIdeas->count() }} idée(s)</small>
                                    @endif
                                </div>
                            @endforeach
                            
                            <!-- Publications -->
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
                                <div class="calendar-event" 
                                     data-event-type="publication"
                                     data-event-id="{{ $publication->id }}"
                                     style="background-color: {{ $bgColor }}; color: {{ $textColor }}; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.7rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                     onclick="event.stopPropagation(); window.location.href='{{ route('publications.show', $publication) }}'"
                                     title="Publication - {{ $publication->client->nom_entreprise }} - {{ $publication->contentIdea->titre }} - {{ $publication->status === 'completed' ? 'Complétée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'Approche' : 'En attente')) }}">
                                    <strong>{{ $icon }} {{ $publication->client->nom_entreprise }}</strong>
                                    <br><small>{{ mb_strlen($publication->contentIdea->titre) > 15 ? mb_substr($publication->contentIdea->titre, 0, 15) . '...' : $publication->contentIdea->titre }}</small>
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

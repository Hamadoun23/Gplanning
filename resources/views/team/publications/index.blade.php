@extends('layouts.app')

@section('title', 'Planning de publication')

@section('content')
    @php
        $isTeamReadOnly = true; // Toujours en lecture seule pour les team
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    @endphp
    
    <!-- Calendrier -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="calendar-header">
            <h3 id="calendarTitle">
                Planning de publication - {{ $months[$month] }} {{ $year }}
            </h3>
            <div class="calendar-nav">
                <button type="button" onclick="navigateMonth(-1)" class="btn btn-secondary calendar-nav-btn">←</button>
                <button type="button" onclick="navigateMonth(1)" class="btn btn-secondary calendar-nav-btn">→</button>
            </div>
        </div>
        
        <form class="calendar-form-modern" id="calendarForm">
            <div class="calendar-form-wrapper">
                <div class="calendar-form-field">
                    <label for="month" class="calendar-form-label">
                        <svg class="calendar-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Mois</span>
                    </label>
                    <div class="calendar-select-wrapper">
                        <select id="month" name="month" class="calendar-select" data-initial-month="{{ $month }}">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ (int)$month == $i ? 'selected' : '' }}>{{ $months[$i] }}</option>
                            @endfor
                        </select>
                        <svg class="calendar-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 4l4 4 4-4"></path>
                        </svg>
                    </div>
                </div>
                <div class="calendar-form-field">
                    <label for="year" class="calendar-form-label">
                        <svg class="calendar-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Année</span>
                    </label>
                    <div class="calendar-select-wrapper">
                        <select id="year" name="year" class="calendar-select" data-initial-year="{{ $year }}">
                            @for($i = 2020; $i <= 2030; $i++)
                                <option value="{{ $i }}" {{ (int)$year == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <svg class="calendar-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 4l4 4 4-4"></path>
                        </svg>
                    </div>
                </div>
                <div class="calendar-form-field" style="flex: 2;">
                    <label for="client_filter" class="calendar-form-label">
                        <svg class="calendar-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Client</span>
                    </label>
                    <div class="client-combobox" data-client-combobox>
                        <button type="button" class="client-combobox-trigger" aria-expanded="false">
                            <span class="client-combobox-text">Tous les clients</span>
                            <span class="client-combobox-icon">▾</span>
                        </button>
                        <div class="client-combobox-panel" role="listbox">
                            <div class="client-combobox-search">
                                <input type="text" placeholder="Rechercher un client..." aria-label="Rechercher un client">
                            </div>
                            <ul class="client-combobox-list">
                                <li>
                                    <button type="button" class="is-active" data-client-value="all" data-client-label="Tous les clients">Tous les clients</button>
                                </li>
                                @foreach($clients as $client)
                                    <li>
                                        <button type="button" data-client-value="{{ $client->id }}" data-client-label="{{ $client->nom_entreprise }}">{{ $client->nom_entreprise }}</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="hidden" id="client_filter" value="all">
                    </div>
                </div>
            </div>
        </form>
        
        <div class="calendar-wrapper" id="calendarWrapper">
            <div id="calendarLoading" style="display: none; text-align: center; padding: 2rem;">
                <p>Chargement...</p>
            </div>
            <div id="calendarContent">
                @include('publications.partials.calendar-table', ['isTeamReadOnly' => $isTeamReadOnly])
            </div>
        </div>
        
        <div class="calendar-legend">
            <h4>Légende :</h4>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #28a745; border-left: 3px solid #1e7e34;"></div>
                    <span>📢 Publication</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #ffc107; border-left: 3px solid #ff9800;"></div>
                    <span>⏰ Approche (dans 3 jours)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #dc3545; border-left: 3px solid #c82333;"></div>
                    <span>🚨 En retard</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #28a745; border-left: 3px solid #1e7e34;"></div>
                    <span>✅ Complétée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #6c757d; border-left: 3px solid #5a6268;"></div>
                    <span>❌ Non réalisée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #6c757d; border-left: 3px solid #5a6268;"></div>
                    <span>🚫 Annulée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #17a2b8; border-left: 3px solid #138496;"></div>
                    <span>📅 Reprogrammée</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour gérer les événements d'une date -->
    <div id="dateModal" class="date-modal" style="display: none;">
        <div class="date-modal-overlay" onclick="closeDateModal()"></div>
        <div class="date-modal-content">
            <div class="date-modal-header">
                <h3 id="modalDateTitle">Événements du <span id="modalDateText"></span></h3>
                <button class="date-modal-close" onclick="closeDateModal()">×</button>
            </div>
            <div class="date-modal-body">
                <div id="modalLoading" class="modal-loading" style="text-align: center; padding: 2rem;">
                    <p>Chargement...</p>
                </div>
                <div id="modalContent" style="display: none;">
                    <div class="modal-section">
                        <h4>📢 Publications</h4>
                        <div id="publicationsList" class="events-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .calendar-select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .calendar-select {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #303030;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .calendar-select:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .calendar-select:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .calendar-select-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
            transition: transform 0.2s ease;
        }
        
        .calendar-select-wrapper:hover .calendar-select-arrow {
            color: #FF6A3A;
        }
        
        .calendar-select:focus + .calendar-select-arrow {
            color: #FF6A3A;
            transform: translateY(-50%) rotate(180deg);
        }
        
        .client-combobox {
            position: relative;
        }
        
        .client-combobox-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            background: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            color: #303030;
            cursor: pointer;
        }
        
        .client-combobox-trigger:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.12);
        }
        
        .client-combobox-icon {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .client-combobox-panel {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #eef0f2;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
            padding: 0.5rem;
            z-index: 20;
            display: none;
        }
        
        .client-combobox-search input {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }
        
        .client-combobox-list {
            list-style: none;
            margin: 0.5rem 0 0;
            padding: 0;
            max-height: 220px;
            overflow-y: auto;
        }
        
        .client-combobox-list button {
            width: 100%;
            text-align: left;
            padding: 0.55rem 0.75rem;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            color: #303030;
            cursor: pointer;
        }
        
        .client-combobox-list button:hover,
        .client-combobox-list button.is-active {
            background: rgba(255, 106, 58, 0.12);
            color: #FF6A3A;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .calendar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #303030;
        }
        
        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        .calendar-nav-btn {
            padding: 0.5rem 1rem;
            white-space: nowrap;
        }
        
        .calendar-form-modern {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .calendar-form-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .calendar-form-field {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .calendar-form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .calendar-form-icon {
            color: #FF6A3A;
            flex-shrink: 0;
        }
        
        .calendar-wrapper {
            overflow-x: auto;
        }
        
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .calendar-day-cell {
            transition: background-color 0.2s;
        }
        
        .calendar-day-cell:hover {
            background-color: #f0f0f0 !important;
        }
        
        .calendar-legend {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .calendar-legend h4 {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 700;
            color: #303030;
        }
        
        .legend-items {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .calendar-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .calendar-form-wrapper {
                flex-direction: column;
            }
            
            .calendar-form-field {
                width: 100%;
            }
        }

        .date-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; display: flex; align-items: center; justify-content: center; }
        .date-modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); z-index: 10000; }
        .date-modal-content { position: relative; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 800px; width: 90%; max-height: 90vh; display: flex; flex-direction: column; z-index: 10001; animation: modalSlideIn 0.3s ease-out; }
        @keyframes modalSlideIn { from { opacity: 0; transform: translateY(-20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .date-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 2px solid #f0f0f0; }
        .date-modal-header h3 { margin: 0; color: #303030; font-size: 1.5rem; }
        .date-modal-close { background: none; border: none; font-size: 2rem; cursor: pointer; color: #999; border-radius: 50%; transition: all 0.2s; }
        .date-modal-close:hover { background: #f0f0f0; color: #303030; }
        .date-modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
        .modal-section h4 { margin: 0 0 1rem 0; color: #303030; font-size: 1.25rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f0f0f0; }
        .events-list { display: flex; flex-direction: column; gap: 0.75rem; }
        .event-item { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
        .event-item:hover { background: #f0f0f0; }
        .event-item-info { flex: 1; }
        .event-item-title { font-weight: 600; color: #303030; margin-bottom: 0.25rem; }
        .event-item-details { font-size: 0.875rem; color: #666; }
        .event-item-actions { display: flex; gap: 0.5rem; }
        .event-item-actions .btn { padding: 0.375rem 0.75rem; font-size: 0.85rem; white-space: nowrap; }
        .event-status-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-info { background: #d1ecf1; color: #0c5460; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        .status-upcoming { background: #fff3cd; color: #856404; }
        .empty-events { text-align: center; padding: 2rem; color: #999; font-style: italic; }
        .calendar-day-add-btn { opacity: 0; transition: opacity 0.2s; }
        .calendar-day-cell:hover .calendar-day-add-btn { opacity: 1; }
    </style>
    
    <script>
        let isUpdatingCalendar = false;
        let pendingUpdate = null;
        const months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        let currentMonth = {{ $month }};
        let currentYear = {{ $year }};
        
        function navigateMonth(direction) {
            currentMonth += direction;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            } else if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            updateCalendar(currentMonth, currentYear);
        }
        
        function updateCalendar(month, year) {
            if (isUpdatingCalendar) {
                pendingUpdate = { month, year };
                return;
            }
            
            month = parseInt(month);
            year = parseInt(year);
            
            if (isNaN(month) || month < 1 || month > 12) {
                console.error('Mois invalide:', month);
                return;
            }
            
            if (isNaN(year) || year < 2020 || year > 2030) {
                console.error('Année invalide:', year);
                return;
            }
            
            currentMonth = month;
            currentYear = year;
            isUpdatingCalendar = true;
            
            const calendarWrapper = document.getElementById('calendarWrapper');
            const calendarContent = document.getElementById('calendarContent');
            const calendarLoading = document.getElementById('calendarLoading');
            const calendarTitle = document.getElementById('calendarTitle');
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            
            // Mise à jour IMMÉDIATE du titre et des selects
            if (calendarTitle) {
                calendarTitle.textContent = `Planning de publication - ${months[month]} ${year}`;
            }
            
            if (monthSelect) {
                monthSelect.value = month.toString();
                monthSelect.setAttribute('data-initial-month', month.toString());
            }
            
            if (yearSelect) {
                yearSelect.value = year.toString();
                yearSelect.setAttribute('data-initial-year', year.toString());
            }
            
            // Afficher le loading
            if (calendarContent) {
                calendarContent.style.display = 'none';
            }
            if (calendarLoading) {
                calendarLoading.style.display = 'block';
            }
            
            // Faire la requête AJAX
            fetch(`/api/publications-calendar?month=${month}&year=${year}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || `HTTP error! status: ${response.status}`);
                        }).catch(() => {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Vérifier s'il y a une erreur dans la réponse
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Mettre à jour le contenu du calendrier
                    if (calendarContent && data.html) {
                        calendarContent.innerHTML = data.html;
                    }
                    
                    // Synchronisation finale avec les données du serveur
                    const finalMonth = data.month ? parseInt(data.month) : month;
                    const finalYear = data.year ? parseInt(data.year) : year;
                    const finalMonthName = data.monthName || months[finalMonth];
                    
                    currentMonth = finalMonth;
                    currentYear = finalYear;
                    
                    if (calendarTitle) {
                        calendarTitle.textContent = `Planning de publication - ${finalMonthName} ${finalYear}`;
                    }
                    
                    if (monthSelect) {
                        monthSelect.value = finalMonth.toString();
                        monthSelect.setAttribute('data-initial-month', finalMonth.toString());
                    }
                    if (yearSelect) {
                        yearSelect.value = finalYear.toString();
                        yearSelect.setAttribute('data-initial-year', finalYear.toString());
                    }
                    
                    initCalendarCellEvents();

                    // Réappliquer le filtre client si actif
                    const clientFilter = document.getElementById('client_filter');
                    if (clientFilter && clientFilter.value !== 'all') {
                        filterByClient(clientFilter.value);
                    }
                    
                    // Masquer le loading et afficher le contenu
                    if (calendarLoading) {
                        calendarLoading.style.display = 'none';
                    }
                    if (calendarContent) {
                        calendarContent.style.display = 'block';
                    }
                    
                    isUpdatingCalendar = false;
                    if (pendingUpdate) {
                        const nextUpdate = pendingUpdate;
                        pendingUpdate = null;
                        updateCalendar(nextUpdate.month, nextUpdate.year);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    console.error('Détails:', error.message, error.stack);
                    if (calendarLoading) {
                        calendarLoading.innerHTML = '<p style="color: #dc3545; padding: 2rem; text-align: center;">Erreur lors du chargement du calendrier<br><small style="font-size: 0.85rem; opacity: 0.8;">' + error.message + '</small></p>';
                    }
                    // Afficher le contenu précédent si disponible
                    if (calendarContent) {
                        calendarContent.style.display = 'block';
                    }
                    isUpdatingCalendar = false;
                    if (pendingUpdate) {
                        const nextUpdate = pendingUpdate;
                        pendingUpdate = null;
                        updateCalendar(nextUpdate.month, nextUpdate.year);
                    }
                });
        }
        
        function filterByClient(clientId) {
            // Filtrer les publications individuelles (pas les cellules de jour)
            const events = document.querySelectorAll('.calendar-event[data-client-id]');
            events.forEach(event => {
                if (!clientId || clientId === 'all' || event.getAttribute('data-client-id') === clientId) {
                    event.style.display = '';
                } else {
                    event.style.display = 'none';
                }
            });
        }
        
        function syncInitialValues() {
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const calendarTitle = document.getElementById('calendarTitle');
            
            if (!monthSelect || !yearSelect || !calendarTitle) {
                return;
            }
            
            const initialMonth = parseInt(monthSelect.getAttribute('data-initial-month') || monthSelect.value);
            const initialYear = parseInt(yearSelect.getAttribute('data-initial-year') || yearSelect.value);
            
            currentMonth = initialMonth;
            currentYear = initialYear;
            
            monthSelect.value = initialMonth.toString();
            yearSelect.value = initialYear.toString();
            calendarTitle.textContent = `Planning de publication - ${months[initialMonth]} ${initialYear}`;
        }
        
        function initClientCombobox() {
            const comboboxes = document.querySelectorAll('[data-client-combobox]');

            comboboxes.forEach((combobox) => {
                const trigger = combobox.querySelector('.client-combobox-trigger');
                const panel = combobox.querySelector('.client-combobox-panel');
                const searchInput = combobox.querySelector('.client-combobox-search input');
                const listButtons = combobox.querySelectorAll('.client-combobox-list button');
                const hiddenInput = combobox.querySelector('input[type="hidden"]');
                const label = combobox.querySelector('.client-combobox-text');

                if (!trigger || !panel || !searchInput || !hiddenInput || !label) {
                    return;
                }

                const closePanel = () => {
                    if (panel.style.display === 'none') {
                        return;
                    }
                    trigger.setAttribute('aria-expanded', 'false');
                    if (typeof gsap !== 'undefined') {
                        gsap.to(panel, {
                            opacity: 0,
                            y: -6,
                            duration: 0.2,
                            ease: 'power2.inOut',
                            onComplete: () => {
                                panel.style.display = 'none';
                            }
                        });
                    } else {
                        panel.style.display = 'none';
                    }
                };

                const openPanel = () => {
                    panel.style.display = 'block';
                    trigger.setAttribute('aria-expanded', 'true');
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo(panel, { opacity: 0, y: -6 }, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out' });
                    }
                    searchInput.focus();
                };

                trigger.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (panel.style.display === 'block') {
                        closePanel();
                    } else {
                        openPanel();
                    }
                });

                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    listButtons.forEach(button => {
                        const text = button.textContent.toLowerCase();
                        const listItem = button.closest('li');
                        if (text.includes(searchTerm)) {
                            listItem.style.display = '';
                        } else {
                            listItem.style.display = 'none';
                        }
                    });
                });

                listButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const value = button.getAttribute('data-client-value');
                        const labelText = button.getAttribute('data-client-label');
                        
                        hiddenInput.value = value;
                        label.textContent = labelText;
                        
                        listButtons.forEach(btn => btn.classList.remove('is-active'));
                        button.classList.add('is-active');
                        
                        filterByClient(value);
                        
                        closePanel();
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!combobox.contains(event.target)) {
                        closePanel();
                    }
                });
            });
        }
        
        function openDateModal(date) {
            const modal = document.getElementById('dateModal');
            const modalDateText = document.getElementById('modalDateText');
            const modalLoading = document.getElementById('modalLoading');
            const modalContent = document.getElementById('modalContent');
            const dateObj = new Date(date + 'T00:00:00');
            modalDateText.textContent = dateObj.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            modal.style.display = 'flex';
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            fetch(`/api/events-by-date?date=${date}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { modalLoading.style.display = 'none'; modalContent.style.display = 'block'; displayPublications(data.publications); })
                .catch(() => { modalLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement</p>'; });
        }
        function closeDateModal() { document.getElementById('dateModal').style.display = 'none'; }
        function displayPublications(publications) {
            const container = document.getElementById('publicationsList');
            if (publications.length === 0) { container.innerHTML = '<div class="empty-events">Aucune publication prévue</div>'; return; }
            container.innerHTML = publications.map(p => {
                let sc = 'status-pending', st = 'En attente';
                if (p.status === 'completed') { sc = 'status-completed'; st = 'Complétée'; }
                else if (p.status === 'not_realized') { sc = 'status-cancelled'; st = 'Non réalisée'; }
                else if (p.status === 'cancelled') { sc = 'status-cancelled'; st = 'Annulée'; }
                else if (p.status === 'rescheduled') { sc = 'status-info'; st = 'Reprogrammée'; }
                else if (p.is_overdue) { sc = 'status-overdue'; st = 'En retard'; }
                else if (p.is_upcoming) { sc = 'status-upcoming'; st = 'À venir'; }
                return `<div class="event-item"><div class="event-item-info"><div class="event-item-title">📢 ${p.client_name} <span class="event-status-badge ${sc}">${st}</span></div><div class="event-item-details">${p.content_idea_titre || 'Aucune idée'}</div></div><div class="event-item-actions"><a href="${p.url}" class="btn btn-primary">Voir</a></div></div>`;
            }).join('');
        }
        function initCalendarCellEvents() {
            document.querySelectorAll('.calendar-day-cell').forEach(cell => {
                const n = cell.cloneNode(true); cell.parentNode.replaceChild(n, cell);
                n.addEventListener('click', function(e) { if (e.target.closest('.calendar-event') || e.target.closest('.calendar-day-add-btn')) return; const d = this.getAttribute('data-date'); if (d) openDateModal(d); });
            });
        }
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDateModal(); });

        document.addEventListener('DOMContentLoaded', function() {
            initClientCombobox();
            syncInitialValues();
            initCalendarCellEvents();
            
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            
            if (monthSelect) {
                monthSelect.addEventListener('change', function() {
                    const month = parseInt(this.value);
                    const year = parseInt(yearSelect.value);
                    updateCalendar(month, year);
                });
            }
            
            if (yearSelect) {
                yearSelect.addEventListener('change', function() {
                    const month = parseInt(monthSelect.value);
                    const year = parseInt(this.value);
                    updateCalendar(month, year);
                });
            }
        });
    </script>
@endsection

@extends('layouts.client-space')

@section('title', 'Espace Client - ' . $client->nom_entreprise)

@section('content')
    <div class="client-dashboard-header">
        <div>
            <h1 style="color: #303030; margin: 0 0 0.5rem 0;">{{ $client->nom_entreprise }}</h1>
            <p style="color: #666; margin: 0; font-size: 1.1rem;">Espace Client - Tableau de bord</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('clients.generate-report', $client) }}" class="btn btn-primary">
                <span style="margin-right: 0.5rem;">📄</span>
                Générer rapport
            </a>
        </div>
    </div>
    
    <!-- Statistiques principales -->
    <div class="client-stats-grid">
        <div class="client-stat-card">
            <div class="stat-icon">📹</div>
            <div class="stat-content">
                <h3>Tournages</h3>
                <div class="stat-value">{{ $stats['total_shootings'] }}</div>
                <div class="stat-details">
                    <span class="stat-badge pending">{{ $stats['pending_shootings'] }} en attente</span>
                    <span class="stat-badge completed">{{ $stats['completed_shootings'] }} complétés</span>
                    <span class="stat-badge non-realise">{{ $stats['non_realises_shootings'] }} non réalisés</span>
                </div>
            </div>
        </div>
        
        <div class="client-stat-card">
            <div class="stat-icon">📢</div>
            <div class="stat-content">
                <h3>Publications</h3>
                <div class="stat-value">{{ $stats['total_publications'] }}</div>
                <div class="stat-details">
                    <span class="stat-badge pending">{{ $stats['pending_publications'] }} en attente</span>
                    <span class="stat-badge completed">{{ $stats['completed_publications'] }} complétées</span>
                    <span class="stat-badge non-realise">{{ $stats['non_realises_publications'] }} non réalisées</span>
                </div>
            </div>
        </div>
        
        <div class="client-stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3>Règles de publication</h3>
                <div class="stat-value">{{ $stats['publication_rules'] }}</div>
                <div class="stat-details">
                    @if($stats['publication_rules'] > 0)
                        <span class="stat-info">Jours non recommandés configurés</span>
                    @else
                        <span class="stat-info">Aucune règle</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="client-stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3>Ce mois</h3>
                <div class="stat-value">{{ $stats['shootings_this_month'] + $stats['publications_this_month'] }}</div>
                <div class="stat-details">
                    <span class="stat-info">{{ $stats['shootings_this_month'] }} tournages</span>
                    <span class="stat-info">{{ $stats['publications_this_month'] }} publications</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendrier du client -->
    <div class="card client-calendar-card">
        <div class="calendar-header">
            <h3>
                @php
                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                @endphp
                Planning - {{ $months[$month] }} {{ $year }}
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
                        <select id="month" name="month" class="calendar-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ $months[$i] }}</option>
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
                        <select id="year" name="year" class="calendar-select">
                            @for($i = 2020; $i <= 2030; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <svg class="calendar-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 4l4 4 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="calendar-wrapper" id="calendarWrapper">
            <div id="calendarLoading" style="display: none; text-align: center; padding: 2rem; color: #666;">
                <p>Chargement du planning...</p>
            </div>
            @include('clients.partials.calendar-table')
        </div>
    </div>
    
    <!-- Événements à venir et récents -->
    <div class="client-events-grid">
        <!-- Tournages à venir -->
        <div class="card">
            <div class="card-header">📹 Tournages à venir (30 prochains jours)</div>
            @if($upcomingShootings->count() > 0)
                <div class="events-list-view">
                    @foreach($upcomingShootings as $shooting)
                        <div class="event-item-view">
                            <div class="event-date">{{ $shooting->date->format('d/m/Y') }}</div>
                            <div class="event-content">
                                <div class="event-title">Tournage</div>
                                <div class="event-details">
                                    {{ $shooting->contentIdeas->count() }} idée(s) de contenu
                                    @if($shooting->description)
                                        • {{ Str::limit($shooting->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <button onclick="openClientEventModal('shooting', {{ $shooting->id }})" class="btn btn-primary btn-sm">Voir</button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Aucun tournage à venir</p>
                </div>
            @endif
        </div>
        
        <!-- Publications à venir -->
        <div class="card">
            <div class="card-header">📢 Publications à venir (30 prochains jours)</div>
            @if($upcomingPublications->count() > 0)
                <div class="events-list-view">
                    @foreach($upcomingPublications as $publication)
                        <div class="event-item-view">
                            <div class="event-date">{{ $publication->date->format('d/m/Y') }}</div>
                            <div class="event-content">
                                <div class="event-title">{{ $publication->contentIdea->titre }}</div>
                                <div class="event-details">
                                    @if($publication->shooting)
                                        Tournage lié du {{ $publication->shooting->date->format('d/m/Y') }}
                                    @else
                                        Aucun tournage lié
                                    @endif
                                    @if($publication->description)
                                        • {{ Str::limit($publication->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <button onclick="openClientEventModal('publication', {{ $publication->id }})" class="btn btn-primary btn-sm">Voir</button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Aucune publication à venir</p>
                </div>
            @endif
        </div>
        
        <!-- Tournages récents -->
        <div class="card">
            <div class="card-header">📹 Tournages récents (30 derniers jours)</div>
            @if($recentShootings->count() > 0)
                <div class="events-list-view">
                    @foreach($recentShootings as $shooting)
                        <div class="event-item-view">
                            <div class="event-date">{{ $shooting->date->format('d/m/Y') }}</div>
                            <div class="event-content">
                                <div class="event-title">
                                    Tournage
                                    @if($shooting->isCompleted())
                                        <span class="status-badge completed">Complété</span>
                                    @elseif($shooting->status === 'cancelled')
                                        <span class="status-badge cancelled">Annulé</span>
                                    @endif
                                </div>
                                <div class="event-details">
                                    {{ $shooting->contentIdeas->count() }} idée(s) de contenu
                                    @if($shooting->description)
                                        • {{ Str::limit($shooting->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <button onclick="openClientEventModal('shooting', {{ $shooting->id }})" class="btn btn-primary btn-sm">Voir</button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Aucun tournage récent</p>
                </div>
            @endif
        </div>
        
        <!-- Publications récentes -->
        <div class="card">
            <div class="card-header">📢 Publications récentes (30 derniers jours)</div>
            @if($recentPublications->count() > 0)
                <div class="events-list-view">
                    @foreach($recentPublications as $publication)
                        <div class="event-item-view">
                            <div class="event-date">{{ $publication->date->format('d/m/Y') }}</div>
                            <div class="event-content">
                                <div class="event-title">
                                    {{ $publication->contentIdea->titre }}
                                    @if($publication->isCompleted())
                                        <span class="status-badge completed">Complétée</span>
                                    @elseif($publication->status === 'cancelled')
                                        <span class="status-badge cancelled">Annulée</span>
                                    @endif
                                </div>
                                <div class="event-details">
                                    @if($publication->shooting)
                                        Tournage lié du {{ $publication->shooting->date->format('d/m/Y') }}
                                    @else
                                        Aucun tournage lié
                                    @endif
                                    @if($publication->description)
                                        • {{ Str::limit($publication->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <button onclick="openClientEventModal('publication', {{ $publication->id }})" class="btn btn-primary btn-sm">Voir</button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Aucune publication récente</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Règles de publication -->
    <div class="card">
        <div class="card-header">📋 Règles de publication</div>
        @if($client->publicationRules->count() > 0)
            <div class="rules-list">
                <p style="margin-bottom: 1rem; color: #666;">Jours non recommandés pour les publications :</p>
                <div class="rules-grid">
                    @foreach($client->publicationRules as $rule)
                        <div class="rule-badge">
                            {{ ucfirst($rule->day_of_week) }}
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="empty-state">
                <p>Aucune règle de publication configurée</p>
            </div>
        @endif
    </div>
    
    <style>
        .client-dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .client-dashboard-header > div:last-child {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .client-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .client-stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }
        
        .client-stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .stat-icon {
            font-size: 3rem;
            flex-shrink: 0;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FF6A3A;
            margin-bottom: 0.5rem;
        }
        
        .stat-details {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .stat-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stat-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .stat-badge.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .stat-badge.non-realise {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stat-info {
            font-size: 0.85rem;
            color: #666;
        }
        
        .client-calendar-card {
            margin-bottom: 2rem;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .calendar-header h3 {
            margin: 0;
            color: #303030;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        .calendar-nav-btn {
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border-radius: 8px;
        }
        
        .calendar-nav-btn:hover {
            transform: scale(1.1);
            background-color: #FF6A3A !important;
            color: white !important;
        }
        
        .calendar-wrapper {
            display: flex;
            justify-content: flex-start;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 1rem 0;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            position: relative;
        }
        
        .calendar-wrapper::-webkit-scrollbar {
            height: 8px;
        }
        
        .calendar-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .calendar-wrapper::-webkit-scrollbar-thumb {
            background: #FF6A3A;
            border-radius: 10px;
        }
        
        .calendar-wrapper::-webkit-scrollbar-thumb:hover {
            background: #e55a2a;
        }
        
        .calendar-table {
            margin: 0;
            width: 100%;
            min-width: 100%;
            table-layout: fixed;
        }
        
        
        /* Modern Calendar Form Styles */
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
        
        .calendar-input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #303030;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .calendar-input:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .calendar-input:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .calendar-submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 106, 58, 0.3);
            white-space: nowrap;
            min-height: 48px;
        }
        
        .calendar-submit-btn:hover {
            background: linear-gradient(135deg, #e55a2a 0%, #d44a1a 100%);
            box-shadow: 0 6px 16px rgba(255, 106, 58, 0.4);
            transform: translateY(-2px);
        }
        
        .calendar-submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(255, 106, 58, 0.3);
        }
        
        .calendar-submit-btn svg {
            transition: transform 0.3s ease;
        }
        
        .calendar-submit-btn:hover svg {
            transform: translateX(4px);
        }
        
        @media (max-width: 768px) {
            .calendar-form-modern {
                padding: 1rem;
            }
            
            .calendar-form-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            
            .calendar-form-field {
                min-width: 100%;
            }
            
            .calendar-submit-btn {
                width: 100%;
                margin-top: 0.5rem;
            }
        }
        
        .client-events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .events-list-view {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .event-item-view {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #FF6A3A;
            transition: all 0.2s;
        }
        
        .event-item-view:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        
        .event-date {
            font-weight: 700;
            color: #FF6A3A;
            min-width: 90px;
            font-size: 0.9rem;
        }
        
        .event-content {
            flex: 1;
        }
        
        .event-title {
            font-weight: 600;
            color: #303030;
            margin-bottom: 0.25rem;
        }
        
        .event-details {
            font-size: 0.85rem;
            color: #666;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .rules-list {
            padding: 1rem 0;
        }
        
        .rules-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .rule-badge {
            padding: 0.5rem 1rem;
            background: #fff3cd;
            color: #856404;
            border-radius: 6px;
            font-weight: 600;
            border: 2px solid #ffc107;
        }
        
        @media (max-width: 768px) {
            .client-dashboard-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .client-dashboard-header > div:last-child {
                width: 100%;
            }
            
            .client-dashboard-header > div:last-child .btn {
                flex: 1;
                text-align: center;
            }
            
            .client-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .client-events-grid {
                grid-template-columns: 1fr;
            }
            
            .event-item-view {
                flex-direction: column;
                align-items: stretch;
            }
            
            .event-date {
                min-width: auto;
            }
            
            /* Calendrier responsive mobile */
            .calendar-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1rem;
            }
            
            .calendar-header h3 {
                font-size: 1.25rem;
                text-align: center;
            }
            
            .calendar-nav {
                justify-content: center;
            }
            
            .calendar-wrapper {
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                padding: 0.5rem 0;
                width: 100%;
                position: relative;
            }
            
            .calendar-table {
                min-width: 700px;
                width: 700px;
                margin: 0;
            }
            
            .calendar-table th,
            .calendar-table td {
                min-width: 100px;
                width: 100px;
            }
            
            .client-calendar-day {
                height: 120px !important;
                padding: 0.4rem !important;
            }
            
            .client-calendar-events {
                max-height: 85px !important;
                font-size: 0.65rem !important;
            }
            
            .client-calendar-event {
                padding: 0.2rem 0.4rem !important;
                font-size: 0.65rem !important;
                margin-bottom: 0.2rem !important;
            }
            
            .client-day-number {
                font-size: 0.9rem !important;
                margin-bottom: 0.3rem !important;
            }
            
            /* Amélioration des stats sur mobile */
            .client-stat-card {
                padding: 1rem;
            }
            
            .stat-icon {
                font-size: 2.5rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
            
            /* Amélioration du formulaire sur mobile */
            .calendar-form-modern {
                padding: 1rem;
            }
            
            /* Amélioration des cartes */
            .card {
                padding: 1rem;
            }
            
            .card-header {
                font-size: 1rem;
                padding: 0.75rem 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .calendar-table {
                min-width: 600px;
                width: 600px;
            }
            
            .calendar-table th,
            .calendar-table td {
                min-width: 85px;
                width: 85px;
            }
            
            .client-calendar-day {
                height: 100px !important;
                padding: 0.3rem !important;
            }
            
            .client-calendar-events {
                max-height: 70px !important;
            }
        }
        
        /* Calendar Day Hover */
        .client-calendar-day {
            cursor: pointer !important;
        }
        
        .client-calendar-day:hover {
            background-color: #f0f0f0 !important;
        }
        
        .client-day-number {
            pointer-events: none;
        }
        
        .client-calendar-events {
            pointer-events: none;
        }
        
        .client-calendar-event {
            pointer-events: auto !important;
            user-select: none;
        }
        
        /* Client Date Modal */
        .client-date-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .client-date-modal.active {
            display: flex;
        }
        
        .client-date-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        
        .client-date-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            z-index: 10001;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .client-date-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .client-date-modal-header h3 {
            margin: 0;
            color: #303030;
            font-size: 1.5rem;
        }
        
        .client-date-modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            line-height: 1;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }
        
        .client-date-modal-close:hover {
            background: #f0f0f0;
            color: #303030;
        }
        
        .client-date-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .client-modal-section {
            margin-bottom: 2rem;
        }
        
        .client-modal-section:last-child {
            margin-bottom: 0;
        }
        
        .client-modal-section h4 {
            margin: 0 0 1rem 0;
            color: #303030;
            font-size: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .client-modal-events-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .client-modal-event-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.2s;
        }
        
        .client-modal-event-item:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .client-modal-event-title {
            font-weight: 600;
            color: #303030;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .client-modal-event-details {
            font-size: 0.875rem;
            color: #666;
            line-height: 1.6;
        }
        
        .client-modal-event-details strong {
            color: #303030;
        }
        
        .client-modal-loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .client-modal-empty {
            text-align: center;
            padding: 2rem;
            color: #999;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .client-date-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .client-date-modal-header {
                padding: 1rem;
            }
            
            .client-date-modal-header h3 {
                font-size: 1.25rem;
            }
            
            .client-date-modal-body {
                padding: 1rem;
            }
        }
    </style>
    
    <!-- Modal pour afficher les détails d'une date -->
    <div id="clientDateModal" class="client-date-modal">
        <div class="client-date-modal-overlay" onclick="closeClientDateModal()"></div>
        <div class="client-date-modal-content">
            <div class="client-date-modal-header">
                <h3 id="clientModalDateTitle">Événements du <span id="clientModalDateText"></span></h3>
                <button class="client-date-modal-close" onclick="closeClientDateModal()">×</button>
            </div>
            <div class="client-date-modal-body">
                <div id="clientModalLoading" class="client-modal-loading">
                    <p>Chargement...</p>
                </div>
                <div id="clientModalContent" style="display: none;">
                    <!-- Tournages -->
                    <div class="client-modal-section">
                        <h4>📹 Tournages</h4>
                        <div id="clientShootingsList" class="client-modal-events-list"></div>
                    </div>
                    
                    <!-- Publications -->
                    <div class="client-modal-section">
                        <h4>📢 Publications</h4>
                        <div id="clientPublicationsList" class="client-modal-events-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const clientId = {{ $client->id }};
        
        
        function displayClientShootings(shootings) {
            const container = document.getElementById('clientShootingsList');
            
            if (shootings.length === 0) {
                container.innerHTML = '<div class="client-modal-empty">Aucun tournage prévu</div>';
                return;
            }
            
            container.innerHTML = shootings.map(shooting => {
                const contentIdeasText = shooting.content_ideas.length > 0 
                    ? shooting.content_ideas.join(', ') 
                    : 'Aucune idée de contenu';
                
                return `
                    <div class="client-modal-event-item">
                        <div class="client-modal-event-title">
                            📹 Tournage
                            <span class="stat-badge ${shooting.status === 'completed' ? 'completed' : (shooting.status === 'cancelled' ? 'non-realise' : 'pending')}">${shooting.status_text}</span>
                        </div>
                        <div class="client-modal-event-details">
                            <p><strong>Date :</strong> ${new Date(shooting.date + 'T00:00:00').toLocaleDateString('fr-FR')}</p>
                            <p><strong>Statut :</strong> ${shooting.status_text}</p>
                            <p><strong>Idées de contenu :</strong> ${contentIdeasText}</p>
                            ${shooting.description ? `<p><strong>Description :</strong> ${shooting.description}</p>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function displayClientPublications(publications) {
            const container = document.getElementById('clientPublicationsList');
            
            if (publications.length === 0) {
                container.innerHTML = '<div class="client-modal-empty">Aucune publication prévue</div>';
                return;
            }
            
            container.innerHTML = publications.map(publication => {
                const shootingText = publication.shooting_date 
                    ? `Tournage lié du ${publication.shooting_date}` 
                    : 'Aucun tournage lié';
                
                return `
                    <div class="client-modal-event-item">
                        <div class="client-modal-event-title">
                            📢 Publication
                            <span class="stat-badge ${publication.status === 'completed' ? 'completed' : (publication.status === 'cancelled' ? 'non-realise' : 'pending')}">${publication.status_text}</span>
                        </div>
                        <div class="client-modal-event-details">
                            <p><strong>Date :</strong> ${new Date(publication.date + 'T00:00:00').toLocaleDateString('fr-FR')}</p>
                            <p><strong>Statut :</strong> ${publication.status_text}</p>
                            <p><strong>Idée de contenu :</strong> ${publication.content_idea_titre || 'Aucune'}</p>
                            <p><strong>Tournage :</strong> ${shootingText}</p>
                            ${publication.description ? `<p><strong>Description :</strong> ${publication.description}</p>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Fonction globale pour ouvrir la modal avec une date (appelée depuis le calendrier)
        window.openClientDateModal = function(date) {
            if (!date) {
                console.error('Date manquante');
                return;
            }
            
            const modal = document.getElementById('clientDateModal');
            const modalDateText = document.getElementById('clientModalDateText');
            const modalLoading = document.getElementById('clientModalLoading');
            const modalContent = document.getElementById('clientModalContent');
            
            // Formater la date en français
            const dateObj = new Date(date + 'T00:00:00');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateFormatted = dateObj.toLocaleDateString('fr-FR', options);
            modalDateText.textContent = dateFormatted;
            
            // Afficher la modal
            modal.classList.add('active');
            modalLoading.style.display = 'block';
            modalContent.style.display = 'block';
            
            // Charger les événements
            fetch(`/api/client-events-by-date?date=${date}&client_id=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    modalLoading.style.display = 'none';
                    
                    // Afficher les tournages
                    displayClientShootings(data.shootings);
                    
                    // Afficher les publications
                    displayClientPublications(data.publications);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    modalLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement des événements</p>';
                });
        };
        
        // Fonction globale pour ouvrir la modal avec un événement spécifique (appelée depuis les boutons "Voir")
        window.openClientEventModal = function(type, id) {
            if (!type || !id) {
                console.error('Type ou ID manquant');
                return;
            }
            
            const modal = document.getElementById('clientDateModal');
            const modalDateText = document.getElementById('clientModalDateText');
            const modalLoading = document.getElementById('clientModalLoading');
            const modalContent = document.getElementById('clientModalContent');
            
            // Afficher la modal
            modal.classList.add('active');
            modalLoading.style.display = 'block';
            modalContent.style.display = 'block';
            
            // Charger les détails de l'événement
            fetch(`/api/client-event-details?type=${type}&id=${id}&client_id=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        modalLoading.innerHTML = `<p style="color: #dc3545;">${data.error}</p>`;
                        return;
                    }
                    
                    modalLoading.style.display = 'none';
                    
                    // Formater la date en français
                    const dateObj = new Date(data.data.date + 'T00:00:00');
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const dateFormatted = dateObj.toLocaleDateString('fr-FR', options);
                    modalDateText.textContent = dateFormatted;
                    
                    // Afficher selon le type
                    if (data.type === 'shooting') {
                        displayClientShootings([data.data]);
                        document.getElementById('clientPublicationsList').innerHTML = '<div class="client-modal-empty">Aucune publication</div>';
                    } else if (data.type === 'publication') {
                        displayClientPublications([data.data]);
                        document.getElementById('clientShootingsList').innerHTML = '<div class="client-modal-empty">Aucun tournage</div>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    modalLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement de l\'événement</p>';
                });
        };
        
        // Fonction globale pour fermer la modal
        window.closeClientDateModal = function() {
            const modal = document.getElementById('clientDateModal');
            modal.classList.remove('active');
        };
        
        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeClientDateModal();
            }
        });
        
        // Mise à jour automatique du calendrier
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        const calendarWrapper = document.getElementById('calendarWrapper');
        const calendarTable = document.getElementById('calendarTable');
        const calendarLoading = document.getElementById('calendarLoading');
        const calendarTitle = document.querySelector('.calendar-header h3');
        const months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        let updateTimeout;
        
        function updateCalendar() {
            const month = monthSelect.value;
            const year = yearSelect.value;
            
            // Afficher le loader
            if (calendarTable) calendarTable.style.display = 'none';
            if (calendarLoading) calendarLoading.style.display = 'block';
            
            // Mettre à jour le titre
            if (calendarTitle) calendarTitle.textContent = `Planning - ${months[month]} ${year}`;
            
            // Charger le calendrier via AJAX
            fetch(`/api/client-calendar?month=${month}&year=${year}&client_id=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Erreur:', data.error);
                        if (calendarLoading) {
                            calendarLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement du planning</p>';
                        }
                        return;
                    }
                    
                    // Remplacer le contenu du calendrier
                    calendarWrapper.innerHTML = '<div id="calendarLoading" style="display: none; text-align: center; padding: 2rem; color: #666;"><p>Chargement du planning...</p></div>' + data.html;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    if (calendarLoading) {
                        calendarLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement du planning</p>';
                    }
                });
        }
        
        // Écouter les changements avec debounce
        monthSelect.addEventListener('change', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updateCalendar, 300);
        });
        
        yearSelect.addEventListener('change', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updateCalendar, 300);
        });
        
        // Navigation mois précédent/suivant
        window.navigateMonth = function(direction) {
            const currentMonth = parseInt(monthSelect.value);
            const currentYear = parseInt(yearSelect.value);
            
            let newMonth = currentMonth + direction;
            let newYear = currentYear;
            
            if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            } else if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            }
            
            // Mettre à jour les selects
            monthSelect.value = newMonth;
            yearSelect.value = newYear;
            
            // Déclencher la mise à jour
            updateCalendar();
        };
    </script>
@endsection

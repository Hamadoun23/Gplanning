@extends('layouts.client-space')

@section('title', 'Espace Client - ' . $client->nom_entreprise)

@section('content')
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 1rem; background: #d4edda; color: #155724; border-radius: 8px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.5rem; padding: 1rem; background: #f8d7da; color: #721c24; border-radius: 8px; border: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="client-dashboard-header">
        <div>
            <h1 style="color: #303030; margin: 0 0 0.5rem 0;">{{ $client->nom_entreprise }}</h1>
            <p style="color: #666; margin: 0; font-size: 1.1rem;">Espace Client - Tableau de bord</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" onclick="openPlanningModal()" class="btn btn-primary">
                <span style="margin-right: 0.5rem;">📄</span>
                Générer Planning
            </button>
        </div>
    </div>
    
    <!-- Statistiques principales -->
    <div class="client-stats-grid" id="clientStatsGrid">
        <div class="client-stat-card">
            <div class="stat-icon">📹</div>
            <div class="stat-content">
                <h3>Tournages</h3>
                <div class="stat-value" id="stat-total-shootings">{{ $stats['total_shootings'] }}</div>
                <div class="stat-details">
                    <span class="stat-badge pending" id="stat-pending-shootings">{{ $stats['pending_shootings'] }} en attente</span>
                    <span class="stat-badge completed" id="stat-completed-shootings">{{ $stats['completed_shootings'] }} complétés</span>
                    <span class="stat-badge non-realise" id="stat-non-realises-shootings">{{ $stats['non_realises_shootings'] }} non réalisés</span>
                </div>
            </div>
        </div>
        
        <div class="client-stat-card">
            <div class="stat-icon">📢</div>
            <div class="stat-content">
                <h3>Publications</h3>
                <div class="stat-value" id="stat-total-publications">{{ $stats['total_publications'] }}</div>
                <div class="stat-details">
                    <span class="stat-badge pending" id="stat-pending-publications">{{ $stats['pending_publications'] }} en attente</span>
                    <span class="stat-badge completed" id="stat-completed-publications">{{ $stats['completed_publications'] }} complétées</span>
                    <span class="stat-badge non-realise" id="stat-non-realises-publications">{{ $stats['non_realises_publications'] }} non réalisées</span>
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
                <h3>Période sélectionnée</h3>
                <div class="stat-value" id="stat-total-period">{{ $stats['shootings_this_month'] + $stats['publications_this_month'] }}</div>
                <div class="stat-details">
                    <span class="stat-info" id="stat-shootings-period">{{ $stats['shootings_this_month'] }} tournages</span>
                    <span class="stat-info" id="stat-publications-period">{{ $stats['publications_this_month'] }} publications</span>
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
                    @foreach($upcomingShootings->take(5) as $shooting)
                        <div class="event-item-view">
                            <div class="event-date">{{ $shooting->date->format('d/m/Y H:i') }}</div>
                            <div class="event-content">
                                <div class="event-title">Tournage</div>
                                <div class="event-details">
                                    {{ $shooting->contentIdeas->count() }} idée(s) de contenu
                                    @if($shooting->description)
                                        • {{ Str::limit($shooting->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('shootings.show', $shooting) }}" class="btn btn-primary btn-sm">Voir</a>
                        </div>
                    @endforeach
                </div>
                @if($upcomingShootings->count() > 5)
                    <div class="events-more-section">
                        <button type="button" onclick="openEventsModal('upcoming-shootings')" class="btn btn-secondary btn-sm">
                            Voir plus ({{ $upcomingShootings->count() - 5 }})
                        </button>
                    </div>
                @endif
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
                    @foreach($upcomingPublications->take(5) as $publication)
                        <div class="event-item-view">
                            <div class="event-date">{{ $publication->date->format('d/m/Y H:i') }}</div>
                            <div class="event-content">
                                <div class="event-title">{{ $publication->contentIdea->titre }}</div>
                                <div class="event-details">
                                    @if($publication->shooting)
                                        Tournage lié du {{ $publication->shooting->date->format('d/m/Y H:i') }}
                                    @else
                                        Aucun tournage lié
                                    @endif
                                    @if($publication->description)
                                        • {{ Str::limit($publication->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('publications.show', $publication) }}" class="btn btn-primary btn-sm">Voir</a>
                        </div>
                    @endforeach
                </div>
                @if($upcomingPublications->count() > 5)
                    <div class="events-more-section">
                        <button type="button" onclick="openEventsModal('upcoming-publications')" class="btn btn-secondary btn-sm">
                            Voir plus ({{ $upcomingPublications->count() - 5 }})
                        </button>
                    </div>
                @endif
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
                    @foreach($recentShootings->take(5) as $shooting)
                        <div class="event-item-view">
                            <div class="event-date">{{ $shooting->date->format('d/m/Y H:i') }}</div>
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
                            <a href="{{ route('shootings.show', $shooting) }}" class="btn btn-primary btn-sm">Voir</a>
                        </div>
                    @endforeach
                </div>
                @if($recentShootings->count() > 5)
                    <div class="events-more-section">
                        <button type="button" onclick="openEventsModal('recent-shootings')" class="btn btn-secondary btn-sm">
                            Voir plus ({{ $recentShootings->count() - 5 }})
                        </button>
                    </div>
                @endif
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
                    @foreach($recentPublications->take(5) as $publication)
                        <div class="event-item-view">
                            <div class="event-date">{{ $publication->date->format('d/m/Y H:i') }}</div>
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
                                        Tournage lié du {{ $publication->shooting->date->format('d/m/Y H:i') }}
                                    @else
                                        Aucun tournage lié
                                    @endif
                                    @if($publication->description)
                                        • {{ Str::limit($publication->description, 50) }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('publications.show', $publication) }}" class="btn btn-primary btn-sm">Voir</a>
                        </div>
                    @endforeach
                </div>
                @if($recentPublications->count() > 5)
                    <div class="events-more-section">
                        <button type="button" onclick="openEventsModal('recent-publications')" class="btn btn-secondary btn-sm">
                            Voir plus ({{ $recentPublications->count() - 5 }})
                        </button>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <p>Aucune publication récente</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal pour afficher tous les événements -->
    <div id="eventsModal" class="events-modal">
        <div class="events-modal-overlay" onclick="closeEventsModal()"></div>
        <div class="events-modal-content">
            <div class="events-modal-header">
                <h3 id="eventsModalTitle">Événements</h3>
                <button class="events-modal-close" onclick="closeEventsModal()">×</button>
            </div>
            <div class="events-modal-body">
                <div id="eventsModalLoading" class="events-modal-loading">
                    <p>Chargement...</p>
                </div>
                <div id="eventsModalList" class="events-modal-list" style="display: none;">
                    <!-- Les événements seront chargés ici -->
                </div>
            </div>
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
    
    <!-- Liste des rapports -->
    @if(isset($monthlyReports) && isset($annualReports))
    <div class="card">
        <div class="card-header">📄 Rapports de reporting</div>
        
        <!-- Liste des rapports téléversés -->
        <div class="reports-list-section" style="padding: 1.5rem;">
            <h4 style="margin-bottom: 1.5rem; color: #303030;">Rapports téléversés</h4>
            
            <!-- Rapports mensuels -->
            <div class="report-type-section">
                <div class="report-type-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #0c5460;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <h5 style="margin: 0; color: #303030; font-size: 1.1rem;">Rapports mensuels</h5>
                    <span class="report-count-badge">{{ $monthlyReports->count() }}</span>
                </div>
                @if($monthlyReports && $monthlyReports->count() > 0)
                    <div class="reports-list">
                        @foreach($monthlyReports as $report)
                            <div class="report-item">
                                <div class="report-item-info">
                                <div class="report-item-icon" style="color: #0c5460;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </div>
                                <div class="report-item-details">
                                    <div class="report-item-name">{{ $report->original_filename }}</div>
                                    <div class="report-item-meta">
                                        @if($report->report_date)
                                            <span class="report-date-label">Date du rapport : {{ $report->report_date->format('d/m/Y') }}</span>
                                        @endif
                                        <span class="report-date">Téléversé le {{ $report->uploaded_at->format('d/m/Y à H:i') }}</span>
                                        @if($report->file_size)
                                            <span class="report-size">{{ number_format($report->file_size / 1024, 2) }} Ko</span>
                                        @endif
                                    </div>
                                </div>
                                </div>
                                <div class="report-item-actions">
                                    <a href="{{ route('clients.reports.download', [$client, $report]) }}" class="btn btn-primary btn-sm" title="Télécharger">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 1.5rem; text-align: center; color: #999; font-style: italic;">
                        <p>Aucun rapport mensuel téléversé</p>
                    </div>
                @endif
            </div>
            
            <!-- Rapports annuels -->
            <div class="report-type-section" style="margin-top: 2rem;">
                <div class="report-type-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #155724;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h5 style="margin: 0; color: #303030; font-size: 1.1rem;">Rapports annuels</h5>
                    <span class="report-count-badge">{{ $annualReports->count() }}</span>
                </div>
                @if($annualReports && $annualReports->count() > 0)
                    <div class="reports-list">
                        @foreach($annualReports as $report)
                            <div class="report-item">
                                <div class="report-item-info">
                                <div class="report-item-icon" style="color: #155724;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </div>
                                <div class="report-item-details">
                                    <div class="report-item-name">{{ $report->original_filename }}</div>
                                    <div class="report-item-meta">
                                        @if($report->report_date)
                                            <span class="report-date-label">Date du rapport : {{ $report->report_date->format('d/m/Y') }}</span>
                                        @endif
                                        <span class="report-date">Téléversé le {{ $report->uploaded_at->format('d/m/Y à H:i') }}</span>
                                        @if($report->file_size)
                                            <span class="report-size">{{ number_format($report->file_size / 1024, 2) }} Ko</span>
                                        @endif
                                    </div>
                                </div>
                                </div>
                                <div class="report-item-actions">
                                    <a href="{{ route('clients.reports.download', [$client, $report]) }}" class="btn btn-primary btn-sm" title="Télécharger">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 1.5rem; text-align: center; color: #999; font-style: italic;">
                        <p>Aucun rapport annuel téléversé</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal pour générer le planning -->
    <div id="planningModal" class="planning-modal">
        <div class="planning-modal-overlay" onclick="closePlanningModal()"></div>
        <div class="planning-modal-content">
            <div class="planning-modal-header">
                <h3>Générer le Planning</h3>
                <button class="planning-modal-close" onclick="closePlanningModal()">×</button>
            </div>
            <div class="planning-modal-body">
                <form id="planningForm">
                    <div class="planning-option-group">
                        <label class="planning-option">
                            <input type="radio" name="type" value="annual" id="planning_annual" checked>
                            <div class="planning-option-content">
                                <div class="planning-option-icon">📅</div>
                                <div class="planning-option-text">
                                    <strong>Planning Annuel</strong>
                                    <span>Générer le planning pour toute l'année</span>
                                </div>
                            </div>
                        </label>
                        
                        <label class="planning-option">
                            <input type="radio" name="type" value="monthly" id="planning_monthly">
                            <div class="planning-option-content">
                                <div class="planning-option-icon">📆</div>
                                <div class="planning-option-text">
                                    <strong>Planning Mensuel</strong>
                                    <span>Générer le planning pour un mois spécifique</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <div id="monthSelection" class="planning-month-selection" style="display: none;">
                        <label for="planning_month" class="planning-form-label">
                            <svg class="planning-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Mois</span>
                        </label>
                        <div class="planning-select-wrapper">
                            <select id="planning_month" name="month" class="planning-select">
                                @php
                                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                                @endphp
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ $months[$i] }}</option>
                                @endfor
                            </select>
                            <svg class="planning-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4l4 4 4-4"></path>
                            </svg>
                        </div>
                        
                        <label for="planning_year" class="planning-form-label" style="margin-top: 1rem;">
                            <svg class="planning-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Année</span>
                        </label>
                        <div class="planning-select-wrapper">
                            <select id="planning_year" name="year" class="planning-select">
                                @for($i = 2020; $i <= 2030; $i++)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <svg class="planning-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4l4 4 4-4"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div id="yearSelection" class="planning-year-selection">
                        <label for="planning_year_annual" class="planning-form-label">
                            <svg class="planning-form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Année</span>
                        </label>
                        <div class="planning-select-wrapper">
                            <select id="planning_year_annual" class="planning-select">
                                @for($i = 2020; $i <= 2030; $i++)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <svg class="planning-select-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 4l4 4 4-4"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <input type="hidden" name="type" id="planning_type_input" value="annual">
                    <input type="hidden" name="year" id="planning_year_input" value="{{ $year }}">
                    <input type="hidden" name="month" id="planning_month_input" value="{{ $month }}">
                    
                    <div class="planning-modal-actions">
                        <button type="button" onclick="closePlanningModal()" class="btn btn-secondary">Annuler</button>
                        <button type="button" id="generatePlanningBtn" onclick="generatePlanning()" class="btn btn-primary">
                            <span id="generatePlanningIcon" style="margin-right: 0.5rem;">📄</span>
                            <span id="generatePlanningText">Générer le Planning</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        
        .events-more-section {
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #f0f0f0;
        }
        
        /* Styles pour la modal des événements */
        .events-modal {
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
        
        .events-modal.active {
            display: flex;
        }
        
        .events-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        
        .events-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            z-index: 10001;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        .events-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .events-modal-header h3 {
            margin: 0;
            color: #303030;
            font-size: 1.5rem;
        }
        
        .events-modal-close {
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
        
        .events-modal-close:hover {
            background: #f0f0f0;
            color: #303030;
        }
        
        .events-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .events-modal-loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .events-modal-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .event-item-modal {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #FF6A3A;
            transition: all 0.2s;
        }
        
        .event-item-modal:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        
        .event-date-modal {
            font-weight: 700;
            color: #FF6A3A;
            min-width: 90px;
            font-size: 0.9rem;
        }
        
        .event-content-modal {
            flex: 1;
        }
        
        .event-title-modal {
            font-weight: 600;
            color: #303030;
            margin-bottom: 0.25rem;
        }
        
        .event-details-modal {
            font-size: 0.85rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .events-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .events-modal-header {
                padding: 1rem;
            }
            
            .events-modal-header h3 {
                font-size: 1.25rem;
            }
            
            .events-modal-body {
                padding: 1rem;
            }
            
            .event-item-modal {
                flex-direction: column;
                align-items: stretch;
            }
            
            .event-date-modal {
                min-width: auto;
            }
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
        
        /* Styles pour le formulaire d'upload de rapports */
        .report-upload-section {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .report-upload-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .report-form-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .report-form-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .report-form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .report-form-icon {
            color: #FF6A3A;
            flex-shrink: 0;
        }
        
        .report-select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .report-select {
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
        
        .report-select:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .report-select:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .report-select-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
            transition: transform 0.2s ease;
        }
        
        .report-select-wrapper:hover .report-select-arrow {
            color: #FF6A3A;
        }
        
        .report-select:focus + .report-select-arrow {
            color: #FF6A3A;
            transform: translateY(-50%) rotate(180deg);
        }
        
        .report-date-input {
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
        
        .report-date-input:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .report-date-input:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .report-file-wrapper {
            position: relative;
        }
        
        .report-file-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .report-file-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: white;
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .report-file-label:hover {
            border-color: #FF6A3A;
            background: #fff5f2;
        }
        
        .report-file-text {
            font-size: 0.9rem;
            color: #666;
            flex: 1;
        }
        
        .report-file-button {
            padding: 0.5rem 1rem;
            background: #FF6A3A;
            color: white;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .report-file-label:hover .report-file-button {
            background: #e55a2a;
        }
        
        .report-file-input:focus + .report-file-label {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .report-upload-btn {
            align-self: flex-start;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Styles pour la liste des rapports */
        .reports-list-section {
            padding: 1.5rem;
        }
        
        .report-type-section {
            margin-bottom: 2rem;
        }
        
        .report-type-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .report-count-badge {
            margin-left: auto;
            padding: 0.25rem 0.75rem;
            background: #FF6A3A;
            color: white;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .reports-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .report-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #FF6A3A;
            transition: all 0.2s;
        }
        
        .report-item:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        
        .report-item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }
        
        .report-item-icon {
            color: #FF6A3A;
            flex-shrink: 0;
        }
        
        .report-item-details {
            flex: 1;
        }
        
        .report-item-name {
            font-weight: 600;
            color: #303030;
            margin-bottom: 0.5rem;
        }
        
        .report-item-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .report-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .report-badge-monthly {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .report-badge-annual {
            background: #d4edda;
            color: #155724;
        }
        
        .report-date {
            font-size: 0.85rem;
            color: #666;
        }
        
        .report-date-label {
            font-size: 0.85rem;
            color: #FF6A3A;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .report-size {
            font-size: 0.85rem;
            color: #666;
        }
        
        .report-item-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        
        .report-delete-form {
            display: inline;
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
            
            .report-form-fields {
                grid-template-columns: 1fr;
            }
            
            .report-item {
                flex-direction: column;
                align-items: stretch;
            }
            
            .report-item-actions {
                justify-content: flex-end;
                margin-top: 1rem;
            }
        }
    </style>
    
    <script>
        const clientId = {{ $client->id }};
        
        // Mise à jour automatique du calendrier et des statistiques
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        const calendarWrapper = document.getElementById('calendarWrapper');
        const calendarTable = document.getElementById('calendarTable');
        const calendarLoading = document.getElementById('calendarLoading');
        const calendarTitle = document.querySelector('.calendar-header h3');
        const months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        let updateTimeout;
        
        function updateStats() {
            const month = monthSelect.value;
            const year = yearSelect.value;
            
            fetch(`{{ route('clients.get-stats', $client) }}?month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour les statistiques
                    document.getElementById('stat-total-shootings').textContent = data.total_shootings;
                    document.getElementById('stat-pending-shootings').textContent = data.pending_shootings + ' en attente';
                    document.getElementById('stat-completed-shootings').textContent = data.completed_shootings + ' complétés';
                    document.getElementById('stat-non-realises-shootings').textContent = data.non_realises_shootings + ' non réalisés';
                    
                    document.getElementById('stat-total-publications').textContent = data.total_publications;
                    document.getElementById('stat-pending-publications').textContent = data.pending_publications + ' en attente';
                    document.getElementById('stat-completed-publications').textContent = data.completed_publications + ' complétées';
                    document.getElementById('stat-non-realises-publications').textContent = data.non_realises_publications + ' non réalisées';
                    
                    const totalPeriod = data.shootings_this_month + data.publications_this_month;
                    document.getElementById('stat-total-period').textContent = totalPeriod;
                    document.getElementById('stat-shootings-period').textContent = data.shootings_this_month + ' tournages';
                    document.getElementById('stat-publications-period').textContent = data.publications_this_month + ' publications';
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour des statistiques:', error);
                });
        }
        
        function updateCalendar() {
            const month = monthSelect.value;
            const year = yearSelect.value;
            
            // Mettre à jour les statistiques en même temps que le calendrier
            updateStats();
            
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
        
        // Modal pour générer le planning
        function openPlanningModal() {
            document.getElementById('planningModal').classList.add('active');
        }
        
        function closePlanningModal() {
            document.getElementById('planningModal').classList.remove('active');
        }
        
        // Gérer l'affichage des options selon le type sélectionné
        const planningAnnual = document.getElementById('planning_annual');
        const planningMonthly = document.getElementById('planning_monthly');
        const monthSelection = document.getElementById('monthSelection');
        const yearSelection = document.getElementById('yearSelection');
        const planningTypeInput = document.getElementById('planning_type_input');
        const planningYearInput = document.getElementById('planning_year_input');
        const planningMonthInput = document.getElementById('planning_month_input');
        const planningYear = document.getElementById('planning_year');
        const planningYearAnnual = document.getElementById('planning_year_annual');
        const planningMonth = document.getElementById('planning_month');
        
        // Synchroniser les valeurs des années
        function syncYearValues() {
            const yearValue = planningYearAnnual ? planningYearAnnual.value : (planningYear ? planningYear.value : '{{ $year }}');
            if (planningYearInput) planningYearInput.value = yearValue;
            if (planningYear && planningYearAnnual) {
                planningYear.value = yearValue;
                planningYearAnnual.value = yearValue;
            }
        }
        
        // Synchroniser la valeur du mois
        function syncMonthValue() {
            if (planningMonth && planningMonthInput) {
                planningMonthInput.value = planningMonth.value;
            }
        }
        
        if (planningAnnual && planningMonthly) {
            planningAnnual.addEventListener('change', function() {
                if (this.checked) {
                    monthSelection.style.display = 'none';
                    yearSelection.style.display = 'block';
                    if (planningTypeInput) planningTypeInput.value = 'annual';
                    syncYearValues();
                }
            });
            
            planningMonthly.addEventListener('change', function() {
                if (this.checked) {
                    monthSelection.style.display = 'block';
                    yearSelection.style.display = 'none';
                    if (planningTypeInput) planningTypeInput.value = 'monthly';
                    syncYearValues();
                    syncMonthValue();
                }
            });
        }
        
        // Synchroniser les changements d'année
        if (planningYearAnnual) {
            planningYearAnnual.addEventListener('change', syncYearValues);
        }
        
        if (planningYear) {
            planningYear.addEventListener('change', syncYearValues);
        }
        
        if (planningMonth) {
            planningMonth.addEventListener('change', syncMonthValue);
        }
        
        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePlanningModal();
            }
        });
        
        // Générer le planning via AJAX (sans recharger la page)
        window.generatePlanning = function() {
            const generateBtn = document.getElementById('generatePlanningBtn');
            const generateText = document.getElementById('generatePlanningText');
            const generateIcon = document.getElementById('generatePlanningIcon');
            
            // Désactiver le bouton et afficher le chargement
            generateBtn.disabled = true;
            generateText.textContent = 'Génération en cours...';
            generateIcon.innerHTML = '⏳';
            
            // Récupérer les valeurs du formulaire
            const type = document.querySelector('input[name="type"]:checked').value;
            const yearInput = document.getElementById('planning_year_input');
            const monthInput = document.getElementById('planning_month_input');
            
            let url = '{{ route("clients.generate-report", $client) }}?type=' + encodeURIComponent(type) + '&year=' + encodeURIComponent(yearInput.value);
            
            if (type === 'monthly') {
                url += '&month=' + encodeURIComponent(monthInput.value);
            }
            
            // Créer un lien temporaire pour télécharger le fichier
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Réactiver le bouton et fermer la modal après un court délai
            setTimeout(function() {
                generateBtn.disabled = false;
                generateText.textContent = 'Générer le Planning';
                generateIcon.innerHTML = '📄';
                closePlanningModal();
            }, 500);
        };
        
        // Empêcher la soumission normale du formulaire
        const planningForm = document.getElementById('planningForm');
        if (planningForm) {
            planningForm.addEventListener('submit', function(e) {
                e.preventDefault();
                generatePlanning();
            });
        }
        
        // Modal pour afficher tous les événements
        const eventsModalTitles = {
            'upcoming-shootings': '📹 Tournages à venir (30 prochains jours)',
            'upcoming-publications': '📢 Publications à venir (30 prochains jours)',
            'recent-shootings': '📹 Tournages récents (30 derniers jours)',
            'recent-publications': '📢 Publications récentes (30 derniers jours)'
        };
        
        window.openEventsModal = function(type) {
            const modal = document.getElementById('eventsModal');
            const modalTitle = document.getElementById('eventsModalTitle');
            const modalLoading = document.getElementById('eventsModalLoading');
            const modalList = document.getElementById('eventsModalList');
            
            // Afficher la modal
            modal.classList.add('active');
            modalTitle.textContent = eventsModalTitles[type] || 'Événements';
            modalLoading.style.display = 'block';
            modalList.style.display = 'none';
            modalList.innerHTML = '';
            
            // Charger les événements via AJAX
            fetch(`{{ route('clients.get-events', $client) }}?type=${type}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Erreur HTTP ' + response.status);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    modalLoading.style.display = 'none';
                    modalList.style.display = 'block';
                    
                    if (data.error) {
                        modalList.innerHTML = `<div class="empty-state"><p style="color: #dc3545;">${data.error}</p></div>`;
                        return;
                    }
                    
                    if (data.events && data.events.length > 0) {
                        modalList.innerHTML = data.events.map(event => {
                            const titleHtml = event.titleHtml || event.title;
                            return `
                                <div class="event-item-modal">
                                    <div class="event-date-modal">${event.date}</div>
                                    <div class="event-content-modal">
                                        <div class="event-title-modal">${titleHtml}</div>
                                        <div class="event-details-modal">${event.details || ''}</div>
                                    </div>
                                    <a href="${event.url}" class="btn btn-primary btn-sm">Voir</a>
                                </div>
                            `;
                        }).join('');
                    } else {
                        modalList.innerHTML = '<div class="empty-state"><p>Aucun événement</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    modalLoading.style.display = 'none';
                    modalList.style.display = 'block';
                    modalList.innerHTML = `<div class="empty-state"><p style="color: #dc3545;">Erreur lors du chargement des événements: ${error.message}</p></div>`;
                });
        };
        
        window.closeEventsModal = function() {
            document.getElementById('eventsModal').classList.remove('active');
        };
        
        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const eventsModal = document.getElementById('eventsModal');
                if (eventsModal && eventsModal.classList.contains('active')) {
                    closeEventsModal();
                }
            }
        });
    </script>
    
    <style>
        /* Styles pour la modal de génération de planning */
        .planning-modal {
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
        
        .planning-modal.active {
            display: flex;
        }
        
        .planning-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        
        .planning-modal-content {
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
        
        .planning-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .planning-modal-header h3 {
            margin: 0;
            color: #303030;
            font-size: 1.5rem;
        }
        
        .planning-modal-close {
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
        
        .planning-modal-close:hover {
            background: #f0f0f0;
            color: #303030;
        }
        
        .planning-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .planning-option-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .planning-option {
            display: block;
            cursor: pointer;
        }
        
        .planning-option input[type="radio"] {
            display: none;
        }
        
        .planning-option-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .planning-option:hover .planning-option-content {
            border-color: #FF6A3A;
            background: #fff5f2;
        }
        
        .planning-option input[type="radio"]:checked + .planning-option-content {
            border-color: #FF6A3A;
            background: #fff5f2;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .planning-option-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        .planning-option-text {
            flex: 1;
        }
        
        .planning-option-text strong {
            display: block;
            color: #303030;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .planning-option-text span {
            display: block;
            color: #666;
            font-size: 0.875rem;
        }
        
        .planning-month-selection,
        .planning-year-selection {
            margin-bottom: 1.5rem;
        }
        
        .planning-form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .planning-form-icon {
            color: #FF6A3A;
            flex-shrink: 0;
        }
        
        .planning-select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .planning-select {
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
        
        .planning-select:hover {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .planning-select:focus {
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
        }
        
        .planning-select-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
            transition: transform 0.2s ease;
        }
        
        .planning-select-wrapper:hover .planning-select-arrow {
            color: #FF6A3A;
        }
        
        .planning-select:focus + .planning-select-arrow {
            color: #FF6A3A;
            transform: translateY(-50%) rotate(180deg);
        }
        
        .planning-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f0f0;
        }
        
        @media (max-width: 768px) {
            .planning-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .planning-modal-header {
                padding: 1rem;
            }
            
            .planning-modal-header h3 {
                font-size: 1.25rem;
            }
            
            .planning-modal-body {
                padding: 1rem;
            }
            
            .planning-modal-actions {
                flex-direction: column;
            }
            
            .planning-modal-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

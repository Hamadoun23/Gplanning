@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    @php
        $isTeamReadOnly = auth()->check() && auth()->user()->isTeam();
    @endphp
    <div class="dashboard-header">
        <div class="dashboard-title" data-gsap="fadeIn">
            <div class="dashboard-title-badge">
                <span class="dashboard-badge-icon">📊</span>
                <span class="dashboard-kicker">Vue globale</span>
            </div>
            <h2>
                <span class="dashboard-title-main">Tableau de bord</span>
                <span class="dashboard-title-accent"></span>
            </h2>
            <p class="dashboard-subtitle">
                <span class="dashboard-subtitle-icon">✨</span>
                Gérez vos plannings et générez des rapports en un clic.
            </p>
        </div>
        <div class="dashboard-actions">
            <div class="report-card" data-gsap="fadeInUp">
                @php
                    // Adresses mails Finance, RH & Direction
                    $financeEmail = 'thsylla@gdamali.net';
                    $rhEmail = 'askoita@gdamali.net';
                    $directionEmail1 = 'yhdiallo@gdamali.net';
                    $directionEmail2 = 'ysacko@gdamali.net';
                    $allRecipients = $financeEmail . ',' . $rhEmail . ',' . $directionEmail1 . ',' . $directionEmail2;

                    // Mois / année courants affichés
                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

                    // Sujet du mail
                    $mailSubject = 'Préparation tournages - ' . $months[$month] . ' ' . $year;

                    // Construction de la liste des tournages du mois (tous clients)
                    $lines = [];
                    if (isset($shootingsForMonth) && $shootingsForMonth->count() > 0) {
                        foreach ($shootingsForMonth as $shooting) {
                            $lines[] = '- ' . \Carbon\Carbon::parse($shooting->date)->format('d/m/Y H:i') . ' - ' . ($shooting->client ? $shooting->client->nom_entreprise : 'Client inconnu');
                        }
                    } else {
                        $lines[] = 'Aucun tournage planifié pour ce mois.';
                    }

                    $mailBodyText = "Bonjour La Direction, Finance & RH,\n\nNous aurons besoin de votre présence / préparation pour les tournages prévus durant la période de " . $months[$month] . " " . $year . ".\n\nListe des tournages prévus :\n" . implode("\n", $lines) . "\n\nMerci d'anticiper les besoins (budgets, ressources humaines, déplacements, etc.).\n\nBien à vous,";
                    $mailBody = rawurlencode($mailBodyText);
                    $mailtoLinkFinanceRh = 'mailto:' . $allRecipients . '?subject=' . rawurlencode($mailSubject) . '&body=' . $mailBody;
                @endphp

                @if(auth()->check() && auth()->user()->isAdmin())
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <a href="{{ $mailtoLinkFinanceRh }}" class="btn btn-warning" style="display: inline-flex; align-items: center;">
                                <span style="margin-right: 0.5rem;">✉️</span>
                                Prévenir Finance & RH (tous les tournages du mois)
                            </a>
                        </div>
                    </div>
                @endif

                <form action="{{ route('dashboard.generate-report') }}" method="GET" class="report-form report-form-modern">
                    <div class="report-field">
                        <label class="report-label">Client</label>
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
                                    @foreach(\App\Models\Client::orderBy('nom_entreprise')->get() as $client)
                                        <li>
                                            <button type="button" data-client-value="{{ $client->id }}" data-client-label="{{ $client->nom_entreprise }}">{{ $client->nom_entreprise }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <input type="hidden" name="client_id" value="all">
                        </div>
                    </div>
                    <div class="report-field">
                        <label class="report-label">Période</label>
                        <div class="report-period-toggle" role="group" aria-label="Période du rapport">
                            <label class="period-option">
                                <input type="radio" name="period" value="weekly">
                                <span>Hebdo</span>
                            </label>
                            <label class="period-option">
                                <input type="radio" name="period" value="monthly" checked>
                                <span>Mensuel</span>
                            </label>
                            <label class="period-option">
                                <input type="radio" name="period" value="annual">
                                <span>Annuel</span>
                            </label>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button type="submit" class="btn btn-primary report-btn">
                            <span class="btn-icon">📄</span>
                            <span class="btn-text">Générer rapport</span>
                        </button>
                        <a href="{{ route('planning-comparison.index') }}" class="btn btn-secondary comparison-btn">
                            <span class="btn-icon">📊</span>
                            <span class="btn-text">Comparer</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Alertes globales -->
    <div class="dashboard-alerts">
        @if($overdueShootings->count() > 0 || $overduePublications->count() > 0)
            <div class="alert-component alert-danger">
                <div class="alert-icon-wrapper">
                    <span class="alert-icon">🚨</span>
                </div>
                <div class="alert-content-wrapper">
                    <div class="alert-header">
                        <strong class="alert-title">Événements en retard</strong>
                        <button type="button" class="alert-close" onclick="this.closest('.alert-component').remove()" aria-label="Fermer">×</button>
                    </div>
                    <div class="alert-items">
                        @foreach($overdueShootings as $shooting)
                            <div class="alert-item">
                                <span class="item-icon">📹</span>
                                <span class="item-text">Tournage du {{ $shooting->date->format('d/m/Y H:i') }} - {{ $shooting->client->nom_entreprise }}</span>
                                <a href="{{ route('shootings.show', $shooting) }}" class="item-link">Voir</a>
                            </div>
                        @endforeach
                        @foreach($overduePublications as $publication)
                            <div class="alert-item">
                                <span class="item-icon">📢</span>
                                <span class="item-text">Publication du {{ $publication->date->format('d/m/Y H:i') }} - {{ $publication->client->nom_entreprise }}</span>
                                <a href="{{ route('publications.show', $publication) }}" class="item-link">Voir</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        @if($upcomingShootings->count() > 0 || $upcomingPublications->count() > 0)
            <div class="alert-component alert-warning">
                <div class="alert-icon-wrapper">
                    <span class="alert-icon">⏰</span>
                </div>
                <div class="alert-content-wrapper">
                    <div class="alert-header">
                        <strong class="alert-title">Événements à venir (dans 3 jours)</strong>
                        <button type="button" class="alert-close" onclick="this.closest('.alert-component').remove()" aria-label="Fermer">×</button>
                    </div>
                    <div class="alert-items">
                        @foreach($upcomingShootings as $shooting)
                            <div class="alert-item">
                                <span class="item-icon">📹</span>
                                <span class="item-text">Tournage du {{ $shooting->date->format('d/m/Y H:i') }} - {{ $shooting->client->nom_entreprise }}</span>
                                <a href="{{ route('shootings.show', $shooting) }}" class="item-link">Voir</a>
                            </div>
                        @endforeach
                        @foreach($upcomingPublications as $publication)
                            <div class="alert-item">
                                <span class="item-icon">📢</span>
                                <span class="item-text">Publication du {{ $publication->date->format('d/m/Y H:i') }} - {{ $publication->client->nom_entreprise }}</span>
                                <a href="{{ route('publications.show', $publication) }}" class="item-link">Voir</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <style>
        .dashboard-alerts {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .alert-component {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 8px;
            border: 1px solid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }
        
        .alert-component:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        
        .alert-component.alert-danger {
            background: #fff5f5;
            border-color: #dc3545;
        }
        
        .alert-component.alert-warning {
            background: #fffbf0;
            border-color: #ffc107;
        }
        
        .alert-icon-wrapper {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }
        
        .alert-component.alert-danger .alert-icon-wrapper {
            background: #dc3545;
        }
        
        .alert-component.alert-warning .alert-icon-wrapper {
            background: #ffc107;
        }
        
        .alert-icon {
            font-size: 1.125rem;
            line-height: 1;
        }
        
        .alert-content-wrapper {
            flex: 1;
            min-width: 0;
        }
        
        .alert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.625rem;
        }
        
        .alert-title {
            font-size: 0.875rem;
            font-weight: 700;
            margin: 0;
        }
        
        .alert-component.alert-danger .alert-title {
            color: #dc3545;
        }
        
        .alert-component.alert-warning .alert-title {
            color: #856404;
        }
        
        .alert-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
            opacity: 0.6;
        }
        
        .alert-component.alert-danger .alert-close {
            color: #dc3545;
        }
        
        .alert-component.alert-warning .alert-close {
            color: #856404;
        }
        
        .alert-close:hover {
            opacity: 1;
            background: rgba(0,0,0,0.05);
        }
        
        .alert-items {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .alert-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            background: rgba(255,255,255,0.8);
            border-radius: 6px;
            border-left: 3px solid;
            font-size: 0.8125rem;
            transition: all 0.2s;
        }
        
        .alert-component.alert-danger .alert-item {
            border-left-color: #dc3545;
        }
        
        .alert-component.alert-warning .alert-item {
            border-left-color: #ffc107;
        }
        
        .alert-item:hover {
            background: rgba(255,255,255,1);
            transform: translateX(2px);
        }
        
        .item-icon {
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .item-text {
            flex: 1;
            color: #303030;
            line-height: 1.4;
        }
        
        .item-link {
            padding: 0.375rem 0.75rem;
            background: #FF6A3A;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .item-link:hover {
            background: #e55a2a;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .alert-component {
                padding: 0.75rem;
                gap: 0.625rem;
            }
            
            .alert-icon-wrapper {
                width: 28px;
                height: 28px;
            }
            
            .alert-icon {
                font-size: 1rem;
            }
            
            .alert-item {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .item-link {
                width: 100%;
                text-align: center;
            }
        }
        
        /* Dashboard Header Modern */
        .dashboard-header {
            display: grid;
            grid-template-columns: minmax(280px, 1fr) minmax(360px, 580px);
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            position: relative;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(255, 106, 58, 0.05) 0%, rgba(255, 106, 58, 0.02) 100%);
            border-radius: 20px;
            border: 1px solid rgba(255, 106, 58, 0.1);
        }

        .dashboard-title-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            padding: 0.4rem 0.85rem;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(255, 106, 58, 0.25);
        }

        .dashboard-badge-icon {
            font-size: 1rem;
            line-height: 1;
        }

        .dashboard-kicker {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 800;
            color: #ffffff;
        }

        .dashboard-title h2 {
            color: #303030;
            margin: 0 0 0.75rem;
            font-size: 2.1rem;
            font-weight: 800;
            line-height: 1.2;
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }

        .dashboard-title-main {
            background: linear-gradient(135deg, #303030 0%, #1a1a1a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dashboard-title-accent {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 50%;
            box-shadow: 0 0 12px rgba(255, 106, 58, 0.6);
        }

        .dashboard-subtitle {
            margin: 0;
            color: #6c757d;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .dashboard-subtitle-icon {
            font-size: 1.1rem;
            opacity: 0.7;
        }

        .dashboard-actions {
            display: flex;
            justify-content: flex-end;
        }

        .report-card {
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #eef0f2;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        }

        .report-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .report-field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .report-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 700;
            color: #495057;
        }

        .report-actions {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .report-btn,
        .comparison-btn {
            justify-content: center;
            width: 100%;
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

        .report-period-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem;
            border: 1px solid #e5e5e5;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            width: 100%;
            justify-content: space-between;
        }

        .period-option {
            position: relative;
            cursor: pointer;
            flex: 1;
            min-width: 0;
        }

        .period-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .period-option span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-width: 0;
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #303030;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .period-option input:checked + span {
            background: #FF6A3A;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(255,106,58,0.25);
        }

        .period-option span:hover {
            border-color: #FF6A3A;
        }

        .period-option input:focus-visible + span {
            outline: 2px solid rgba(255,106,58,0.4);
            outline-offset: 2px;
        }
        
        .report-btn,
        .comparison-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            padding: 0.5rem 1rem;
        }
        
        .btn-icon {
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .btn-text {
            flex-shrink: 0;
        }
        
        /* Calendar Header Responsive */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .calendar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .calendar-nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .calendar-nav .btn {
            padding: 0.5rem 1rem;
            white-space: nowrap;
        }
        
        /* Calendar Form Responsive */
        .calendar-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .calendar-form .form-group {
            flex: 1;
            min-width: 120px;
        }
        
        /* Calendar Table Responsive */
        .calendar-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        /* Stats Grid Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Events Lists Responsive */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .events-grid .card table {
            width: 100%;
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .events-grid .card table thead,
        .events-grid .card table tbody,
        .events-grid .card table tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        /* Calendar Legend Responsive */
        .calendar-legend {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .calendar-legend h4 {
            margin-bottom: 0.5rem;
            font-size: 1rem;
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
            font-size: 0.9rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .dashboard-title {
                padding: 1.25rem;
                text-align: center;
            }

            .dashboard-title-badge {
                justify-content: center;
            }
            
            .dashboard-title h2 {
                font-size: 1.75rem;
                justify-content: center;
            }

            .dashboard-subtitle {
                justify-content: center;
            }
            
            .dashboard-actions {
                width: 100%;
                justify-content: center;
            }

            .report-card {
                padding: 0.9rem;
            }

            .report-form {
                grid-template-columns: 1fr;
            }

            .report-actions {
                grid-template-columns: 1fr;
            }

            .report-period-toggle {
                width: 100%;
                justify-content: space-between;
                padding: 0.35rem;
            }

            .period-option span {
                flex: 1;
                min-width: auto;
            }
            
            .report-btn,
            .comparison-btn {
                width: 100%;
                justify-content: center;
            }
            
            .calendar-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .calendar-header h3 {
                font-size: 1.25rem;
                text-align: center;
            }
            
            .calendar-nav {
                justify-content: center;
                width: 100%;
            }
            
            .calendar-form {
                flex-direction: column;
            }
            
            .calendar-form .form-group {
                width: 100%;
            }
            
            .calendar-form button {
                width: 100%;
            }
            
            .legend-items {
                gap: 1rem;
            }
            
            .legend-item {
                font-size: 0.85rem;
            }
            
            .legend-color {
                width: 18px;
                height: 18px;
            }
            
            .events-grid .card table {
                min-width: 100%;
            }
            
            .events-grid .card table th,
            .events-grid .card table td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }
            
            .events-grid .card table th:last-child,
            .events-grid .card table td:last-child {
                min-width: 80px;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-header h2 {
                font-size: 1.25rem;
            }
            
            .calendar-header h3 {
                font-size: 1.1rem;
            }
            
            .report-btn .btn-text,
            .comparison-btn .btn-text {
                font-size: 0.85rem;
            }
            
            .calendar-nav .btn {
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
            }
            
            .legend-items {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .events-grid .card table th,
            .events-grid .card table td {
                padding: 0.4rem;
                font-size: 0.8rem;
            }
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
        
        .calendar-nav-btn {
            padding: 0.5rem 1rem;
            white-space: nowrap;
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
        }
    </style>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Clients</h3>
            <div class="value">{{ $stats['clients_count'] }}</div>
        </div>
        
        <div class="stat-card">
            <h3>Tournages ce mois</h3>
            <div class="value" id="shootings-count">{{ $stats['shootings_this_month'] }}</div>
        </div>
        
        <div class="stat-card">
            <h3>Publications ce mois</h3>
            <div class="value" id="publications-count">{{ $stats['publications_this_month'] }}</div>
        </div>
    </div>
    
    <!-- Calendrier combiné -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="calendar-header">
            <h3 id="calendarTitle">
                @php
                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                @endphp
                Planning global - {{ $months[$month] }} {{ $year }}
            </h3>
            <div class="calendar-nav">
                <button type="button" onclick="navigateMonth(-1)" class="btn btn-secondary calendar-nav-btn">←</button>
                <button type="button" onclick="navigateMonth(1)" class="btn btn-secondary calendar-nav-btn">→</button>
                <a href="{{ route('dashboard.export-calendar', ['month' => $month, 'year' => $year]) }}" id="exportCalendarLink" class="btn btn-primary">
                    <span class="btn-icon">📊</span>
                    <span class="btn-text">Exporter</span>
                </a>
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
            </div>
        </form>
        
        <div class="calendar-wrapper" id="calendarWrapper">
            <div id="calendarLoading" style="display: none; text-align: center; padding: 2rem;">
                <p>Chargement...</p>
            </div>
            <div id="calendarContent">
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
                                    @if(!$isTeamReadOnly)
                                        <button class="calendar-day-add-btn" onclick="event.stopPropagation(); openDateModal('{{ $day['date']->format('Y-m-d') }}');" title="Gérer les événements" style="margin-left: auto; background: #FF6A3A; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s;">+</button>
                                    @endif
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
                                             title="Tournage - {{ $shooting->client->nom_entreprise }} - {{ $shooting->date->format('d/m/Y H:i') }} - {{ $shooting->status === 'completed' ? 'Complété' : ($shooting->isOverdue() ? 'En retard' : ($shooting->isUpcoming() ? 'Approche' : 'En attente')) }}">
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
                                             style="background-color: {{ $bgColor }}; color: {{ $textColor }}; padding: 0.25rem 0.5rem; margin-bottom: 0.25rem; border-radius: 3px; font-size: 0.7rem; cursor: pointer; border-left: 3px solid {{ $borderColor }};" 
                                             onclick="event.stopPropagation(); window.location.href='{{ route('publications.show', $publication) }}'"
                                             title="Publication - {{ $publication->client->nom_entreprise }} - {{ $publication->date->format('d/m/Y H:i') }} - {{ $publication->contentIdea->titre }} - {{ $publication->status === 'completed' ? 'Complétée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'Approche' : 'En attente')) }}">
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
            </div>
        </div>
        
        <div class="calendar-legend">
            <h4>Légende :</h4>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FF6A3A; border-left: 3px solid #e55a2a;"></div>
                    <span>📹 Tournage</span>
                </div>
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
                    <span>✅ Complété</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #6c757d; border-left: 3px solid #5a6268;"></div>
                    <span>❌ Échec/Annulé</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Listes des prochains événements -->
    <div class="events-grid">
        <div class="card">
            <div class="card-header">Prochains tournages</div>
            @if($stats['upcoming_shootings']->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['upcoming_shootings'] as $shooting)
                            <tr>
                                <td>{{ $shooting->date->format('d/m/Y') }}</td>
                                <td>{{ $shooting->client->nom_entreprise }}</td>
                                <td>
                                    <a href="{{ route('shootings.show', $shooting) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>Aucun tournage à venir</p>
                </div>
            @endif
        </div>
        
        <div class="card">
            <div class="card-header">Prochaines publications</div>
            @if($stats['upcoming_publications']->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Idée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['upcoming_publications'] as $publication)
                            <tr>
                                <td>{{ $publication->date->format('d/m/Y H:i') }}</td>
                                <td>{{ $publication->client->nom_entreprise }}</td>
                                <td>{{ $publication->contentIdea->titre }}</td>
                                <td>
                                    <a href="{{ route('publications.show', $publication) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>Aucune publication à venir</p>
                </div>
            @endif
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
                    <!-- Tournages -->
                    <div class="modal-section">
                        <h4>📹 Tournages</h4>
                        <div id="shootingsList" class="events-list"></div>
                        @if(!$isTeamReadOnly)
                            <a href="{{ route('shootings.create') }}" id="addShootingLink" class="btn btn-primary modal-add-btn" style="margin-top: 0.5rem; display: inline-block; text-decoration: none;">
                                + Ajouter un tournage
                            </a>
                        @endif
                    </div>
                    
                    <!-- Publications -->
                    <div class="modal-section">
                        <h4>📢 Publications</h4>
                        <div id="publicationsList" class="events-list"></div>
                        @if(!$isTeamReadOnly)
                            <a href="{{ route('publications.create') }}" id="addPublicationLink" class="btn btn-primary modal-add-btn" style="margin-top: 0.5rem; display: inline-block; text-decoration: none;">
                                + Ajouter une publication
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Calendar Day Cell Styles */
        .calendar-day-cell {
            transition: background-color 0.2s;
        }
        
        .calendar-day-cell:hover {
            background-color: #f0f0f0 !important;
        }
        
        .calendar-day-add-btn {
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .calendar-day-cell:hover .calendar-day-add-btn {
            opacity: 1;
        }
        
        .calendar-day-add-btn:hover {
            opacity: 1 !important;
            background: #e55a2a !important;
            transform: scale(1.1);
        }
        
        /* Date Modal Styles */
        .date-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .date-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 10000;
        }
        
        .date-modal-content {
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
        
        .date-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .date-modal-header h3 {
            margin: 0;
            color: #303030;
            font-size: 1.5rem;
        }
        
        .date-modal-close {
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
        
        .date-modal-close:hover {
            background: #f0f0f0;
            color: #303030;
        }
        
        .date-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .modal-section {
            margin-bottom: 2rem;
        }
        
        .modal-section:last-child {
            margin-bottom: 0;
        }
        
        .modal-section h4 {
            margin: 0 0 1rem 0;
            color: #303030;
            font-size: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .event-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }
        
        .event-item:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .event-item-info {
            flex: 1;
        }
        
        .event-item-title {
            font-weight: 600;
            color: #303030;
            margin-bottom: 0.25rem;
        }
        
        .event-item-details {
            font-size: 0.875rem;
            color: #666;
        }
        
        .event-item-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .event-item-actions .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .event-status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-upcoming {
            background: #fff3cd;
            color: #856404;
        }
        
        .modal-loading {
            color: #666;
        }
        
        .empty-events {
            text-align: center;
            padding: 2rem;
            color: #999;
            font-style: italic;
        }
        
        .modal-add-btn {
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 10002 !important;
            position: relative;
            text-decoration: none !important;
        }
        
        .modal-add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            background-color: #e55a2a !important;
        }
        
        .modal-add-btn:active {
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .date-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .date-modal-header {
                padding: 1rem;
            }
            
            .date-modal-header h3 {
                font-size: 1.25rem;
            }
            
            .date-modal-body {
                padding: 1rem;
            }
            
            .event-item {
                flex-direction: column;
                align-items: stretch;
            }
            
            .event-item-actions {
                margin-top: 0.75rem;
                justify-content: stretch;
            }
            
            .event-item-actions .btn {
                flex: 1;
            }
        }
    </style>
    
    <script>
        const isTeamReadOnly = @json($isTeamReadOnly);

        function openDateModal(date) {
            const modal = document.getElementById('dateModal');
            const modalDateText = document.getElementById('modalDateText');
            const modalDateTitle = document.getElementById('modalDateTitle');
            const modalLoading = document.getElementById('modalLoading');
            const modalContent = document.getElementById('modalContent');
            
            // Formater la date en français
            const dateObj = new Date(date + 'T00:00:00');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateFormatted = dateObj.toLocaleDateString('fr-FR', options);
            modalDateText.textContent = dateFormatted;
            
            // Afficher la modal
            modal.style.display = 'flex';
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            
            // Charger les événements
            fetch(`/api/events-by-date?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    modalLoading.style.display = 'none';
                    modalContent.style.display = 'block';
                    
                    // Afficher les tournages
                    displayShootings(data.shootings, date);
                    
                    // Afficher les publications
                    displayPublications(data.publications, date);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    modalLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement des événements</p>';
                });
        }
        
        function closeDateModal() {
            const modal = document.getElementById('dateModal');
            modal.style.display = 'none';
        }
        
        function displayShootings(shootings, date) {
            const container = document.getElementById('shootingsList');
            const addLink = document.getElementById('addShootingLink');
            if (addLink) {
                // Récupérer le mois et l'année actuels du planning
                const currentMonth = parseInt(document.getElementById('month').value);
                const currentYear = parseInt(document.getElementById('year').value);
                // Construire l'URL avec le paramètre date, month et year
                const baseUrl = addLink.getAttribute('href').split('?')[0];
                const shootingCreateUrl = `${baseUrl}?date=${encodeURIComponent(date)}&return_month=${currentMonth}&return_year=${currentYear}&return_to_dashboard=1`;
                addLink.href = shootingCreateUrl;
                addLink.style.pointerEvents = 'auto';
                addLink.style.cursor = 'pointer';
                // S'assurer que le lien fonctionne même si JavaScript bloque
                addLink.setAttribute('target', '_self');
            }
            
            if (shootings.length === 0) {
                container.innerHTML = '<div class="empty-events">Aucun tournage prévu</div>';
                return;
            }
            
            container.innerHTML = shootings.map(shooting => {
                let statusClass = 'status-pending';
                let statusText = 'En attente';
                
                if (shooting.status === 'completed') {
                    statusClass = 'status-completed';
                    statusText = 'Complété';
                } else if (shooting.status === 'cancelled') {
                    statusClass = 'status-cancelled';
                    statusText = 'Annulé';
                } else if (shooting.is_overdue) {
                    statusClass = 'status-overdue';
                    statusText = 'En retard';
                } else if (shooting.is_upcoming) {
                    statusClass = 'status-upcoming';
                    statusText = 'À venir';
                }
                
                const contentIdeasText = shooting.content_ideas && shooting.content_ideas.length > 0 
                    ? shooting.content_ideas.map(ci => ci.titre || ci).join(', ')
                    : 'Aucune idée de contenu';
                
                const actionButtons = isTeamReadOnly
                    ? `<a href="${shooting.url}" class="btn btn-primary">Voir</a>`
                    : `<a href="${shooting.url}" class="btn btn-primary">Voir</a>
                       <a href="${shooting.edit_url}" class="btn btn-secondary">Modifier</a>
                       <button onclick="deleteEvent('shooting', ${shooting.id}, '${shooting.client_name}')" class="btn btn-danger">Supprimer</button>`;

                return `
                    <div class="event-item">
                        <div class="event-item-info">
                            <div class="event-item-title">
                                📹 ${shooting.client_name}
                                <span class="event-status-badge ${statusClass}">${statusText}</span>
                            </div>
                            <div class="event-item-details">
                                ${contentIdeasText}
                                ${shooting.description ? ' • ' + shooting.description.substring(0, 50) + (shooting.description.length > 50 ? '...' : '') : ''}
                            </div>
                        </div>
                        <div class="event-item-actions">
                            ${actionButtons}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function displayPublications(publications, date) {
            const container = document.getElementById('publicationsList');
            const addLink = document.getElementById('addPublicationLink');
            if (addLink) {
                // Récupérer le mois et l'année actuels du planning
                const currentMonth = parseInt(document.getElementById('month').value);
                const currentYear = parseInt(document.getElementById('year').value);
                // Construire l'URL avec le paramètre date, month et year
                const baseUrl = addLink.getAttribute('href').split('?')[0];
                const publicationCreateUrl = `${baseUrl}?date=${encodeURIComponent(date)}&return_month=${currentMonth}&return_year=${currentYear}&return_to_dashboard=1`;
                addLink.href = publicationCreateUrl;
                addLink.style.pointerEvents = 'auto';
                addLink.style.cursor = 'pointer';
                // S'assurer que le lien fonctionne même si JavaScript bloque
                addLink.setAttribute('target', '_self');
            }
            
            if (publications.length === 0) {
                container.innerHTML = '<div class="empty-events">Aucune publication prévue</div>';
                return;
            }
            
            container.innerHTML = publications.map(publication => {
                let statusClass = 'status-pending';
                let statusText = 'En attente';
                
                if (publication.status === 'completed') {
                    statusClass = 'status-completed';
                    statusText = 'Complétée';
                } else if (publication.status === 'not_realized') {
                    statusClass = 'status-cancelled';
                    statusText = 'Non réalisée';
                } else if (publication.status === 'cancelled') {
                    statusClass = 'status-cancelled';
                    statusText = 'Annulée';
                } else if (publication.status === 'rescheduled') {
                    statusClass = 'status-info';
                    statusText = 'Reprogrammée';
                } else if (publication.is_overdue) {
                    statusClass = 'status-overdue';
                    statusText = 'En retard';
                } else if (publication.is_upcoming) {
                    statusClass = 'status-upcoming';
                    statusText = 'À venir';
                }
                
                const actionButtons = isTeamReadOnly
                    ? `<a href="${publication.url}" class="btn btn-primary">Voir</a>`
                    : `<a href="${publication.url}" class="btn btn-primary">Voir</a>
                       <a href="${publication.edit_url}" class="btn btn-secondary">Modifier</a>
                       <button onclick="deleteEvent('publication', ${publication.id}, '${publication.client_name}')" class="btn btn-danger">Supprimer</button>`;

                return `
                    <div class="event-item">
                        <div class="event-item-info">
                            <div class="event-item-title">
                                📢 ${publication.client_name}
                                <span class="event-status-badge ${statusClass}">${statusText}</span>
                            </div>
                            <div class="event-item-details">
                                ${publication.content_idea_titre || 'Aucune idée de contenu'}
                                ${publication.description ? ' • ' + publication.description.substring(0, 50) + (publication.description.length > 50 ? '...' : '') : ''}
                            </div>
                        </div>
                        <div class="event-item-actions">
                            ${actionButtons}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function deleteEvent(type, id, name) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer ce ${type === 'shooting' ? 'tournage' : 'publication'} pour ${name} ?`)) {
                return;
            }
            
            const url = type === 'shooting' 
                ? `/shootings/${id}` 
                : `/publications/${id}`;
            
            // Récupérer le mois et l'année actuels du calendrier
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const currentMonth = monthSelect ? parseInt(monthSelect.value) : new Date().getMonth() + 1;
            const currentYear = yearSelect ? parseInt(yearSelect.value) : new Date().getFullYear();
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);
            
            // Ajouter les paramètres de retour vers le dashboard
            const monthInput = document.createElement('input');
            monthInput.type = 'hidden';
            monthInput.name = 'return_month';
            monthInput.value = currentMonth;
            form.appendChild(monthInput);
            
            const yearInput = document.createElement('input');
            yearInput.type = 'hidden';
            yearInput.name = 'return_year';
            yearInput.value = currentYear;
            form.appendChild(yearInput);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function initClientCombobox() {
            const comboboxes = document.querySelectorAll('[data-client-combobox]');

            comboboxes.forEach((combobox) => {
                const trigger = combobox.querySelector('.client-combobox-trigger');
                const panel = combobox.querySelector('.client-combobox-panel');
                const searchInput = combobox.querySelector('.client-combobox-search input');
                const listButtons = combobox.querySelectorAll('.client-combobox-list button');
                const hiddenInput = combobox.querySelector('input[type="hidden"][name="client_id"]');
                const label = combobox.querySelector('.client-combobox-text');

                if (!trigger || !panel || !searchInput || !hiddenInput || !label) {
                    return;
                }

                const closePanel = () => {
                    if (panel.style.display === 'none') {
                        return;
                    }
                    trigger.setAttribute('aria-expanded', 'false');
                    gsap.to(panel, {
                        opacity: 0,
                        y: -6,
                        duration: 0.2,
                        ease: 'power2.inOut',
                        onComplete: () => {
                            panel.style.display = 'none';
                        }
                    });
                };

                const openPanel = () => {
                    panel.style.display = 'block';
                    trigger.setAttribute('aria-expanded', 'true');
                    gsap.fromTo(panel, { opacity: 0, y: -6 }, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out' });
                    searchInput.focus();
                };

                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    const isOpen = panel.style.display === 'block';
                    if (isOpen) {
                        closePanel();
                    } else {
                        openPanel();
                    }
                });

                listButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const value = button.getAttribute('data-client-value');
                        const text = button.getAttribute('data-client-label') || button.textContent.trim();
                        hiddenInput.value = value || 'all';
                        label.textContent = text;
                        listButtons.forEach((btn) => btn.classList.remove('is-active'));
                        button.classList.add('is-active');
                        closePanel();
                    });
                });

                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.trim().toLowerCase();
                    listButtons.forEach((button) => {
                        const text = (button.getAttribute('data-client-label') || button.textContent || '').toLowerCase();
                        const item = button.closest('li');
                        if (!item) {
                            return;
                        }
                        item.style.display = text.includes(query) ? '' : 'none';
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!combobox.contains(event.target)) {
                        closePanel();
                    }
                });
            });
        }

        // Fonction pour initialiser les événements de clic sur les cellules du calendrier
        function initCalendarCellEvents() {
            document.querySelectorAll('.calendar-day-cell').forEach(cell => {
                // Supprimer les anciens événements si ils existent
                const newCell = cell.cloneNode(true);
                cell.parentNode.replaceChild(newCell, cell);
                
                // Ajouter le nouvel événement
                newCell.addEventListener('click', function(e) {
                    // Ne pas ouvrir si on clique sur un événement ou le bouton +
                    if (e.target.closest('.calendar-event') || e.target.closest('.calendar-day-add-btn')) {
                        return;
                    }
                    
                    const date = this.getAttribute('data-date');
                    if (date) {
                        openDateModal(date);
                    }
                });
            });
        }
        
        // Fonction pour synchroniser les valeurs initiales
        function syncInitialValues() {
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const calendarTitle = document.getElementById('calendarTitle');
            
            if (!monthSelect || !yearSelect || !calendarTitle) {
                return;
            }
            
            // Récupérer les valeurs depuis les attributs data ou les valeurs actuelles des selects
            let initialMonth = parseInt(monthSelect.getAttribute('data-initial-month'));
            let initialYear = parseInt(yearSelect.getAttribute('data-initial-year'));
            
            // Si les attributs data ne sont pas définis, utiliser les valeurs des selects
            if (isNaN(initialMonth)) {
                initialMonth = parseInt(monthSelect.value);
            }
            if (isNaN(initialYear)) {
                initialYear = parseInt(yearSelect.value);
            }
            
            // Valider les valeurs
            if (isNaN(initialMonth) || initialMonth < 1 || initialMonth > 12) {
                initialMonth = new Date().getMonth() + 1;
            }
            if (isNaN(initialYear) || initialYear < 2020 || initialYear > 2030) {
                initialYear = new Date().getFullYear();
            }
            
            // Forcer la synchronisation avec des valeurs numériques
            monthSelect.value = initialMonth.toString();
            yearSelect.value = initialYear.toString();
            monthSelect.setAttribute('data-initial-month', initialMonth.toString());
            yearSelect.setAttribute('data-initial-year', initialYear.toString());
            
            // Mettre à jour le titre pour être sûr
            const months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            calendarTitle.textContent = `Planning global - ${months[initialMonth]} ${initialYear}`;
        }
        
        // Ouvrir la modal au clic sur une cellule de date
        document.addEventListener('DOMContentLoaded', function() {
            initClientCombobox();
            initCalendarCellEvents();
            
            // Synchroniser les valeurs initiales
            syncInitialValues();
            
            // Ajouter les événements change sur les selects pour mettre à jour automatiquement
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
            
            // S'assurer que les liens d'ajout fonctionnent
            document.addEventListener('click', function(e) {
                if (e.target.closest('#addShootingLink') || e.target.closest('#addPublicationLink')) {
                    const link = e.target.closest('#addShootingLink') || e.target.closest('#addPublicationLink');
                    if (link && link.href && link.href !== '#' && link.href !== '{{ route('shootings.create') }}' && link.href !== '{{ route('publications.create') }}') {
                        // Le lien a déjà été mis à jour avec la date, on le laisse fonctionner normalement
                        return true;
                    }
                }
            });
        });
        
        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDateModal();
            }
        });
        
        // Fonction pour naviguer entre les mois sans recharger la page
        function navigateMonth(direction) {
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            
            let currentMonth = parseInt(monthSelect.value);
            let currentYear = parseInt(yearSelect.value);
            
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
            
            updateCalendar(newMonth, newYear);
        }
        
        // Variable pour éviter les appels multiples simultanés
        let isUpdatingCalendar = false;
        let pendingUpdate = null;

        // Fonction pour mettre à jour le calendrier via AJAX
        function updateCalendar(month, year) {
            // Si une mise à jour est en cours, on stocke la demande pour l'exécuter après
            if (isUpdatingCalendar) {
                pendingUpdate = { month, year };
                return;
            }

            // Valider les valeurs
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

            isUpdatingCalendar = true;

            const calendarWrapper = document.getElementById('calendarWrapper');
            const calendarContent = document.getElementById('calendarContent');
            const calendarLoading = document.getElementById('calendarLoading');
            const calendarTitle = document.getElementById('calendarTitle');
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const exportLink = document.getElementById('exportCalendarLink');
            
            // Tableau des mois pour synchronisation immédiate
            const months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            
            // Mise à jour IMMÉDIATE du titre, des selects et du lien d'export (sans attendre AJAX)
            // Cela garantit que l'interface est synchronisée instantanément
            if (calendarTitle) {
                calendarTitle.textContent = `Planning global - ${months[month]} ${year}`;
            }
            
            // Forcer la synchronisation des selects avec des valeurs numériques
            // On force toujours la valeur pour s'assurer de la cohérence
            if (monthSelect) {
                monthSelect.value = month.toString();
                // Mettre à jour aussi l'attribut data pour la cohérence
                monthSelect.setAttribute('data-initial-month', month.toString());
            }
            
            if (yearSelect) {
                yearSelect.value = year.toString();
                // Mettre à jour aussi l'attribut data pour la cohérence
                yearSelect.setAttribute('data-initial-year', year.toString());
            }
            
            if (exportLink) {
                const baseUrl = exportLink.getAttribute('href').split('?')[0];
                exportLink.setAttribute('href', `${baseUrl}?month=${month}&year=${year}`);
            }
            
            // Afficher le loading
            if (calendarContent) {
                calendarContent.style.display = 'none';
            }
            if (calendarLoading) {
                calendarLoading.style.display = 'block';
            }
            
            // Faire la requête AJAX
            fetch(`/api/admin-calendar?month=${month}&year=${year}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Mettre à jour le contenu du calendrier
                    if (calendarContent && data.html) {
                        calendarContent.innerHTML = data.html;
                    }
                    
                    // Synchronisation finale avec les données du serveur (vérification de cohérence)
                    // Utiliser les données du serveur si disponibles, sinon garder les valeurs passées
                    const finalMonth = data.month ? parseInt(data.month) : month;
                    const finalYear = data.year ? parseInt(data.year) : year;
                    const finalMonthName = data.monthName || months[finalMonth];
                    
                    if (calendarTitle) {
                        calendarTitle.textContent = `Planning global - ${finalMonthName} ${finalYear}`;
                    }
                    
                    // Forcer la synchronisation des selects avec les valeurs finales
                    if (monthSelect) {
                        monthSelect.value = finalMonth.toString();
                        monthSelect.setAttribute('data-initial-month', finalMonth.toString());
                    }
                    if (yearSelect) {
                        yearSelect.value = finalYear.toString();
                        yearSelect.setAttribute('data-initial-year', finalYear.toString());
                    }
                    
                    // Mettre à jour les statistiques si disponibles
                    if (data.stats) {
                        const shootingsCount = document.getElementById('shootings-count');
                        const publicationsCount = document.getElementById('publications-count');
                        if (shootingsCount) {
                            shootingsCount.textContent = data.stats.shootings_this_month;
                        }
                        if (publicationsCount) {
                            publicationsCount.textContent = data.stats.publications_this_month;
                        }
                    }
                    
                    // Réinitialiser les événements de clic sur les cellules
                    initCalendarCellEvents();
                    
                    // Masquer le loading et afficher le contenu
                    if (calendarLoading) {
                        calendarLoading.style.display = 'none';
                    }
                    if (calendarContent) {
                        calendarContent.style.display = 'block';
                    }

                    // Libérer le verrou et traiter une éventuelle mise à jour en attente
                    isUpdatingCalendar = false;
                    if (pendingUpdate) {
                        const nextUpdate = pendingUpdate;
                        pendingUpdate = null;
                        updateCalendar(nextUpdate.month, nextUpdate.year);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    if (calendarLoading) {
                        calendarLoading.innerHTML = '<p style="color: #dc3545;">Erreur lors du chargement du calendrier</p>';
                    }
                    
                    // Libérer le verrou même en cas d'erreur
                    isUpdatingCalendar = false;
                    if (pendingUpdate) {
                        const nextUpdate = pendingUpdate;
                        pendingUpdate = null;
                        updateCalendar(nextUpdate.month, nextUpdate.year);
                    }
                });
        }
    </script>
@endsection

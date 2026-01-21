@extends('layouts.app')

@section('title', 'Tournage du ' . $shooting->date->format('d/m/Y H:i'))

@section('content')
    @php
        $isTeamReadOnly = true; // Toujours en lecture seule pour les team
        $statusConfig = [
            'pending' => ['label' => 'En attente', 'icon' => '⏳', 'color' => '#ffc107', 'bg' => '#fff3cd', 'text' => '#856404'],
            'completed' => ['label' => 'Complété', 'icon' => '✅', 'color' => '#28a745', 'bg' => '#d4edda', 'text' => '#155724'],
            'not_realized' => ['label' => 'Non réalisé', 'icon' => '❌', 'color' => '#dc3545', 'bg' => '#f8d7da', 'text' => '#721c24'],
            'cancelled' => ['label' => 'Annulé', 'icon' => '🚫', 'color' => '#dc3545', 'bg' => '#f8d7da', 'text' => '#721c24'],
            'rescheduled' => ['label' => 'Reprogrammé', 'icon' => '📅', 'color' => '#17a2b8', 'bg' => '#d1ecf1', 'text' => '#0c5460'],
        ];
        $currentStatus = $statusConfig[$shooting->status] ?? $statusConfig['pending'];
    @endphp
    
    <style>
        .shooting-page {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .shooting-hero {
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(255, 106, 58, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .shooting-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .shooting-hero-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .shooting-hero-title {
            flex: 1;
        }
        
        .shooting-hero-title h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 0.5rem;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .shooting-hero-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .shooting-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            font-weight: 600;
        }
        
        .shooting-status-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.25);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            font-weight: 700;
            font-size: 1.1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .shooting-actions-header {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .shooting-action-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .shooting-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .shooting-content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .shooting-main-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
        }
        
        .shooting-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .shooting-sidebar-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
        }
        
        .shooting-sidebar-card h3 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin: 0 0 1rem;
            font-weight: 700;
        }
        
        .shooting-info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .shooting-info-item:hover {
            background: #f0f0f0;
            transform: translateX(4px);
        }
        
        .shooting-info-item:last-child {
            margin-bottom: 0;
        }
        
        .shooting-info-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 12px;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .shooting-info-content {
            flex: 1;
        }
        
        .shooting-info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .shooting-info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #303030;
        }
        
        .shooting-status-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
            margin-bottom: 2rem;
        }
        
        .shooting-status-section h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #303030;
            margin: 0 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .shooting-status-section h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 2px;
        }
        
        .status-buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }
        
        .status-btn-modern {
            position: relative;
            padding: 1rem 1.25rem;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            overflow: hidden;
        }
        
        .status-btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 106, 58, 0.1) 0%, rgba(255, 106, 58, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .status-btn-modern:hover::before {
            opacity: 1;
        }
        
        .status-btn-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #FF6A3A;
        }
        
        .status-btn-modern.active {
            border-color: currentColor;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .status-btn-modern.status-pending.active {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        
        .status-btn-modern.status-completed.active {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        
        .status-btn-modern.status-not-realized.active {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        .status-btn-modern.status-cancelled.active {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        .status-btn-modern.status-rescheduled.active {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #17a2b8;
        }
        
        .status-btn-icon {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .status-btn-text {
            display: block;
        }
        
        .shooting-alert-modern {
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-left: 4px solid;
            animation: slideInDown 0.4s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .shooting-alert-modern.alert-danger {
            background: #fff5f5;
            border-left-color: #dc3545;
        }
        
        .shooting-alert-modern.alert-warning {
            background: #fffbf0;
            border-left-color: #ffc107;
        }
        
        .shooting-alert-modern.alert-success {
            background: #f0fff4;
            border-left-color: #28a745;
        }
        
        .shooting-alert-modern.alert-info {
            background: #f0f9ff;
            border-left-color: #17a2b8;
        }
        
        .shooting-alert-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .shooting-alert-content {
            flex: 1;
        }
        
        .shooting-alert-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        
        .shooting-alert-text {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .content-ideas-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
        }
        
        .content-ideas-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #303030;
            margin: 0 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .content-ideas-card h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 2px;
        }
        
        .content-ideas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        
        .content-idea-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #e9ecef;
            border-radius: 14px;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }
        
        .content-idea-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #FF6A3A;
        }
        
        .content-idea-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #303030;
            margin-bottom: 0.75rem;
        }
        
        .content-idea-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 0.4rem 0.85rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        .empty-content-ideas {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-content-ideas-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .shooting-modal-modern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .shooting-modal-content {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        
        .shooting-modal-header {
            margin-bottom: 1.5rem;
        }
        
        .shooting-modal-header h3 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #303030;
            margin: 0 0 0.5rem;
        }
        
        .shooting-modal-header p {
            color: #6c757d;
            margin: 0;
        }
        
        .shooting-modal-form-group {
            margin-bottom: 1.5rem;
        }
        
        .shooting-modal-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .shooting-modal-form-group textarea,
        .shooting-modal-form-group input[type="date"] {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .shooting-modal-form-group textarea:focus,
        .shooting-modal-form-group input[type="date"]:focus {
            outline: none;
            border-color: #FF6A3A;
            box-shadow: 0 0 0 4px rgba(255, 106, 58, 0.1);
        }
        
        .shooting-modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        
        .shooting-modal-btn {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .shooting-modal-btn-secondary {
            background: #f8f9fa;
            color: #495057;
        }
        
        .shooting-modal-btn-secondary:hover {
            background: #e9ecef;
        }
        
        .shooting-modal-btn-primary {
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 106, 58, 0.3);
        }
        
        .shooting-modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 106, 58, 0.4);
        }
        
        @media (max-width: 968px) {
            .shooting-content-grid {
                grid-template-columns: 1fr;
            }
            
            .shooting-hero-header {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .shooting-hero-title h1 {
                font-size: 2rem;
            }
            
            .status-buttons-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .content-ideas-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <div class="shooting-page" data-gsap="fadeIn">
        <!-- Alertes modernes -->
        @if($shooting->isOverdue())
            <div class="shooting-alert-modern alert-danger" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">🚨</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">En retard</div>
                    <div class="shooting-alert-text">Ce tournage était prévu le {{ $shooting->date->format('d/m/Y H:i') }} et n'a pas encore été complété.</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @elseif($shooting->isUpcoming())
            <div class="shooting-alert-modern alert-warning" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">⏰</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">Approche</div>
                    <div class="shooting-alert-text">Ce tournage est prévu dans {{ now()->diffInDays($shooting->date, false) }} jour(s) ({{ $shooting->date->format('d/m/Y H:i') }}).</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @elseif($shooting->isCompleted())
            <div class="shooting-alert-modern alert-success" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">✅</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">Complété</div>
                    <div class="shooting-alert-text">Ce tournage a été marqué comme complété.</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @elseif($shooting->status === 'not_realized')
            <div class="shooting-alert-modern alert-danger" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">❌</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">Non réalisé</div>
                    <div class="shooting-alert-text">Ce tournage n'a pas pu être réalisé.@if($shooting->status_reason) <br><em style="opacity: 0.8;">{{ $shooting->status_reason }}</em>@endif</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @elseif($shooting->status === 'cancelled')
            <div class="shooting-alert-modern alert-danger" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">🚫</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">Annulé</div>
                    <div class="shooting-alert-text">Ce tournage a été annulé.@if($shooting->status_reason) <br><em style="opacity: 0.8;">{{ $shooting->status_reason }}</em>@endif</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @elseif($shooting->status === 'rescheduled')
            <div class="shooting-alert-modern alert-info" data-gsap="fadeInUp">
                <span class="shooting-alert-icon">📅</span>
                <div class="shooting-alert-content">
                    <div class="shooting-alert-title">Reprogrammé</div>
                    <div class="shooting-alert-text">Ce tournage a été reprogrammé.@if($shooting->status_reason) <br><em style="opacity: 0.8;">{{ $shooting->status_reason }}</em>@endif</div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6; color: inherit;">×</button>
            </div>
        @endif
        
        <!-- Hero Section -->
        <div class="shooting-hero" data-gsap="fadeInUp">
            <div class="shooting-hero-header">
                <div class="shooting-hero-title">
                    <h1>Tournage</h1>
                    <div class="shooting-hero-meta">
                        <div class="shooting-meta-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>{{ $shooting->date->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="shooting-meta-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>{{ $shooting->client->nom_entreprise }}</span>
                        </div>
                        <div class="shooting-status-badge-large">
                            <span>{{ $currentStatus['icon'] }}</span>
                            <span>{{ $currentStatus['label'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="shooting-actions-header">
                    @if(!$isTeamReadOnly)
                        <a href="{{ route('shootings.edit', $shooting) }}" class="shooting-action-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Modifier
                        </a>
                    @endif
                    <a href="{{ route('dashboard', ['month' => $shooting->date->month, 'year' => $shooting->date->year]) }}" class="shooting-action-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="shooting-content-grid">
            <!-- Carte principale -->
            <div class="shooting-main-card" data-gsap="fadeInUp">
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #303030; margin: 0 0 2rem; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 4px; height: 24px; background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%); border-radius: 2px;"></span>
                    Informations
                </h2>
                
                <div class="shooting-info-item">
                    <div class="shooting-info-icon">👤</div>
                    <div class="shooting-info-content">
                        <div class="shooting-info-label">Client</div>
                        <div class="shooting-info-value">{{ $shooting->client->nom_entreprise }}</div>
                    </div>
                </div>
                
                <div class="shooting-info-item">
                    <div class="shooting-info-icon">📅</div>
                    <div class="shooting-info-content">
                        <div class="shooting-info-label">Date et heure du tournage</div>
                        <div class="shooting-info-value">{{ $shooting->date->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                
                @if($shooting->description)
                <div class="shooting-info-item">
                    <div class="shooting-info-icon">📝</div>
                    <div class="shooting-info-content">
                        <div class="shooting-info-label">Description</div>
                        <div class="shooting-info-value" style="font-weight: 400; line-height: 1.6;">{{ $shooting->description }}</div>
                    </div>
                </div>
                @endif
                
                @if($shooting->status_reason)
                <div class="shooting-info-item" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                    <div class="shooting-info-icon">ℹ️</div>
                    <div class="shooting-info-content">
                        <div class="shooting-info-label">Raison du statut</div>
                        <div class="shooting-info-value" style="font-weight: 400; line-height: 1.6;">{{ $shooting->status_reason }}</div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="shooting-sidebar">
                @if(!$isTeamReadOnly)
                <div class="shooting-sidebar-card" data-gsap="fadeInUp">
                    <h3>Changer le statut</h3>
                    <div class="status-buttons-grid">
                        @php
                            $month = request()->get('return_month', $shooting->date->month);
                            $year = request()->get('return_year', $shooting->date->year);
                        @endphp
                        <button type="button" onclick="changeStatus('pending', '{{ $month }}', '{{ $year }}')" 
                                class="status-btn-modern status-pending {{ $shooting->status === 'pending' ? 'active' : '' }}">
                            <span class="status-btn-icon">⏳</span>
                            <span class="status-btn-text">En attente</span>
                        </button>
                        <button type="button" onclick="changeStatus('completed', '{{ $month }}', '{{ $year }}')" 
                                class="status-btn-modern status-completed {{ $shooting->status === 'completed' ? 'active' : '' }}">
                            <span class="status-btn-icon">✅</span>
                            <span class="status-btn-text">Complété</span>
                        </button>
                        <button type="button" onclick="changeStatus('not_realized', '{{ $month }}', '{{ $year }}')" 
                                class="status-btn-modern status-not-realized {{ $shooting->status === 'not_realized' ? 'active' : '' }}">
                            <span class="status-btn-icon">❌</span>
                            <span class="status-btn-text">Non réalisé</span>
                        </button>
                        <button type="button" onclick="changeStatus('cancelled', '{{ $month }}', '{{ $year }}')" 
                                class="status-btn-modern status-cancelled {{ $shooting->status === 'cancelled' ? 'active' : '' }}">
                            <span class="status-btn-icon">🚫</span>
                            <span class="status-btn-text">Annulé</span>
                        </button>
                        <button type="button" onclick="changeStatus('rescheduled', '{{ $month }}', '{{ $year }}')" 
                                class="status-btn-modern status-rescheduled {{ $shooting->status === 'rescheduled' ? 'active' : '' }}">
                            <span class="status-btn-icon">📅</span>
                            <span class="status-btn-text">Reprogrammé</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Idées de contenu -->
        <div class="content-ideas-card" data-gsap="fadeInUp">
            <h2>Idées de contenu associées</h2>
            @if($shooting->contentIdeas->count() > 0)
                <div class="content-ideas-grid">
                    @foreach($shooting->contentIdeas as $idea)
                        <div class="content-idea-card">
                            <div class="content-idea-title">{{ $idea->titre }}</div>
                            <div class="content-idea-type-badge">{{ $idea->type }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-content-ideas">
                    <div class="empty-content-ideas-icon">💡</div>
                    <p style="font-size: 1.1rem; font-weight: 600; margin: 0;">Aucune idée de contenu associée</p>
                    <p style="margin-top: 0.5rem; opacity: 0.7;">Ajoutez des idées de contenu lors de la modification du tournage</p>
                </div>
            @endif
        </div>
        
        <!-- Modal moderne pour la description obligatoire -->
        <div id="statusReasonModal" class="shooting-modal-modern">
            <div class="shooting-modal-content">
                <div class="shooting-modal-header">
                    <h3>Description obligatoire</h3>
                    <p>Veuillez expliquer pourquoi ce tournage est <strong id="statusReasonLabel"></strong> :</p>
                </div>
                <form id="statusReasonForm" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="statusReasonStatus">
                    <input type="hidden" name="return_month" id="statusReasonMonth">
                    <input type="hidden" name="return_year" id="statusReasonYear">
                    <div class="shooting-modal-form-group">
                        <label for="status_reason">Description *</label>
                        <textarea id="status_reason" name="status_reason" rows="5" required placeholder="Expliquez la raison de ce changement de statut..."></textarea>
                    </div>
                    <div id="rescheduleDateContainer" style="display: none;">
                        <div class="shooting-modal-form-group">
                            <label for="reschedule_date">Nouvelle date et heure *</label>
                            <input type="datetime-local" id="reschedule_date" name="reschedule_date" required min="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="shooting-modal-actions">
                        <button type="button" onclick="closeStatusReasonModal()" class="shooting-modal-btn shooting-modal-btn-secondary">Annuler</button>
                        <button type="submit" class="shooting-modal-btn shooting-modal-btn-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function changeStatus(status, month, year) {
            const statusesRequiringReason = ['not_realized', 'cancelled', 'rescheduled'];
            
            if (statusesRequiringReason.includes(status)) {
                const modal = document.getElementById('statusReasonModal');
                const form = document.getElementById('statusReasonForm');
                const statusLabel = document.getElementById('statusReasonLabel');
                const statusInput = document.getElementById('statusReasonStatus');
                const monthInput = document.getElementById('statusReasonMonth');
                const yearInput = document.getElementById('statusReasonYear');
                
                const statusLabels = {
                    'not_realized': 'non réalisé',
                    'cancelled': 'annulé',
                    'rescheduled': 'reprogrammé'
                };
                
                statusLabel.textContent = statusLabels[status] || status;
                statusInput.value = status;
                monthInput.value = month;
                yearInput.value = year;
                form.action = '{{ route("shootings.toggle-status", $shooting) }}';
                document.getElementById('status_reason').value = '';
                
                const dateContainer = document.getElementById('rescheduleDateContainer');
                const dateInput = document.getElementById('reschedule_date');
                if (status === 'rescheduled') {
                    dateContainer.style.display = 'block';
                    dateInput.required = true;
                    const defaultDate = new Date();
                    defaultDate.setDate(defaultDate.getDate() + 7);
                    // Format datetime-local: YYYY-MM-DDTHH:mm
                    const year = defaultDate.getFullYear();
                    const month = String(defaultDate.getMonth() + 1).padStart(2, '0');
                    const day = String(defaultDate.getDate()).padStart(2, '0');
                    const hours = String(defaultDate.getHours()).padStart(2, '0');
                    const minutes = String(defaultDate.getMinutes()).padStart(2, '0');
                    dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                    
                    // Animation pour afficher le champ de date
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo(dateContainer, 
                            { opacity: 0, height: 0, marginTop: 0 },
                            { opacity: 1, height: 'auto', marginTop: '1rem', duration: 0.3, ease: 'power2.out' }
                        );
                    }
                } else {
                    dateContainer.style.display = 'none';
                    dateInput.required = false;
                    dateInput.value = '';
                }
                
                modal.style.display = 'flex';
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo('.shooting-modal-content', 
                        { opacity: 0, scale: 0.9, y: -20 },
                        { opacity: 1, scale: 1, y: 0, duration: 0.3, ease: 'power2.out' }
                    );
                }
                document.getElementById('status_reason').focus();
            } else {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("shootings.toggle-status", $shooting) }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = status;
                form.appendChild(statusInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        document.getElementById('statusReasonForm').addEventListener('submit', function(e) {
            const status = document.getElementById('statusReasonStatus').value;
            const dateInput = document.getElementById('reschedule_date');
            
            if (status === 'rescheduled' && (!dateInput.value || dateInput.value.trim() === '')) {
                e.preventDefault();
                alert('Veuillez sélectionner une nouvelle date pour reprogrammer le tournage.');
                dateInput.focus();
                return false;
            }
        });
        
        function closeStatusReasonModal() {
            const modal = document.getElementById('statusReasonModal');
            if (typeof gsap !== 'undefined') {
                gsap.to('.shooting-modal-content', {
                    opacity: 0,
                    scale: 0.9,
                    y: -20,
                    duration: 0.2,
                    ease: 'power2.in',
                    onComplete: () => {
                        modal.style.display = 'none';
                    }
                });
            } else {
                modal.style.display = 'none';
            }
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStatusReasonModal();
            }
        });
        
        // Fermer la modal en cliquant en dehors
        document.getElementById('statusReasonModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStatusReasonModal();
            }
        });
    </script>
@endsection

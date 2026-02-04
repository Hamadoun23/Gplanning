@extends('layouts.client-space')

@section('title', 'Publication du ' . $publication->date->format('d/m/Y H:i'))

@section('content')
    @php
        $statusConfig = [
            'pending' => ['label' => 'En attente', 'icon' => '⏳', 'color' => '#ffc107', 'bg' => '#fff3cd', 'text' => '#856404'],
            'completed' => ['label' => 'Complétée', 'icon' => '✅', 'color' => '#28a745', 'bg' => '#d4edda', 'text' => '#155724'],
            'not_realized' => ['label' => 'Non réalisée', 'icon' => '❌', 'color' => '#dc3545', 'bg' => '#f8d7da', 'text' => '#721c24'],
            'cancelled' => ['label' => 'Annulée', 'icon' => '🚫', 'color' => '#dc3545', 'bg' => '#f8d7da', 'text' => '#721c24'],
            'rescheduled' => ['label' => 'Reprogrammée', 'icon' => '📅', 'color' => '#17a2b8', 'bg' => '#d1ecf1', 'text' => '#0c5460'],
        ];
        $currentStatus = $statusConfig[$publication->status] ?? $statusConfig['pending'];
    @endphp
    
    <style>
        .publication-detail-page {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .publication-hero {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }
        
        .publication-hero h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 1rem;
        }
        
        .publication-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .publication-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .publication-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.25);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 700;
        }
        
        .detail-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .detail-card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #303030;
            margin: 0 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .detail-card h2::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border-radius: 2px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 0.75rem;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border-radius: 12px;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #303030;
        }
        
        .content-idea-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 0.4rem 0.85rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #303030;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .alert-box {
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid;
        }
        
        .alert-box.alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .alert-box.alert-danger {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .alert-box.alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .alert-box.alert-info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        .shooting-link-card {
            background: linear-gradient(135deg, #fff5f2 0%, #fff 100%);
            border: 2px solid #FF6A3A;
            border-radius: 14px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .shooting-link-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 12px;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .shooting-link-content {
            flex: 1;
        }
        
        .shooting-link-title {
            font-weight: 700;
            color: #303030;
            margin-bottom: 0.25rem;
        }
        
        .shooting-link-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
    
    <div class="publication-detail-page">
        <!-- Alertes de statut -->
        @if($publication->isOverdue())
            <div class="alert-box alert-danger">
                <span style="font-size: 1.5rem;">🚨</span>
                <div>
                    <strong>En retard</strong><br>
                    Cette publication était prévue le {{ $publication->date->format('d/m/Y H:i') }} et n'a pas encore été complétée.
                </div>
            </div>
        @elseif($publication->isUpcoming())
            <div class="alert-box alert-warning">
                <span style="font-size: 1.5rem;">⏰</span>
                <div>
                    <strong>Approche</strong><br>
                    Cette publication est prévue dans {{ now()->diffInDays($publication->date, false) }} jour(s).
                </div>
            </div>
        @elseif($publication->isCompleted())
            <div class="alert-box alert-success">
                <span style="font-size: 1.5rem;">✅</span>
                <div>
                    <strong>Complétée</strong><br>
                    Cette publication a été marquée comme complétée.
                </div>
            </div>
        @elseif($publication->status === 'cancelled')
            <div class="alert-box alert-danger">
                <span style="font-size: 1.5rem;">🚫</span>
                <div>
                    <strong>Annulée</strong><br>
                    Cette publication a été annulée.
                    @if($publication->status_reason)<br><em>{{ $publication->status_reason }}</em>@endif
                </div>
            </div>
        @endif
        
        <!-- Hero -->
        <div class="publication-hero">
            <h1>📢 Publication</h1>
            <div class="publication-meta">
                <div class="publication-meta-item">
                    📅 {{ $publication->date->format('d/m/Y H:i') }}
                </div>
                <div class="publication-status-badge">
                    {{ $currentStatus['icon'] }} {{ $currentStatus['label'] }}
                </div>
            </div>
        </div>
        
        <!-- Informations -->
        <div class="detail-card">
            <h2>Informations</h2>
            
            <div class="info-item">
                <div class="info-icon">📅</div>
                <div class="info-content">
                    <div class="info-label">Date et heure de publication</div>
                    <div class="info-value">{{ $publication->date->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon">💡</div>
                <div class="info-content">
                    <div class="info-label">Idée de contenu</div>
                    <div class="info-value">{{ $publication->contentIdea->titre }}</div>
                    <div class="content-idea-badge">{{ $publication->contentIdea->type }}</div>
                </div>
            </div>
            
            @if($publication->description)
            <div class="info-item">
                <div class="info-icon">📝</div>
                <div class="info-content">
                    <div class="info-label">Description</div>
                    <div class="info-value" style="font-weight: 400;">{{ $publication->description }}</div>
                </div>
            </div>
            @endif
            
            @if($publication->status_reason)
            <div class="info-item" style="background: #fff3cd;">
                <div class="info-icon">ℹ️</div>
                <div class="info-content">
                    <div class="info-label">Raison du statut</div>
                    <div class="info-value" style="font-weight: 400;">{{ $publication->status_reason }}</div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Tournage lié -->
        @if($publication->shooting)
        <div class="detail-card">
            <h2>Tournage associé</h2>
            <a href="{{ route('clients.shootings.show', [$client, $publication->shooting]) }}" class="shooting-link-card" style="text-decoration: none;">
                <div class="shooting-link-icon">📹</div>
                <div class="shooting-link-content">
                    <div class="shooting-link-title">Tournage</div>
                    <div class="shooting-link-date">{{ $publication->shooting->date->format('d/m/Y H:i') }}</div>
                </div>
                <span style="color: #FF6A3A; font-weight: 600;">Voir →</span>
            </a>
        </div>
        @endif
        
        <!-- Bouton retour -->
        <a href="{{ route('clients.dashboard', $client) }}" class="back-btn">
            ← Retour au tableau de bord
        </a>
    </div>
@endsection

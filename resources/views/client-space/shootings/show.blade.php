@extends('layouts.client-space')

@section('title', 'Tournage du ' . $shooting->date->format('d/m/Y H:i'))

@section('content')
    @php
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
        .shooting-detail-page {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .shooting-hero {
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(255, 106, 58, 0.3);
        }
        
        .shooting-hero h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 1rem;
        }
        
        .shooting-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .shooting-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .shooting-status-badge {
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
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
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
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
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
        
        .content-ideas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .content-idea-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #e9ecef;
            border-radius: 14px;
            padding: 1.25rem;
        }
        
        .content-idea-title {
            font-size: 1rem;
            font-weight: 700;
            color: #303030;
            margin-bottom: 0.5rem;
        }
        
        .content-idea-type {
            display: inline-flex;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #dc3545;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .back-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            color: white;
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
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
    </style>
    
    <div class="shooting-detail-page">
        <!-- Bouton retour -->
        <a href="{{ route('clients.dashboard', $client) }}" class="back-btn">
            ← Retour au tableau de bord
        </a>
        
        <!-- Alertes de statut -->
        @if($shooting->isOverdue())
            <div class="alert-box alert-danger">
                <span style="font-size: 1.5rem;">🚨</span>
                <div>
                    <strong>En retard</strong><br>
                    Ce tournage était prévu le {{ $shooting->date->format('d/m/Y H:i') }} et n'a pas encore été complété.
                </div>
            </div>
        @elseif($shooting->isUpcoming())
            <div class="alert-box alert-warning">
                <span style="font-size: 1.5rem;">⏰</span>
                <div>
                    <strong>Approche</strong><br>
                    Ce tournage est prévu dans {{ now()->diffInDays($shooting->date, false) }} jour(s).
                </div>
            </div>
        @elseif($shooting->isCompleted())
            <div class="alert-box alert-success">
                <span style="font-size: 1.5rem;">✅</span>
                <div>
                    <strong>Complété</strong><br>
                    Ce tournage a été marqué comme complété.
                </div>
            </div>
        @elseif($shooting->status === 'cancelled')
            <div class="alert-box alert-danger">
                <span style="font-size: 1.5rem;">🚫</span>
                <div>
                    <strong>Annulé</strong><br>
                    Ce tournage a été annulé.
                    @if($shooting->status_reason)<br><em>{{ $shooting->status_reason }}</em>@endif
                </div>
            </div>
        @endif
        
        <!-- Hero -->
        <div class="shooting-hero">
            <h1>📹 Tournage</h1>
            <div class="shooting-meta">
                <div class="shooting-meta-item">
                    📅 {{ $shooting->date->format('d/m/Y H:i') }}
                </div>
                <div class="shooting-status-badge">
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
                    <div class="info-label">Date et heure</div>
                    <div class="info-value">{{ $shooting->date->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            
            @if($shooting->description)
            <div class="info-item">
                <div class="info-icon">📝</div>
                <div class="info-content">
                    <div class="info-label">Description</div>
                    <div class="info-value" style="font-weight: 400;">{{ $shooting->description }}</div>
                </div>
            </div>
            @endif
            
            @if($shooting->status_reason)
            <div class="info-item" style="background: #fff3cd;">
                <div class="info-icon">ℹ️</div>
                <div class="info-content">
                    <div class="info-label">Raison du statut</div>
                    <div class="info-value" style="font-weight: 400;">{{ $shooting->status_reason }}</div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Idées de contenu -->
        <div class="detail-card">
            <h2>Idées de contenu associées</h2>
            @if($shooting->contentIdeas->count() > 0)
                <div class="content-ideas-grid">
                    @foreach($shooting->contentIdeas as $idea)
                        <div class="content-idea-card">
                            <div class="content-idea-title">{{ $idea->titre }}</div>
                            <div class="content-idea-type">{{ $idea->type }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p>Aucune idée de contenu associée</p>
                </div>
            @endif
        </div>
        
    </div>
@endsection

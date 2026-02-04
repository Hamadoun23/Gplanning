@extends('layouts.app')

@section('title', 'Clients')

@section('content')
    <div class="clients-header-responsive">
        <h2>Clients</h2>
    </div>
    
    @if($clients->count() > 0)
        <div class="card clients-table-wrapper">
            <table class="clients-table">
                <thead>
                    <tr>
                        <th>Nom de l'entreprise</th>
                        <th>Tournages</th>
                        <th>Publications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td data-label="Nom de l'entreprise"><strong>{{ $client->nom_entreprise }}</strong></td>
                            <td data-label="Tournages">{{ $client->shootings_count }}</td>
                            <td data-label="Publications">{{ $client->publications_count }}</td>
                            <td data-label="Actions" class="clients-actions-cell">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-primary clients-action-btn" title="Voir les détails">👁️ Voir</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <p>Aucun client enregistré</p>
            </div>
        </div>
    @endif
    
    <style>
        .clients-header-responsive {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .clients-header-responsive h2 {
            color: #303030;
            margin: 0;
        }
        
        .clients-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .clients-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        .clients-table th,
        .clients-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .clients-table thead th {
            background-color: #FF6A3A;
            color: #ffffff;
            font-weight: 600;
        }
        
        .clients-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .clients-actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        
        .clients-action-btn {
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .clients-header-responsive {
                flex-direction: column;
                align-items: stretch;
            }
            
            .clients-table {
                min-width: 100%;
            }
            
            .clients-table thead {
                display: none;
            }
            
            .clients-table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #f0f0f0;
                border-radius: 8px;
                background: #ffffff;
            }
            
            .clients-table tbody td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0.75rem;
                border-bottom: 1px solid #f5f5f5;
                white-space: normal;
            }
            
            .clients-table tbody td:last-child {
                border-bottom: none;
            }
            
            .clients-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #666;
                margin-right: 0.75rem;
                flex-shrink: 0;
            }
            
            .clients-actions-cell {
                flex-direction: column;
                align-items: stretch;
            }
            
            .clients-action-btn {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .clients-table tbody td {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }
            
            .clients-action-btn {
                font-size: 0.8rem;
                padding: 0.3rem 0.5rem;
            }
        }
    </style>
@endsection

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shooting;
use App\Models\Publication;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        
        // Créer la date du premier jour du mois
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer tous les tournages et publications du mois
        $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdeas'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return Carbon::parse($shooting->date)->format('Y-m-d');
            });

        // Liste plate de tous les tournages du mois (pour Finance & RH)
        $shootingsForMonth = $shootings->flatten()->sortBy('date')->values();

        $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return Carbon::parse($publication->date)->format('Y-m-d');
            });

        // Construire le calendrier combiné
        $calendar = $this->buildCombinedCalendar($startDate, $shootings, $publications);
        
        // Calculer les alertes
        $overdueShootings = Shooting::where('status', 'pending')
            ->where('date', '<', $now->toDateString())
            ->with('client')
            ->orderBy('date', 'asc')
            ->get();
            
        $overduePublications = Publication::where('status', 'pending')
            ->where('date', '<', $now->toDateString())
            ->with(['client', 'contentIdea'])
            ->orderBy('date', 'asc')
            ->get();
            
        $upcomingShootings = Shooting::where('status', 'pending')
            ->where('date', '>=', $now->toDateString())
            ->where('date', '<=', $now->copy()->addDays(3)->toDateString())
            ->with('client')
            ->orderBy('date', 'asc')
            ->get();
            
        $upcomingPublications = Publication::where('status', 'pending')
            ->where('date', '>=', $now->toDateString())
            ->where('date', '<=', $now->copy()->addDays(3)->toDateString())
            ->with(['client', 'contentIdea'])
            ->orderBy('date', 'asc')
            ->get();
        
        $stats = [
            'clients_count' => Client::count(),
            'shootings_this_month' => Shooting::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count(),
            'publications_this_month' => Publication::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count(),
            'upcoming_shootings' => Shooting::where('date', '>=', $now->toDateString())
                ->orderBy('date', 'asc')
                ->limit(5)
                ->with('client')
                ->get(),
            'upcoming_publications' => Publication::where('date', '>=', $now->toDateString())
                ->orderBy('date', 'asc')
                ->limit(5)
                ->with(['client', 'contentIdea'])
                ->get(),
        ];

        return view('dashboard', compact(
            'stats',
            'calendar',
            'month',
            'year',
            'startDate',
            'overdueShootings',
            'overduePublications',
            'upcomingShootings',
            'upcomingPublications',
            'shootingsForMonth'
        ));
    }

    /**
     * Récupère les données du calendrier via API (AJAX)
     */
    public function getCalendarData(Request $request)
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        
        // Créer la date du premier jour du mois
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer tous les tournages et publications du mois
        $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdeas'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return Carbon::parse($shooting->date)->format('Y-m-d');
            });

        $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return Carbon::parse($publication->date)->format('Y-m-d');
            });

        // Liste plate de tous les tournages du mois (pour Finance & RH)
        $shootingsForMonth = $shootings->flatten()->sortBy('date')->values();

        // Construire le calendrier combiné
        $calendar = $this->buildCombinedCalendar($startDate, $shootings, $publications);
        
        // Calculer les stats pour le mois sélectionné
        $stats = [
            'shootings_this_month' => Shooting::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count(),
            'publications_this_month' => Publication::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count(),
        ];
        
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        return response()->json([
            'html' => view('dashboard.partials.calendar-table', compact('calendar'))->render(),
            'month' => $month,
            'monthName' => $months[$month],
            'year' => $year,
            'prevMonth' => $startDate->copy()->subMonth()->month,
            'prevYear' => $startDate->copy()->subMonth()->year,
            'nextMonth' => $startDate->copy()->addMonth()->month,
            'nextYear' => $startDate->copy()->addMonth()->year,
            'stats' => $stats,
            'shootings_for_month' => $shootingsForMonth->map(function ($shooting) {
                return [
                    'date' => Carbon::parse($shooting->date)->format('d/m/Y H:i'),
                    'iso_date' => Carbon::parse($shooting->date)->format('Ymd\THis'),
                    'client' => $shooting->client ? $shooting->client->nom_entreprise : 'Client inconnu',
                ];
            })->values(),
        ]);
    }

    /**
     * Construit le calendrier combiné pour un mois donné
     */
    public function buildCombinedCalendar(Carbon $startDate, $shootings, $publications)
    {
        $calendar = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);

        $days = ['Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'];

        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $dayShootings = $shootings->get($dateKey, collect());
                $dayPublications = $publications->get($dateKey, collect());
                
                // Vérifier les avertissements pour les publications
                $hasDayWarning = false;
                $dayOfWeek = $days[$currentDate->format('l')] ?? strtolower($currentDate->format('l'));
                foreach ($dayPublications as $pub) {
                    if ($pub->client->isDayNotRecommended($dayOfWeek)) {
                        $hasDayWarning = true;
                        break;
                    }
                }
                
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'shootings' => $dayShootings,
                    'publications' => $dayPublications,
                    'hasWarnings' => $hasDayWarning,
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Génère un rapport Word détaillé
     */
    public function generateReport(Request $request)
    {
        $clientId = $request->get('client_id');
        $period = $request->get('period', 'monthly');
        $now = Carbon::now();

        if (!in_array($period, ['weekly', 'monthly', 'annual'], true)) {
            $period = 'monthly';
        }

        switch ($period) {
            case 'weekly':
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $periodLabel = 'Hebdomadaire';
                $periodSlug = 'hebdomadaire';
                break;
            case 'annual':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $periodLabel = 'Annuel';
                $periodSlug = 'annuel';
                break;
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodLabel = 'Mensuel';
                $periodSlug = 'mensuel';
                break;
        }
        
        if ($clientId && $clientId !== 'all') {
            $client = Client::with(['shootings', 'publications.contentIdea', 'publications.shooting', 'publicationRules'])
                ->findOrFail($clientId);
            $clients = collect([$client]);
            $title = "Rapport {$periodLabel} - " . $client->nom_entreprise;
        } else {
            $clients = Client::with(['shootings', 'publications.contentIdea', 'publications.shooting', 'publicationRules'])
                ->orderBy('nom_entreprise')
                ->get();
            $title = "Rapport {$periodLabel} - Tous les Clients";
        }
        
        $html = $this->generateReportHTML($clients, $title, $now, $startDate, $endDate, $periodLabel);
        
        $filename = $clientId && $clientId !== 'all' 
            ? 'rapport_' . $periodSlug . '_' . str_replace(' ', '_', $client->nom_entreprise) . '_' . $now->format('Y-m-d') . '.doc'
            : 'rapport_' . $periodSlug . '_tous_clients_' . $now->format('Y-m-d') . '.doc';
        
        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Génère le HTML du rapport
     */
    private function generateReportHTML($clients, $title, $now, $startDate, $endDate, $periodLabel)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        h1 { color: #FF6A3A; border-bottom: 3px solid #FF6A3A; padding-bottom: 10px; }
        h2 { color: #303030; margin-top: 30px; border-bottom: 2px solid #303030; padding-bottom: 5px; }
        h3 { color: #FF6A3A; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #FF6A3A; color: white; padding: 12px; text-align: left; }
        td { border: 1px solid #ddd; padding: 10px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-cancelled { color: #6c757d; font-weight: bold; }
        .stat-box { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #FF6A3A; }
        .client-section { page-break-after: always; margin-bottom: 40px; }
        .summary { background: #fffbf0; padding: 20px; margin: 20px 0; border: 1px solid #ffc107; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>
    <p><strong>Date de génération :</strong> ' . $now->format('d/m/Y à H:i') . '</p>
    <p><strong>Période :</strong> ' . $periodLabel . ' (du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y') . ')</p>';
        
        foreach ($clients as $client) {
            $shootings = $client->shootings()
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('date')
                ->get();
            $publications = $client->publications()
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('date')
                ->get();
            $rules = $client->publicationRules;
            
            $html .= '<div class="client-section">
                <h2>' . htmlspecialchars($client->nom_entreprise) . '</h2>
                
                <div class="summary">
                    <h3>Résumé</h3>
                    <p><strong>Total Tournages :</strong> ' . $shootings->count() . '</p>
                    <p><strong>Total Publications :</strong> ' . $publications->count() . '</p>
                    <p><strong>Tournages complétés :</strong> ' . $shootings->where('status', 'completed')->count() . '</p>
                    <p><strong>Publications complétées :</strong> ' . $publications->where('status', 'completed')->count() . '</p>
                    <p><strong>Tournages en attente :</strong> ' . $shootings->where('status', 'pending')->count() . '</p>
                    <p><strong>Publications en attente :</strong> ' . $publications->where('status', 'pending')->count() . '</p>
                </div>';
            
            if ($rules->count() > 0) {
                $html .= '<h3>Règles de Publication</h3>
                <table>
                    <tr><th>Jour non recommandé</th></tr>';
                foreach ($rules as $rule) {
                    $html .= '<tr><td>' . ucfirst($rule->day_of_week) . '</td></tr>';
                }
                $html .= '</table>';
            }
            
            if ($shootings->count() > 0) {
                $html .= '<h3>Tournages</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Idées de contenu</th>
                    </tr>';
                
                foreach ($shootings as $shooting) {
                    $statusClass = 'status-' . $shooting->status;
                    $statusText = $shooting->status === 'completed' ? 'Complété' : 
                                  ($shooting->status === 'cancelled' ? 'Annulé' : 'En attente');
                    $contentIdeas = $shooting->contentIdeas->pluck('titre')->join(', ') ?: 'Aucune';
                    
                    $html .= '<tr>
                        <td>' . $shooting->date->format('d/m/Y') . '</td>
                        <td class="' . $statusClass . '">' . $statusText . '</td>
                        <td>' . htmlspecialchars($contentIdeas) . '</td>
                    </tr>';
                }
                $html .= '</table>';
            }
            
            if ($publications->count() > 0) {
                $html .= '<h3>Publications</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Idée de contenu</th>
                        <th>Tournage lié</th>
                        <th>Statut</th>
                    </tr>';
                
                foreach ($publications as $publication) {
                    $statusClass = 'status-' . $publication->status;
                    $statusText = $publication->status === 'completed' ? 'Complétée' : 
                                  ($publication->status === 'cancelled' ? 'Annulée' : 'En attente');
                    $shootingLink = $publication->shooting ? 
                        'Tournage du ' . $publication->shooting->date->format('d/m/Y') : 'Aucun';
                    
                    $html .= '<tr>
                        <td>' . $publication->date->format('d/m/Y') . '</td>
                        <td>' . htmlspecialchars($publication->contentIdea->titre) . '</td>
                        <td>' . $shootingLink . '</td>
                        <td class="' . $statusClass . '">' . $statusText . '</td>
                    </tr>';
                }
                $html .= '</table>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Exporte le calendrier en Excel
     */
    public function exportCalendar(Request $request)
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdeas'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return Carbon::parse($shooting->date)->format('Y-m-d');
            });

        $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return Carbon::parse($publication->date)->format('Y-m-d');
            });

        $calendar = $this->buildCombinedCalendar($startDate, $shootings, $publications);
        
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $filename = 'calendrier_' . $months[$month] . '_' . $year . '.csv';
        
        $csv = $this->generateCalendarCSV($calendar, $months[$month], $year);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Génère le CSV du calendrier
     */
    private function generateCalendarCSV($calendar, $monthName, $year)
    {
        $output = fopen('php://temp', 'r+');
        
        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-tête
        fputcsv($output, ['Planning Global - ' . $monthName . ' ' . $year], ';');
        fputcsv($output, [], ';');
        
        // En-têtes des jours de la semaine
        $headers = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        fputcsv($output, $headers, ';');
        
        // Données - chaque ligne représente une semaine
        foreach ($calendar as $week) {
            $row = [];
            
            foreach ($week as $day) {
                $cellContent = [];
                
                if ($day['isCurrentMonth']) {
                    // Date du jour
                    $cellContent[] = $day['date']->format('d/m');
                    
                    // Tournages
                    foreach ($day['shootings'] as $shooting) {
                        // Déterminer le statut et l'icône
                        $icon = '📹';
                        $statusText = '';
                        if ($shooting->status === 'cancelled') {
                            $icon = '❌';
                            $statusText = 'Annulé';
                        } elseif ($shooting->isCompleted()) {
                            $icon = '✅';
                            $statusText = 'Complété';
                        } elseif ($shooting->isOverdue()) {
                            $icon = '🚨';
                            $statusText = 'En retard';
                        } elseif ($shooting->isUpcoming()) {
                            $icon = '⏰';
                            $statusText = 'À venir';
                        } else {
                            $statusText = 'En attente';
                        }
                        
                        $cellContent[] = $icon . ' TOURNAGE - ' . $shooting->client->nom_entreprise;
                        $cellContent[] = '   Statut: ' . $statusText;
                        if ($shooting->contentIdeas->count() > 0) {
                            $cellContent[] = '   Idées de contenu (' . $shooting->contentIdeas->count() . '):';
                            foreach ($shooting->contentIdeas as $idea) {
                                $cellContent[] = '     • ' . $idea->titre;
                            }
                        }
                    }
                    
                    // Publications
                    foreach ($day['publications'] as $publication) {
                        // Déterminer le statut et l'icône
                        $icon = '📢';
                        $statusText = '';
                        if ($publication->status === 'cancelled') {
                            $icon = '❌';
                            $statusText = 'Annulée';
                        } elseif ($publication->isCompleted()) {
                            $icon = '✅';
                            $statusText = 'Complétée';
                        } elseif ($publication->isOverdue()) {
                            $icon = '🚨';
                            $statusText = 'En retard';
                        } elseif ($publication->isUpcoming()) {
                            $icon = '⏰';
                            $statusText = 'À venir';
                        } else {
                            $statusText = 'En attente';
                        }
                        
                        $cellContent[] = $icon . ' PUBLICATION - ' . $publication->client->nom_entreprise;
                        $cellContent[] = '   Statut: ' . $statusText;
                        $cellContent[] = '   Idée: ' . $publication->contentIdea->titre;
                        if ($publication->shooting) {
                            $cellContent[] = '   Tournage lié: ' . $publication->shooting->date->format('d/m/Y');
                        }
                        
                        // Avertissement si jour non recommandé
                        $days = ['Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'];
                        $pubDayOfWeek = $days[Carbon::parse($publication->date)->format('l')] ?? '';
                        if ($publication->client->isDayNotRecommended($pubDayOfWeek)) {
                            $cellContent[] = '   ⚠️ Jour non recommandé pour ce client';
                        }
                    }
                } else {
                    // Jour du mois précédent/suivant (vide ou avec date)
                    $cellContent[] = $day['date']->format('d/m');
                }
                
                $row[] = implode("\n", $cellContent);
            }
            
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}

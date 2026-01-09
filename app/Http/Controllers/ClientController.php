<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Affiche la liste des clients
     */
    public function index()
    {
        $clients = Client::withCount(['shootings', 'publications'])
            ->orderBy('nom_entreprise')
            ->get();

        return view('clients.index', compact('clients'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Enregistre un nouveau client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_entreprise' => ['required', 'string', 'max:255'],
        ]);

        $client = Client::create($validated);

        // Redirection intelligente : si on vient d'un formulaire qui nécessitait ce client, on y retourne
        if ($request->has('return_to')) {
            return redirect($request->input('return_to') . '?client_id=' . $client->id)
                ->with('success', 'Client créé avec succès. Vous pouvez maintenant l\'utiliser.')
                ->with('created_client_id', $client->id);
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Affiche les détails d'un client
     */
    public function show(Client $client)
    {
        $client->load(['publicationRules', 'shootings', 'publications']);
        return view('clients.show', compact('client'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Met à jour un client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nom_entreprise' => ['required', 'string', 'max:255'],
        ], [
            'nom_entreprise.required' => 'Le nom de l\'entreprise est obligatoire.',
            'nom_entreprise.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères.',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client modifié avec succès.');
    }

    /**
     * Supprime un client
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Affiche le dashboard client (espace client en lecture seule)
     */
    public function dashboard(Request $request, Client $client)
    {
        $now = \Carbon\Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        
        // Créer la date du premier jour du mois
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Charger toutes les relations nécessaires
        $client->load(['publicationRules', 'shootings.contentIdeas', 'publications.contentIdea', 'publications.shooting']);

        // Récupérer les tournages du mois
        $shootings = $client->shootings()
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('contentIdeas')
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return \Carbon\Carbon::parse($shooting->date)->format('Y-m-d');
            });

        // Récupérer les publications du mois
        $publications = $client->publications()
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return \Carbon\Carbon::parse($publication->date)->format('Y-m-d');
            });

        // Construire le calendrier
        $calendar = $this->buildClientCalendar($startDate, $shootings, $publications);

        // Statistiques
        $stats = [
            'total_shootings' => $client->shootings->count(),
            'total_publications' => $client->publications->count(),
            'shootings_this_month' => $shootings->flatten()->count(),
            'publications_this_month' => $publications->flatten()->count(),
            'pending_shootings' => $client->shootings()->where('status', 'pending')->count(),
            'completed_shootings' => $client->shootings()->where('status', 'completed')->count(),
            'cancelled_shootings' => $client->shootings()->where('status', 'cancelled')->count(),
            'non_realises_shootings' => $client->shootings()->where('status', '!=', 'completed')->count(), // Tous sauf complétés
            'pending_publications' => $client->publications()->where('status', 'pending')->count(),
            'completed_publications' => $client->publications()->where('status', 'completed')->count(),
            'cancelled_publications' => $client->publications()->where('status', 'cancelled')->count(),
            'non_realises_publications' => $client->publications()->where('status', '!=', 'completed')->count(), // Tous sauf complétées
            'publication_rules' => $client->publicationRules->count(),
        ];

        // Tournages à venir (dans les 30 prochains jours)
        $upcomingShootings = $client->shootings()
            ->where('date', '>=', $now->toDateString())
            ->where('date', '<=', $now->copy()->addDays(30)->toDateString())
            ->where('status', 'pending')
            ->orderBy('date')
            ->with('contentIdeas')
            ->get();

        // Publications à venir (dans les 30 prochains jours)
        $upcomingPublications = $client->publications()
            ->where('date', '>=', $now->toDateString())
            ->where('date', '<=', $now->copy()->addDays(30)->toDateString())
            ->where('status', 'pending')
            ->orderBy('date')
            ->with('contentIdea')
            ->get();

        // Tournages passés récents (30 derniers jours)
        $recentShootings = $client->shootings()
            ->where('date', '>=', $now->copy()->subDays(30)->toDateString())
            ->where('date', '<', $now->toDateString())
            ->orderBy('date', 'desc')
            ->with('contentIdeas')
            ->get();

        // Publications passées récentes (30 derniers jours)
        $recentPublications = $client->publications()
            ->where('date', '>=', $now->copy()->subDays(30)->toDateString())
            ->where('date', '<', $now->toDateString())
            ->orderBy('date', 'desc')
            ->with('contentIdea')
            ->get();

        return view('clients.dashboard', compact(
            'client',
            'calendar',
            'month',
            'year',
            'startDate',
            'stats',
            'upcomingShootings',
            'upcomingPublications',
            'recentShootings',
            'recentPublications'
        ));
    }

    /**
     * Construit le calendrier pour un client
     */
    public function buildClientCalendar($startDate, $shootings, $publications)
    {
        $calendar = [];
        $currentDate = $startDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);

        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'shootings' => $shootings->get($dateKey, collect()),
                    'publications' => $publications->get($dateKey, collect()),
                    'hasWarnings' => false, // Pas de warnings dans l'espace client
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Génère un rapport Word pour le client
     */
    public function generateReport(Client $client)
    {
        $now = \Carbon\Carbon::now();
        
        // Charger toutes les relations nécessaires
        $client->load(['shootings.contentIdeas', 'publications.contentIdea', 'publications.shooting', 'publicationRules']);
        
        $shootings = $client->shootings()->orderBy('date')->get();
        $publications = $client->publications()->orderBy('date')->get();
        $rules = $client->publicationRules;
        
        $title = "Rapport - " . $client->nom_entreprise;
        
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
        .summary { background: #fffbf0; padding: 20px; margin: 20px 0; border: 1px solid #ffc107; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>
    <p><strong>Date de génération :</strong> ' . $now->format('d/m/Y à H:i') . '</p>
    
    <div class="client-section">
        <h2>' . htmlspecialchars($client->nom_entreprise) . '</h2>
        
        <div class="summary">
            <h3>Résumé</h3>
            <p><strong>Total Tournages :</strong> ' . $shootings->count() . '</p>
            <p><strong>Total Publications :</strong> ' . $publications->count() . '</p>
            <p><strong>Tournages complétés :</strong> ' . $shootings->where('status', 'completed')->count() . '</p>
            <p><strong>Publications complétées :</strong> ' . $publications->where('status', 'completed')->count() . '</p>
            <p><strong>Tournages en attente :</strong> ' . $shootings->where('status', 'pending')->count() . '</p>
            <p><strong>Publications en attente :</strong> ' . $publications->where('status', 'pending')->count() . '</p>
            <p><strong>Tournages annulés :</strong> ' . $shootings->where('status', 'cancelled')->count() . '</p>
            <p><strong>Publications annulées :</strong> ' . $publications->where('status', 'cancelled')->count() . '</p>
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
                    <th>Description</th>
                </tr>';
            
            foreach ($shootings as $shooting) {
                $statusClass = 'status-' . $shooting->status;
                $statusText = $shooting->status === 'completed' ? 'Complété' : 
                              ($shooting->status === 'cancelled' ? 'Annulé' : 'En attente');
                $contentIdeas = $shooting->contentIdeas->pluck('titre')->join(', ') ?: 'Aucune';
                $description = $shooting->description ? htmlspecialchars($shooting->description) : 'Aucune';
                
                $html .= '<tr>
                    <td>' . $shooting->date->format('d/m/Y') . '</td>
                    <td class="' . $statusClass . '">' . $statusText . '</td>
                    <td>' . htmlspecialchars($contentIdeas) . '</td>
                    <td>' . $description . '</td>
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
                    <th>Description</th>
                </tr>';
            
            foreach ($publications as $publication) {
                $statusClass = 'status-' . $publication->status;
                $statusText = $publication->status === 'completed' ? 'Complétée' : 
                              ($publication->status === 'cancelled' ? 'Annulée' : 'En attente');
                $shootingLink = $publication->shooting ? 
                    'Tournage du ' . $publication->shooting->date->format('d/m/Y') : 'Aucun';
                $description = $publication->description ? htmlspecialchars($publication->description) : 'Aucune';
                
                $html .= '<tr>
                    <td>' . $publication->date->format('d/m/Y') . '</td>
                    <td>' . htmlspecialchars($publication->contentIdea->titre) . '</td>
                    <td>' . $shootingLink . '</td>
                    <td class="' . $statusClass . '">' . $statusText . '</td>
                    <td>' . $description . '</td>
                </tr>';
            }
            $html .= '</table>';
        }
        
        $html .= '</div>
</body>
</html>';
        
        $filename = 'rapport_' . str_replace(' ', '_', $client->nom_entreprise) . '_' . $now->format('Y-m-d') . '.doc';
        
        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }
}

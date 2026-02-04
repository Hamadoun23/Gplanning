<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientReport;
use App\Models\Shooting;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        return $this->viewForRole('clients.index', compact('clients'));
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

        return redirect()->route('clients.dashboard', $client)
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Affiche les détails d'un client
     */
    public function show(Request $request, Client $client)
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

        // Statistiques filtrées par mois/année
        $stats = [
            'total_shootings' => $shootings->flatten()->count(),
            'total_publications' => $publications->flatten()->count(),
            'shootings_this_month' => $shootings->flatten()->count(),
            'publications_this_month' => $publications->flatten()->count(),
            'pending_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
            'pending_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
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

        // Charger les rapports du client séparés par type
        $monthlyReports = $client->reports()
            ->where('report_type', 'monthly')
            ->orderBy('uploaded_at', 'desc')
            ->get();
            
        $annualReports = $client->reports()
            ->where('report_type', 'annual')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        // Déterminer si l'utilisateur est en lecture seule (team)
        $isTeamReadOnly = auth()->user() && auth()->user()->role === 'team';

        return $this->viewForRole('clients.show', compact(
            'client',
            'calendar',
            'month',
            'year',
            'startDate',
            'stats',
            'upcomingShootings',
            'upcomingPublications',
            'recentShootings',
            'recentPublications',
            'monthlyReports',
            'annualReports',
            'isTeamReadOnly'
        ));
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

        return redirect()->route('clients.dashboard', $client)
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

        // Statistiques filtrées par mois/année
        $stats = [
            'total_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'total_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'shootings_this_month' => $shootings->flatten()->count(),
            'publications_this_month' => $publications->flatten()->count(),
            'pending_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
            'pending_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
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

        // Charger les rapports du client séparés par type
        $monthlyReports = $client->reports()
            ->where('report_type', 'monthly')
            ->orderBy('uploaded_at', 'desc')
            ->get();
            
        $annualReports = $client->reports()
            ->where('report_type', 'annual')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        // Déterminer si l'utilisateur est en lecture seule (team)
        $isTeamReadOnly = auth()->user() && auth()->user()->role === 'team';

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
            'recentPublications',
            'monthlyReports',
            'annualReports',
            'isTeamReadOnly'
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
     * Génère un planning Word pour le client
     */
    public function generateReport(Request $request, Client $client)
    {
        $now = \Carbon\Carbon::now();
        $type = $request->get('type', 'monthly'); // 'monthly' ou 'annual'
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        
        // Charger toutes les relations nécessaires
        $client->load(['shootings.contentIdeas', 'publications.contentIdea', 'publications.shooting', 'publicationRules']);
        
        // Filtrer selon le type
        if ($type === 'annual') {
            // Planning annuel : toute l'année
            $startDate = \Carbon\Carbon::create($year, 1, 1);
            $endDate = \Carbon\Carbon::create($year, 12, 31);
            $shootings = $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('date')
                ->get();
            $publications = $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('date')
                ->get();
            $periodLabel = "Année {$year}";
        } else {
            // Planning mensuel : le mois sélectionné
            $startDate = \Carbon\Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $shootings = $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('date')
                ->get();
            $publications = $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('date')
                ->get();
            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            $periodLabel = $months[$month] . " {$year}";
        }
        
        $rules = $client->publicationRules;
        
        $title = "Planning - " . $client->nom_entreprise . " - " . $periodLabel;
        
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
                    <td>' . $shooting->date->format('d/m/Y H:i') . '</td>
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
                    'Tournage du ' . $publication->shooting->date->format('d/m/Y H:i') : 'Aucun';
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
        
        $clientNameSlug = str_replace(' ', '_', $client->nom_entreprise);
        if ($type === 'annual') {
            $filename = 'planning_' . $clientNameSlug . '_' . $year . '.doc';
        } else {
            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            $monthName = $months[$month];
            $filename = 'planning_' . $clientNameSlug . '_' . $monthName . '_' . $year . '.doc';
        }
        
        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Téléverse un rapport PDF pour un client
     */
    public function uploadReport(Request $request, Client $client)
    {
        $request->validate([
            'report_type' => 'required|in:monthly,annual',
            'report_date' => 'required|date',
            'report_file' => 'required|file|mimes:pdf|max:51200', // Max 50MB
        ], [
            'report_type.required' => 'Le type de rapport est obligatoire.',
            'report_type.in' => 'Le type de rapport doit être mensuel ou annuel.',
            'report_date.required' => 'La date du rapport est obligatoire.',
            'report_date.date' => 'La date du rapport doit être une date valide.',
            'report_file.required' => 'Le fichier PDF est obligatoire.',
            'report_file.file' => 'Le fichier doit être un fichier valide.',
            'report_file.mimes' => 'Le fichier doit être au format PDF.',
            'report_file.max' => 'Le fichier ne doit pas dépasser 50 Mo.',
        ]);

        try {
            $file = $request->file('report_file');
            $originalFilename = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            
            // Générer un nom de fichier unique
            $filename = time() . '_' . $client->id . '_' . $request->report_type . '_' . $originalFilename;
            $path = $file->storeAs('client-reports', $filename, 'public');

            // Créer l'enregistrement dans la base de données
            $report = ClientReport::create([
                'client_id' => $client->id,
                'report_type' => $request->report_type,
                'report_date' => $request->report_date,
                'file_path' => $path,
                'original_filename' => $originalFilename,
                'file_size' => $fileSize,
                'uploaded_at' => now(),
            ]);

            return redirect()->route('clients.show', $client)
                ->with('success', 'Rapport téléversé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('clients.show', $client)
                ->with('error', 'Erreur lors du téléversement du rapport : ' . $e->getMessage());
        }
    }

    /**
     * Télécharge un rapport
     */
    public function downloadReport(Client $client, ClientReport $report)
    {
        // Vérifier que le rapport appartient au client
        if ($report->client_id !== $client->id) {
            abort(403, 'Accès non autorisé.');
        }

        if (!Storage::disk('public')->exists($report->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        // Générer un nom de fichier avec le nom du client et le type
        $clientName = \Str::slug($client->nom_entreprise);
        $reportTypeLabel = $report->report_type === 'monthly' ? 'Mensuel' : 'Annuel';
        $reportDate = $report->report_date ? $report->report_date->format('Y-m') : '';
        
        // Nom du fichier : NomClient_Rapport_Type_Date.pdf
        $downloadFilename = $clientName . '_Rapport_' . $reportTypeLabel . '_' . $reportDate . '.pdf';

        return Storage::disk('public')->download($report->file_path, $downloadFilename);
    }

    /**
     * Supprime un rapport
     */
    public function deleteReport(Client $client, ClientReport $report)
    {
        // Vérifier que le rapport appartient au client
        if ($report->client_id !== $client->id) {
            abort(403, 'Accès non autorisé.');
        }

        try {
            // Supprimer le fichier
            if (Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }

            // Supprimer l'enregistrement
            $report->delete();

            return redirect()->route('clients.show', $client)
                ->with('success', 'Rapport supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('clients.show', $client)
                ->with('error', 'Erreur lors de la suppression du rapport : ' . $e->getMessage());
        }
    }

    /**
     * Récupère les statistiques filtrées par mois/année (AJAX)
     */
    public function getStats(Request $request, Client $client)
    {
        $month = (int) $request->get('month', \Carbon\Carbon::now()->month);
        $year = (int) $request->get('year', \Carbon\Carbon::now()->year);
        
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Statistiques filtrées par mois/année
        $stats = [
            'total_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'total_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'shootings_this_month' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'publications_this_month' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count(),
            'pending_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_shootings' => $client->shootings()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
            'pending_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'pending')
                ->count(),
            'completed_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'completed')
                ->count(),
            'cancelled_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'cancelled')
                ->count(),
            'non_realises_publications' => $client->publications()
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['not_realized', 'cancelled'])
                ->count(),
            'publication_rules' => $client->publicationRules->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Récupère tous les événements d'un type spécifique (AJAX)
     */
    public function getEvents(Request $request, Client $client)
    {
        try {
            $type = $request->get('type'); // 'upcoming-shootings', 'upcoming-publications', 'recent-shootings', 'recent-publications'
            $now = \Carbon\Carbon::now();
            
            $events = collect();
            
            switch ($type) {
                case 'upcoming-shootings':
                    $events = $client->shootings()
                        ->where('date', '>=', $now->toDateString())
                        ->where('date', '<=', $now->copy()->addDays(30)->toDateString())
                        ->where('status', 'pending')
                        ->orderBy('date')
                        ->with('contentIdeas')
                        ->get()
                        ->map(function($shooting) use ($client) {
                            $contentTitle = $shooting->contentIdeas->count() > 0 
                                ? $shooting->contentIdeas->first()->titre 
                                : 'Aucune idée de contenu';
                            return [
                                'id' => $shooting->id,
                                'type' => 'shooting',
                                'date' => $shooting->date->format('d/m/Y H:i'),
                                'title' => 'Tournage',
                                'details' => $contentTitle . ($shooting->description ? ' • ' . Str::limit($shooting->description, 50) : ''),
                                'url' => route('clients.shootings.show', [$client, $shooting]),
                            ];
                        });
                    break;
                    
                case 'upcoming-publications':
                    $events = $client->publications()
                        ->where('date', '>=', $now->toDateString())
                        ->where('date', '<=', $now->copy()->addDays(30)->toDateString())
                        ->where('status', 'pending')
                        ->orderBy('date')
                        ->with(['contentIdea', 'shooting'])
                        ->get()
                        ->map(function($publication) use ($client) {
                            if (!$publication->contentIdea) {
                                return null;
                            }
                            $shootingText = $publication->shooting 
                                ? 'Tournage lié du ' . $publication->shooting->date->format('d/m/Y')
                                : 'Aucun tournage lié';
                            return [
                                'id' => $publication->id,
                                'type' => 'publication',
                                'date' => $publication->date->format('d/m/Y H:i'),
                                'title' => $publication->contentIdea->titre,
                                'details' => $shootingText . ($publication->description ? ' • ' . Str::limit($publication->description, 50) : ''),
                                'url' => route('clients.publications.show', [$client, $publication]),
                            ];
                        })
                        ->filter(); // Retirer les null
                    break;
                    
                case 'recent-shootings':
                    $events = $client->shootings()
                        ->where('date', '>=', $now->copy()->subDays(30)->toDateString())
                        ->where('date', '<', $now->toDateString())
                        ->orderBy('date', 'desc')
                        ->with('contentIdeas')
                        ->get()
                        ->map(function($shooting) use ($client) {
                            $statusBadge = '';
                            if ($shooting->isCompleted()) {
                                $statusBadge = '<span class="status-badge completed">Complété</span>';
                            } elseif ($shooting->status === 'cancelled') {
                                $statusBadge = '<span class="status-badge cancelled">Annulé</span>';
                            }
                            $contentTitle = $shooting->contentIdeas->count() > 0 
                                ? $shooting->contentIdeas->first()->titre 
                                : 'Aucune idée de contenu';
                            return [
                                'id' => $shooting->id,
                                'type' => 'shooting',
                                'date' => $shooting->date->format('d/m/Y H:i'),
                                'title' => 'Tournage',
                                'titleHtml' => 'Tournage ' . $statusBadge,
                                'details' => $contentTitle . ($shooting->description ? ' • ' . Str::limit($shooting->description, 50) : ''),
                                'url' => route('clients.shootings.show', [$client, $shooting]),
                            ];
                        });
                    break;
                    
                case 'recent-publications':
                    $events = $client->publications()
                        ->where('date', '>=', $now->copy()->subDays(30)->toDateString())
                        ->where('date', '<', $now->toDateString())
                        ->orderBy('date', 'desc')
                        ->with(['contentIdea', 'shooting'])
                        ->get()
                        ->map(function($publication) use ($client) {
                            if (!$publication->contentIdea) {
                                return null;
                            }
                            $statusBadge = '';
                            if ($publication->isCompleted()) {
                                $statusBadge = '<span class="status-badge completed">Complétée</span>';
                            } elseif ($publication->status === 'cancelled') {
                                $statusBadge = '<span class="status-badge cancelled">Annulée</span>';
                            }
                            $shootingText = $publication->shooting 
                                ? 'Tournage lié du ' . $publication->shooting->date->format('d/m/Y')
                                : 'Aucun tournage lié';
                            return [
                                'id' => $publication->id,
                                'type' => 'publication',
                                'date' => $publication->date->format('d/m/Y H:i'),
                                'title' => $publication->contentIdea->titre,
                                'titleHtml' => $publication->contentIdea->titre . ' ' . $statusBadge,
                                'details' => $shootingText . ($publication->description ? ' • ' . Str::limit($publication->description, 50) : ''),
                                'url' => route('clients.publications.show', [$client, $publication]),
                            ];
                        })
                        ->filter(); // Retirer les null
                    break;
                    
                default:
                    return response()->json(['error' => 'Type d\'événement invalide'], 400);
            }
            
            return response()->json([
                'events' => $events->values(),
                'count' => $events->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getEvents: ' . $e->getMessage(), [
                'type' => $request->get('type'),
                'client_id' => $client->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors du chargement des événements: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupère tous les événements d'un type spécifique pour les clients (AJAX)
     */
    public function getClientEvents(Request $request, Client $client)
    {
        // Réutilise la même logique que getEvents
        return $this->getEvents($request, $client);
    }
    
    /**
     * Affiche les détails d'un tournage pour le client (lecture seule)
     */
    public function showShooting(Client $client, Shooting $shooting)
    {
        // Vérifier que le tournage appartient bien au client
        if ($shooting->client_id !== $client->id) {
            abort(403, 'Accès non autorisé à ce tournage.');
        }
        
        $shooting->load(['client', 'contentIdeas']);
        
        return view('client-space.shootings.show', compact('client', 'shooting'));
    }
    
    /**
     * Affiche les détails d'une publication pour le client (lecture seule)
     */
    public function showPublication(Client $client, Publication $publication)
    {
        // Vérifier que la publication appartient bien au client
        if ($publication->client_id !== $client->id) {
            abort(403, 'Accès non autorisé à cette publication.');
        }
        
        $publication->load(['client', 'contentIdea', 'shooting']);
        
        return view('client-space.publications.show', compact('client', 'publication'));
    }
}

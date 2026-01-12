<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContentIdeaController;
use App\Http\Controllers\PublicationRuleController;
use App\Http\Controllers\ShootingController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\PlanningComparisonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Routes d'authentification (Breeze)
require __DIR__.'/auth.php';

// Redirection de la racine
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isClient() && $user->client_id) {
            return redirect()->route('clients.dashboard', $user->client_id);
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Dashboard admin (réservé aux admins)
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/generate-report', [DashboardController::class, 'generateReport'])->name('dashboard.generate-report');
        Route::get('/dashboard/export-calendar', [DashboardController::class, 'exportCalendar'])->name('dashboard.export-calendar');

        // Comparaison de plannings
        Route::get('/planning-comparison', [PlanningComparisonController::class, 'index'])->name('planning-comparison.index');

        // Clients (CRUD) - réservé aux admins
        Route::resource('clients', ClientController::class);
    });

    // Dashboard client (accessible aux clients pour leur propre client)
    Route::get('clients/{client}/dashboard', [ClientController::class, 'dashboard'])
        ->middleware('client.access')
        ->name('clients.dashboard');
        
    // Génération de rapport côté client (accessible uniquement pour le client concerné)
    Route::get('clients/{client}/generate-report', [ClientController::class, 'generateReport'])
        ->middleware('client.access')
        ->name('clients.generate-report');

    // Routes réservées aux admins uniquement
    Route::middleware(['admin'])->group(function () {
        // Idées de contenu (partagées entre tous les clients)
        Route::resource('content-ideas', ContentIdeaController::class);

        // Règles de publication (nested dans clients)
        Route::prefix('clients/{client}')->name('clients.')->group(function () {
            Route::get('publication-rules', [PublicationRuleController::class, 'index'])->name('publication-rules.index');
            Route::get('publication-rules/create', [PublicationRuleController::class, 'create'])->name('publication-rules.create');
            Route::post('publication-rules', [PublicationRuleController::class, 'store'])->name('publication-rules.store');
            Route::delete('publication-rules/{publicationRule}', [PublicationRuleController::class, 'destroy'])->name('publication-rules.destroy');
        });

        // Tournages
        Route::resource('shootings', ShootingController::class);
        Route::post('shootings/{shooting}/toggle-status', [ShootingController::class, 'toggleStatus'])->name('shootings.toggle-status');
        Route::post('shootings/{shooting}/reschedule', [ShootingController::class, 'reschedule'])->name('shootings.reschedule');
        Route::get('shootings/export-calendar', [ShootingController::class, 'exportCalendar'])->name('shootings.export-calendar');

        // Publications
        Route::resource('publications', PublicationController::class);
        Route::post('publications/{publication}/toggle-status', [PublicationController::class, 'toggleStatus'])->name('publications.toggle-status');
        Route::post('publications/{publication}/reschedule', [PublicationController::class, 'reschedule'])->name('publications.reschedule');
        Route::get('publications/export-calendar', [PublicationController::class, 'exportCalendar'])->name('publications.export-calendar');
    });

    // API Routes for UX features
    Route::prefix('api')->group(function () {
        Route::get('autocomplete/{type}', function ($type) {
            $query = request('q', '');
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            
            switch ($type) {
                case 'clients':
                    $results = \App\Models\Client::where('nom_entreprise', 'like', "%{$query}%")
                        ->limit(10)
                        ->get()
                        ->map(fn($c) => ['id' => $c->id, 'name' => $c->nom_entreprise]);
                    break;
                case 'content-ideas':
                    $results = \App\Models\ContentIdea::where('titre', 'like', "%{$query}%")
                        ->limit(10)
                        ->get()
                        ->map(fn($i) => ['id' => $i->id, 'titre' => $i->titre, 'type' => $i->type]);
                    break;
                default:
                    $results = collect();
            }
            
            return response()->json($results);
        });
        
        Route::get('shootings/{shooting}', function ($shooting) {
            $shooting = \App\Models\Shooting::with('client')->findOrFail($shooting);
            return response()->json([
                'id' => $shooting->id,
                'date' => $shooting->date->format('Y-m-d'),
                'client' => $shooting->client->nom_entreprise
            ]);
        });
        
        Route::get('client-event-details', function () {
            $type = request('type'); // 'shooting' ou 'publication'
            $id = request('id');
            $clientId = request('client_id');
            
            if (!$type || !$id || !$clientId) {
                return response()->json(['error' => 'Paramètres manquants'], 400);
            }
            
            if ($type === 'shooting') {
                $shooting = \App\Models\Shooting::where('id', $id)
                    ->where('client_id', $clientId)
                    ->with(['contentIdeas'])
                    ->first();
                
                if (!$shooting) {
                    return response()->json(['error' => 'Tournage non trouvé'], 404);
                }
                
                return response()->json([
                    'type' => 'shooting',
                    'data' => [
                        'id' => $shooting->id,
                        'date' => $shooting->date->format('Y-m-d'),
                        'status' => $shooting->status,
                        'status_text' => $shooting->status === 'completed' ? 'Complété' : ($shooting->status === 'cancelled' ? 'Annulé' : ($shooting->isOverdue() ? 'En retard' : ($shooting->isUpcoming() ? 'À venir' : 'En attente'))),
                        'description' => $shooting->description,
                        'content_ideas' => $shooting->contentIdeas->map(fn($ci) => $ci->titre)->toArray(),
                        'is_overdue' => $shooting->isOverdue(),
                        'is_upcoming' => $shooting->isUpcoming(),
                        'is_completed' => $shooting->isCompleted(),
                    ]
                ]);
            } else if ($type === 'publication') {
                $publication = \App\Models\Publication::where('id', $id)
                    ->where('client_id', $clientId)
                    ->with(['contentIdea', 'shooting'])
                    ->first();
                
                if (!$publication) {
                    return response()->json(['error' => 'Publication non trouvée'], 404);
                }
                
                return response()->json([
                    'type' => 'publication',
                    'data' => [
                        'id' => $publication->id,
                        'date' => $publication->date->format('Y-m-d'),
                        'status' => $publication->status,
                        'status_text' => $publication->status === 'completed' ? 'Complétée' : ($publication->status === 'cancelled' ? 'Annulée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'À venir' : 'En attente'))),
                        'description' => $publication->description,
                        'content_idea_titre' => $publication->contentIdea->titre ?? '',
                        'shooting_date' => $publication->shooting ? $publication->shooting->date->format('d/m/Y') : null,
                        'is_overdue' => $publication->isOverdue(),
                        'is_upcoming' => $publication->isUpcoming(),
                        'is_completed' => $publication->isCompleted(),
                    ]
                ]);
            }
            
            return response()->json(['error' => 'Type invalide'], 400);
        });
        
        Route::get('client-events-by-date', function () {
            $date = request('date');
            $clientId = request('client_id');
            
            if (!$date || !$clientId) {
                return response()->json(['shootings' => [], 'publications' => []]);
            }
            
            // Récupérer uniquement les événements du client spécifié
            $shootings = \App\Models\Shooting::whereDate('date', $date)
                ->where('client_id', $clientId)
                ->with(['contentIdeas'])
                ->orderBy('date')
                ->get()
                ->map(function($shooting) {
                    return [
                        'id' => $shooting->id,
                        'date' => $shooting->date->format('Y-m-d'),
                        'status' => $shooting->status,
                        'status_text' => $shooting->status === 'completed' ? 'Complété' : ($shooting->status === 'cancelled' ? 'Annulé' : ($shooting->isOverdue() ? 'En retard' : ($shooting->isUpcoming() ? 'À venir' : 'En attente'))),
                        'description' => $shooting->description,
                        'content_ideas' => $shooting->contentIdeas->map(fn($ci) => $ci->titre)->toArray(),
                        'is_overdue' => $shooting->isOverdue(),
                        'is_upcoming' => $shooting->isUpcoming(),
                        'is_completed' => $shooting->isCompleted(),
                    ];
                });
            
            $publications = \App\Models\Publication::whereDate('date', $date)
                ->where('client_id', $clientId)
                ->with(['contentIdea', 'shooting'])
                ->orderBy('date')
                ->get()
                ->map(function($publication) {
                    return [
                        'id' => $publication->id,
                        'date' => $publication->date->format('Y-m-d'),
                        'status' => $publication->status,
                        'status_text' => $publication->status === 'completed' ? 'Complétée' : ($publication->status === 'cancelled' ? 'Annulée' : ($publication->isOverdue() ? 'En retard' : ($publication->isUpcoming() ? 'À venir' : 'En attente'))),
                        'description' => $publication->description,
                        'content_idea_titre' => $publication->contentIdea->titre ?? '',
                        'shooting_date' => $publication->shooting ? $publication->shooting->date->format('d/m/Y') : null,
                        'is_overdue' => $publication->isOverdue(),
                        'is_upcoming' => $publication->isUpcoming(),
                        'is_completed' => $publication->isCompleted(),
                    ];
                });
            
            return response()->json([
                'date' => $date,
                'shootings' => $shootings,
                'publications' => $publications
            ]);
        });
        
        Route::get('client-calendar', function () {
            $month = (int) request('month');
            $year = (int) request('year');
            $clientId = (int) request('client_id');
            
            if (!$month || !$year || !$clientId) {
                return response()->json(['error' => 'Paramètres manquants'], 400);
            }
            
            $client = \App\Models\Client::findOrFail($clientId);
            $controller = new \App\Http\Controllers\ClientController();
            
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
            $calendar = $controller->buildClientCalendar($startDate, $shootings, $publications);
            
            // Générer le HTML du calendrier (table uniquement)
            $html = view('clients.partials.calendar-table', compact('calendar'))->render();
            
            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            
            return response()->json([
                'html' => $html,
                'month_name' => $months[$month],
                'year' => $year
            ]);
        });
        
        Route::get('admin-calendar', [DashboardController::class, 'getCalendarData'])->name('api.admin-calendar');
        
        Route::get('events-by-date', function () {
            $date = request('date');
            
            if (!$date) {
                return response()->json(['shootings' => [], 'publications' => []]);
            }
            
            $shootings = \App\Models\Shooting::whereDate('date', $date)
                ->with(['client', 'contentIdeas'])
                ->orderBy('date')
                ->get()
                ->map(function($shooting) {
                    return [
                        'id' => $shooting->id,
                        'client_id' => $shooting->client_id,
                        'client_name' => $shooting->client->nom_entreprise,
                        'date' => $shooting->date->format('Y-m-d'),
                        'status' => $shooting->status,
                        'description' => $shooting->description,
                        'content_ideas' => $shooting->contentIdeas->map(fn($ci) => ['id' => $ci->id, 'titre' => $ci->titre]),
                        'is_overdue' => $shooting->isOverdue(),
                        'is_upcoming' => $shooting->isUpcoming(),
                        'is_completed' => $shooting->isCompleted(),
                        'url' => route('shootings.show', $shooting),
                        'edit_url' => route('shootings.edit', $shooting),
                        'delete_url' => route('shootings.destroy', $shooting),
                    ];
                });
            
            $publications = \App\Models\Publication::whereDate('date', $date)
                ->with(['client', 'contentIdea', 'shooting'])
                ->orderBy('date')
                ->get()
                ->map(function($publication) {
                    return [
                        'id' => $publication->id,
                        'client_id' => $publication->client_id,
                        'client_name' => $publication->client->nom_entreprise,
                        'date' => $publication->date->format('Y-m-d'),
                        'status' => $publication->status,
                        'description' => $publication->description,
                        'content_idea_id' => $publication->content_idea_id,
                        'content_idea_titre' => $publication->contentIdea->titre ?? '',
                        'shooting_id' => $publication->shooting_id,
                        'shooting_date' => $publication->shooting ? $publication->shooting->date->format('Y-m-d') : null,
                        'is_overdue' => $publication->isOverdue(),
                        'is_upcoming' => $publication->isUpcoming(),
                        'is_completed' => $publication->isCompleted(),
                        'url' => route('publications.show', $publication),
                        'edit_url' => route('publications.edit', $publication),
                        'delete_url' => route('publications.destroy', $publication),
                    ];
                });
            
            return response()->json([
                'date' => $date,
                'shootings' => $shootings,
                'publications' => $publications
            ]);
        });
        
        Route::get('check-date', function () {
            $clientId = request('client_id');
            $date = request('date');
            $type = request('type', 'publication'); // 'publication' ou 'shooting'
            
            if (!$date) {
                return response()->json(['available' => true, 'conflicts' => [], 'warnings' => []]);
            }
            
            $checkDate = \Carbon\Carbon::parse($date);
            $dayOfWeek = strtolower($checkDate->format('l'));
            $days = ['monday' => 'lundi', 'tuesday' => 'mardi', 'wednesday' => 'mercredi', 
                     'thursday' => 'jeudi', 'friday' => 'vendredi', 'saturday' => 'samedi', 'sunday' => 'dimanche'];
            $dayInFrench = $days[$dayOfWeek] ?? $dayOfWeek;
            
            $warnings = [];
            $conflicts = [];
            
            // Vérifier TOUS les événements à cette date (tournages ET publications) - pour TOUS les clients
            // Publications
            $existingPubs = \App\Models\Publication::whereDate('date', $date)
                ->with('client')
                ->get();
            
            foreach ($existingPubs as $existingPub) {
                $isSameClient = $clientId && $existingPub->client_id == $clientId;
                $conflicts[] = [
                    'type' => 'publication',
                    'eventType' => 'publication',
                    'client' => $existingPub->client->nom_entreprise,
                    'isSameClient' => $isSameClient,
                    'message' => $isSameClient 
                        ? 'Une publication existe déjà pour ce client (' . $existingPub->client->nom_entreprise . ') le ' . $checkDate->format('d/m/Y')
                        : 'Une publication est prévue pour le client "' . $existingPub->client->nom_entreprise . '" le ' . $checkDate->format('d/m/Y'),
                    'id' => $existingPub->id,
                    'url' => route('publications.show', $existingPub)
                ];
            }
            
            // Tournages
            $existingShootings = \App\Models\Shooting::whereDate('date', $date)
                ->with('client')
                ->get();
            
            foreach ($existingShootings as $existingShooting) {
                $isSameClient = $clientId && $existingShooting->client_id == $clientId;
                $conflicts[] = [
                    'type' => 'shooting',
                    'eventType' => 'tournage',
                    'client' => $existingShooting->client->nom_entreprise,
                    'isSameClient' => $isSameClient,
                    'message' => $isSameClient
                        ? 'Un tournage existe déjà pour ce client (' . $existingShooting->client->nom_entreprise . ') le ' . $checkDate->format('d/m/Y')
                        : 'Un tournage est prévu pour le client "' . $existingShooting->client->nom_entreprise . '" le ' . $checkDate->format('d/m/Y'),
                    'id' => $existingShooting->id,
                    'url' => route('shootings.show', $existingShooting)
                ];
            }
            
            // Vérifier si le jour est non recommandé (seulement pour les publications et si client sélectionné)
            if ($type === 'publication' && $clientId) {
                $client = \App\Models\Client::find($clientId);
                if ($client && $client->isDayNotRecommended($dayInFrench)) {
                    $warnings[] = 'Ce jour (' . ucfirst($dayInFrench) . ') est non recommandé pour la publication pour ce client.';
                }
            }
            
            return response()->json([
                'available' => empty($warnings) && empty($conflicts),
                'warnings' => $warnings,
                'conflicts' => $conflicts
            ]);
        });
    });
});

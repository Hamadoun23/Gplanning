<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ContentIdea;
use App\Models\Publication;
use App\Models\Shooting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicationController extends Controller
{
    /**
     * Affiche le calendrier des publications
     */
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        // Créer la date du premier jour du mois
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer toutes les publications du mois
        $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return Carbon::parse($publication->date)->format('Y-m-d');
            });

        // Préparer le calendrier
        $calendar = $this->buildCalendar($startDate, $publications);

        $clients = Client::orderBy('nom_entreprise')->get();

        return $this->viewForRole('publications.index', compact('calendar', 'publications', 'clients', 'month', 'year', 'startDate'));
    }

    /**
     * Récupère les données du calendrier via AJAX
     */
    public function getCalendarData(Request $request)
    {
        try {
            $month = (int) $request->get('month', now()->month);
            $year = (int) $request->get('year', now()->year);

            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();

            $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->with(['client', 'contentIdea', 'shooting'])
                ->orderBy('date')
                ->get()
                ->groupBy(function($publication) {
                    return Carbon::parse($publication->date)->format('Y-m-d');
                });

            $calendar = $this->buildCalendar($startDate, $publications);

            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            
            $isTeamReadOnly = auth()->check() && auth()->user()->isTeam();

            return response()->json([
                'html' => view('publications.partials.calendar-table', compact('calendar', 'isTeamReadOnly'))->render(),
                'month' => $month,
                'year' => $year,
                'monthName' => $months[$month],
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getCalendarData: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'html' => '<p style="color: #dc3545; padding: 2rem; text-align: center;">Erreur lors du chargement du calendrier: ' . $e->getMessage() . '</p>',
                'month' => $request->get('month', now()->month),
                'year' => $request->get('year', now()->year),
                'monthName' => 'Erreur',
            ], 500);
        }
    }

    /**
     * Construit le calendrier pour un mois donné
     */
    private function buildCalendar(Carbon $startDate, $publications)
    {
        $calendar = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);

        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $dayPublications = $publications->get($dateKey, collect());
                
                // Vérifier les avertissements pour chaque publication
                $warnings = [];
                foreach ($dayPublications as $pub) {
                    try {
                        if ($pub->client) {
                            $date = Carbon::parse($pub->date);
                            $dayOfWeek = $this->getDayOfWeekInFrench($date);
                            if ($pub->client->isDayNotRecommended($dayOfWeek)) {
                                $warnings[] = $pub->id;
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignorer les erreurs pour une publication individuelle
                        continue;
                    }
                }
                
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'publications' => $dayPublications,
                    'hasWarnings' => !empty($warnings),
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Exporte le calendrier des publications en Excel
     */
    public function exportCalendar(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $publications = Publication::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdea', 'shooting'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($publication) {
                return Carbon::parse($publication->date)->format('Y-m-d');
            });

        $calendar = $this->buildCalendar($startDate, $publications);
        
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $filename = 'calendrier_publications_' . $months[$month] . '_' . $year . '.csv';
        
        $csv = $this->generateCalendarCSV($calendar, $months[$month], $year);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    private function generateCalendarCSV($calendar, $monthName, $year)
    {
        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Calendrier Publications - ' . $monthName . ' ' . $year], ';');
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
                        
                        $cellContent[] = $icon . ' ' . $publication->client->nom_entreprise;
                        $cellContent[] = '   Statut: ' . $statusText;
                        $cellContent[] = '   Idée: ' . $publication->contentIdea->titre;
                        if ($publication->shooting) {
                            $cellContent[] = '   Tournage lié: ' . $publication->shooting->date->format('d/m/Y H:i');
                        }
                        
                        // Avertissement si jour non recommandé
                        $days = ['Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'];
                        $pubDayOfWeek = $days[Carbon::parse($publication->date)->format('l')] ?? '';
                        if ($publication->client->isDayNotRecommended($pubDayOfWeek)) {
                            $cellContent[] = '   ⚠️ Jour non recommandé pour ce client';
                        }
                    }
                } else {
                    // Jour du mois précédent/suivant
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

    /**
     * Affiche le formulaire de création
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('nom_entreprise')->get();
        $selectedClient = $request->get('client_id');
        $selectedDate = $request->get('date');

        // Toutes les idées de contenu sont disponibles pour tous les clients
        $contentIdeas = ContentIdea::orderBy('titre')->get();

        // Vérifications pour afficher les avertissements
        $warnings = [];
        if ($selectedClient && $selectedDate) {
            $client = Client::find($selectedClient);
            $date = Carbon::parse($selectedDate);
            $dayOfWeek = $this->getDayOfWeekInFrench($date);

            // Vérifier si une publication existe déjà ce jour
            $existingPublication = Publication::where('client_id', $selectedClient)
                ->whereDate('date', $selectedDate)
                ->first();

            if ($existingPublication) {
                $warnings[] = 'Une publication existe déjà pour ce client le ' . $date->format('d/m/Y H:i');
            }

            // Vérifier si le jour est non recommandé
            if ($client && $client->isDayNotRecommended($dayOfWeek)) {
                $warnings[] = 'Ce jour (' . ucfirst($dayOfWeek) . ') est non recommandé pour la publication pour ce client.';
            }
        }

        return view('publications.create', compact('clients', 'selectedClient', 'selectedDate', 'contentIdeas', 'warnings'));
    }

    /**
     * Enregistre une nouvelle publication
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date'],
            'content_idea_id' => ['required', 'exists:content_ideas,id'],
            'description' => ['nullable', 'string'],
        ]);

        // Vérifications (avertissements mais pas de blocage)
        $client = Client::find($validated['client_id']);
        $date = Carbon::parse($validated['date']);
        $dayOfWeek = $this->getDayOfWeekInFrench($date);

        $warnings = [];
        
        // Vérifier si une publication existe déjà ce jour
        $existingPublication = Publication::where('client_id', $validated['client_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if ($existingPublication) {
            $warnings[] = 'Une publication existe déjà pour ce client le ' . $date->format('d/m/Y');
        }

        // Vérifier si le jour est non recommandé
        if ($client && $client->isDayNotRecommended($dayOfWeek)) {
            $warnings[] = 'Ce jour (' . ucfirst($dayOfWeek) . ') est non recommandé pour la publication pour ce client.';
        }

        // Créer la publication même s'il y a des avertissements
        // Utiliser la date parsée avec Carbon pour s'assurer que l'heure est bien incluse
        $publication = Publication::create([
            'client_id' => $validated['client_id'],
            'date' => $date,
            'content_idea_id' => $validated['content_idea_id'],
            'description' => $validated['description'] ?? null,
        ]);

        $message = 'Publication créée avec succès.';
        if (!empty($warnings)) {
            $message .= ' Avertissements : ' . implode(' ', $warnings);
        }

        // Toujours rediriger vers le dashboard principal
        $date = Carbon::parse($validated['date']);
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', $message)
            ->with('warnings', $warnings);
    }

    /**
     * Affiche les détails d'une publication
     */
    public function show(Publication $publication)
    {
        $publication->load(['client', 'contentIdea', 'shooting']);
        return $this->viewForRole('publications.show', compact('publication'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Request $request, Publication $publication)
    {
        // Effacer complètement la session old() pour forcer l'utilisation des données de la DB
        $request->session()->forget('_old_input');
        
        // Recharger la publication depuis la base de données pour s'assurer d'avoir les données à jour
        $publication->refresh();
        
        // Charger toutes les relations nécessaires
        $publication->load(['client', 'contentIdea', 'shooting']);
        $clients = Client::orderBy('nom_entreprise')->get();
        // Toutes les idées de contenu sont disponibles pour tous les clients
        $contentIdeas = ContentIdea::orderBy('titre')->get();

        // Initialiser les warnings si nécessaire
        $warnings = [];

        return view('publications.edit', compact('publication', 'clients', 'contentIdeas', 'warnings'));
    }

    /**
     * Met à jour une publication
     */
    public function update(Request $request, Publication $publication)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date'],
            'content_idea_id' => ['required', 'exists:content_ideas,id'],
            'description' => ['nullable', 'string'],
        ], [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date doit être une date valide.',
            'content_idea_id.required' => 'L\'idée de contenu est obligatoire.',
            'content_idea_id.exists' => 'L\'idée de contenu sélectionnée n\'existe pas.',
        ]);

        // Vérifications (avertissements mais pas de blocage)
        $client = Client::find($validated['client_id']);
        $date = Carbon::parse($validated['date']);
        $dayOfWeek = $this->getDayOfWeekInFrench($date);

        $warnings = [];
        
        // Vérifier si une publication existe déjà ce jour (sauf celle qu'on modifie)
        $existingPublication = Publication::where('client_id', $validated['client_id'])
            ->whereDate('date', $validated['date'])
            ->where('id', '!=', $publication->id)
            ->first();

        if ($existingPublication) {
            $warnings[] = 'Une publication existe déjà pour ce client le ' . $date->format('d/m/Y');
        }

        // Vérifier si le jour est non recommandé
        if ($client && $client->isDayNotRecommended($dayOfWeek)) {
            $warnings[] = 'Ce jour (' . ucfirst($dayOfWeek) . ') est non recommandé pour la publication pour ce client.';
        }

        // Utiliser la date parsée avec Carbon pour s'assurer que l'heure est bien incluse
        $publication->update([
            'client_id' => $validated['client_id'],
            'date' => $date,
            'content_idea_id' => $validated['content_idea_id'],
            'description' => $validated['description'] ?? null,
        ]);

        $message = 'Publication modifiée avec succès.';
        if (!empty($warnings)) {
            $message .= ' Avertissements : ' . implode(' ', $warnings);
        }

        // Toujours rediriger vers le dashboard principal
        $date = Carbon::parse($validated['date']);
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', $message)
            ->with('warnings', $warnings);
    }

    /**
     * Supprime une publication
     */
    public function destroy(Request $request, Publication $publication)
    {
        $date = $publication->date;
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        $publication->delete();

        // Toujours rediriger vers le dashboard principal
        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', 'Publication supprimée avec succès.');
    }

    /**
     * Change le statut d'une publication
     */
    public function toggleStatus(Request $request, Publication $publication)
    {
        $status = $request->input('status', 'completed');
        $statusReason = $request->input('status_reason');
        
        // Statuts qui nécessitent une description obligatoire
        $statusesRequiringReason = ['not_realized', 'cancelled', 'rescheduled'];
        
        if (!in_array($status, ['pending', 'completed', 'not_realized', 'cancelled', 'rescheduled'])) {
            return back()->withErrors(['status' => 'Le statut sélectionné est invalide.']);
        }

        // Vérifier que la description est fournie pour les statuts qui le nécessitent
        if (in_array($status, $statusesRequiringReason) && empty($statusReason)) {
            return back()->withErrors(['status_reason' => 'Une description est obligatoire pour ce statut.']);
        }

        // Si le statut est "rescheduled", modifier directement la date de la publication existante
        if ($status === 'rescheduled') {
            // Vérifier que la date est fournie
            if (!$request->has('reschedule_date') || empty($request->input('reschedule_date'))) {
                return back()->withErrors(['reschedule_date' => 'La nouvelle date est obligatoire pour reprogrammer une publication.']);
            }
            
            $newDate = $request->input('reschedule_date');
            
            // Valider la date
            $validated = $request->validate([
                'reschedule_date' => ['required', 'date'],
            ], [
                'reschedule_date.required' => 'La nouvelle date est obligatoire.',
                'reschedule_date.date' => 'La date doit être une date valide.',
            ]);
            
            // Sauvegarder l'ancienne date pour la raison
            $oldDate = $publication->date->format('d/m/Y H:i');
            
            // Mettre à jour la publication avec la nouvelle date et le statut
            $publication->date = $newDate;
            $publication->status = 'pending'; // Remettre en attente avec la nouvelle date
            $publication->status_reason = $statusReason . ' - Ancienne date : ' . $oldDate . ' - Nouvelle date : ' . \Carbon\Carbon::parse($newDate)->format('d/m/Y H:i');
            $publication->save();
            
            // Rester sur la page de la publication modifiée
            return redirect()->route('publications.show', $publication)
                ->with('success', 'Publication reprogrammée avec succès. La date a été modifiée.');
        }

        // Pour les autres statuts, mettre à jour normalement
        $publication->status = $status;
        $publication->status_reason = in_array($status, $statusesRequiringReason) ? $statusReason : null;
        $publication->save();

        $messages = [
            'completed' => 'Publication marquée comme complétée.',
            'pending' => 'Publication marquée comme en attente.',
            'not_realized' => 'Publication marquée comme non réalisée.',
            'cancelled' => 'Publication marquée comme annulée.',
        ];

        // Rester sur la page actuelle au lieu de rediriger vers le dashboard
        return redirect()->route('publications.show', $publication)
            ->with('success', $messages[$status]);
    }

    /**
     * Reprogramme une publication (crée une nouvelle avec une nouvelle date)
     */
    public function reschedule(Request $request, Publication $publication)
    {
        $validated = $request->validate([
            'new_date' => ['required', 'date'],
        ], [
            'new_date.required' => 'La nouvelle date est obligatoire.',
            'new_date.date' => 'La nouvelle date doit être une date valide.',
        ]);

        // Créer une nouvelle publication avec la nouvelle date
        $newPublication = Publication::create([
            'client_id' => $publication->client_id,
            'date' => $validated['new_date'],
            'content_idea_id' => $publication->content_idea_id,
            'status' => 'pending',
        ]);

        // Marquer l'ancienne comme reprogrammée
        $publication->status = 'rescheduled';
        $publication->status_reason = 'Reprogrammée - Nouvelle date : ' . $validated['new_date'];
        $publication->save();

        return redirect()->route('publications.show', $newPublication)
            ->with('success', 'Publication reprogrammée avec succès.');
    }

    /**
     * Convertit le jour de la semaine en français
     */
    private function getDayOfWeekInFrench(Carbon $date): string
    {
        $days = [
            'Monday' => 'lundi',
            'Tuesday' => 'mardi',
            'Wednesday' => 'mercredi',
            'Thursday' => 'jeudi',
            'Friday' => 'vendredi',
            'Saturday' => 'samedi',
            'Sunday' => 'dimanche',
        ];

        return $days[$date->format('l')] ?? strtolower($date->format('l'));
    }
}

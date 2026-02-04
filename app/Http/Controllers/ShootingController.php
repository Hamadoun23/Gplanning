<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shooting;
use App\Models\ContentIdea;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShootingController extends Controller
{
    /**
     * Affiche le calendrier des tournages
     */
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        // Créer la date du premier jour du mois
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Récupérer tous les tournages du mois
        $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdeas'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return Carbon::parse($shooting->date)->format('Y-m-d');
            });

        // Préparer le calendrier
        $calendar = $this->buildCalendar($startDate, $shootings);

        $clients = Client::orderBy('nom_entreprise')->get();

        return $this->viewForRole('shootings.index', compact('calendar', 'shootings', 'clients', 'month', 'year', 'startDate'));
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

            $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->with(['client', 'contentIdeas'])
                ->orderBy('date')
                ->get()
                ->groupBy(function($shooting) {
                    return Carbon::parse($shooting->date)->format('Y-m-d');
                });

            $calendar = $this->buildCalendar($startDate, $shootings);

            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            
            $isTeamReadOnly = auth()->check() && auth()->user()->isTeam();

            return response()->json([
                'html' => view('shootings.partials.calendar-table', compact('calendar', 'isTeamReadOnly'))->render(),
                'month' => $month,
                'year' => $year,
                'monthName' => $months[$month],
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getCalendarData shootings: ' . $e->getMessage());
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
    private function buildCalendar(Carbon $startDate, $shootings)
    {
        $calendar = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);

        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $dayShootings = $shootings->get($dateKey, collect());
                
                // Vérifier les avertissements pour chaque tournage
                $warnings = [];
                foreach ($dayShootings as $shooting) {
                    try {
                        if ($shooting->client) {
                            $date = Carbon::parse($shooting->date);
                            $dayOfWeek = $this->getDayOfWeekInFrench($date);
                            if ($shooting->client->isDayNotRecommended($dayOfWeek)) {
                                $warnings[] = $shooting->id;
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignorer les erreurs pour un tournage individuel
                        continue;
                    }
                }
                
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'shootings' => $dayShootings,
                    'hasWarnings' => !empty($warnings),
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
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

        return view('shootings.create', compact('clients', 'selectedClient', 'selectedDate', 'contentIdeas'));
    }

    /**
     * Enregistre un nouveau tournage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date'],
            'content_idea_id' => ['required', 'exists:content_ideas,id'],
            'description' => ['nullable', 'string'],
        ], [
            'content_idea_id.required' => 'L\'idée de contenu est obligatoire.',
            'content_idea_id.exists' => 'L\'idée de contenu sélectionnée n\'existe pas.',
        ]);

        // Parser la date avec Carbon pour s'assurer que l'heure est bien incluse
        $date = Carbon::parse($validated['date']);
        
        $shooting = Shooting::create([
            'client_id' => $validated['client_id'],
            'date' => $date,
            'description' => $validated['description'] ?? null,
        ]);

        $shooting->contentIdeas()->attach($validated['content_idea_id']);

        // Toujours rediriger vers le dashboard principal
        $date = Carbon::parse($validated['date']);
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        // Si on demande de créer une publication après, on redirige vers le formulaire de création de publication
        if ($request->input('action') === 'create_and_publish') {
            return redirect()->route('publications.create', [
                'client_id' => $shooting->client_id,
                'date' => $shooting->date->format('Y-m-d'),
                'shooting_id' => $shooting->id,
                'return_to_dashboard' => 1,
                'return_month' => $month,
                'return_year' => $year
            ])->with('success', 'Tournage créé avec succès. Vous pouvez maintenant créer la publication associée.');
        }

        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', 'Tournage créé avec succès.');
    }

    /**
     * Affiche les détails d'un tournage
     */
    public function show(Shooting $shooting)
    {
        $shooting->load(['client', 'contentIdeas']);
        return $this->viewForRole('shootings.show', compact('shooting'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Request $request, $id)
    {
        // Effacer complètement la session old() pour forcer l'utilisation des données de la DB
        $request->session()->forget('_old_input');
        
        // Charger le shooting directement depuis la DB avec l'ID de l'URL
        $shooting = Shooting::with(['client', 'contentIdeas'])->findOrFail($id);
        
        $clients = Client::orderBy('nom_entreprise')->get();
        // Toutes les idées de contenu sont disponibles pour tous les clients
        $contentIdeas = ContentIdea::orderBy('titre')->get();

        return view('shootings.edit', compact('shooting', 'clients', 'contentIdeas'));
    }

    /**
     * Met à jour un tournage
     */
    public function update(Request $request, Shooting $shooting)
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

        // Parser la date avec Carbon pour s'assurer que l'heure est bien incluse
        $date = Carbon::parse($validated['date']);
        
        $shooting->update([
            'client_id' => $validated['client_id'],
            'date' => $date,
            'description' => $validated['description'] ?? null,
        ]);

        $shooting->contentIdeas()->sync([$validated['content_idea_id']]);

        // Toujours rediriger vers le dashboard principal
        $date = Carbon::parse($validated['date']);
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', 'Tournage modifié avec succès.');
    }

    /**
     * Supprime un tournage
     */
    public function destroy(Request $request, Shooting $shooting)
    {
        $date = $shooting->date;
        $month = $request->get('return_month', $date->month);
        $year = $request->get('return_year', $date->year);
        
        $shooting->delete();

        // Toujours rediriger vers le dashboard principal
        return redirect()->route('dashboard', ['month' => $month, 'year' => $year])
            ->with('success', 'Tournage supprimé avec succès.');
    }

    /**
     * Change le statut d'un tournage
     */
    public function toggleStatus(Request $request, Shooting $shooting)
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

        // Si le statut est "rescheduled", modifier directement la date du tournage existant
        if ($status === 'rescheduled') {
            // Vérifier que la date est fournie
            if (!$request->has('reschedule_date') || empty($request->input('reschedule_date'))) {
                return back()->withErrors(['reschedule_date' => 'La nouvelle date est obligatoire pour reprogrammer un tournage.']);
            }
            
            $newDate = $request->input('reschedule_date');
            
            // Valider la date
            $validated = $request->validate([
                'reschedule_date' => ['required', 'date', 'after_or_equal:today'],
            ], [
                'reschedule_date.required' => 'La nouvelle date est obligatoire.',
                'reschedule_date.date' => 'La date doit être une date valide.',
                'reschedule_date.after_or_equal' => 'La nouvelle date doit être aujourd\'hui ou dans le futur.',
            ]);
            
            // Sauvegarder l'ancienne date pour la raison
            $oldDate = $shooting->date->format('d/m/Y H:i');
            
            // Mettre à jour le tournage avec la nouvelle date et le statut
            $shooting->date = $newDate;
            $shooting->status = 'pending'; // Remettre en attente avec la nouvelle date
            $shooting->status_reason = $statusReason . ' - Ancienne date : ' . $oldDate . ' - Nouvelle date : ' . \Carbon\Carbon::parse($newDate)->format('d/m/Y H:i');
            $shooting->save();
            
            // Rester sur la page du tournage modifié
            return redirect()->route('shootings.show', $shooting)
                ->with('success', 'Tournage reprogrammé avec succès. La date a été modifiée.');
        }

        // Pour les autres statuts, mettre à jour normalement
        $shooting->status = $status;
        $shooting->status_reason = in_array($status, $statusesRequiringReason) ? $statusReason : null;
        $shooting->save();

        $messages = [
            'completed' => 'Tournage marqué comme complété.',
            'pending' => 'Tournage marqué comme en attente.',
            'not_realized' => 'Tournage marqué comme non réalisé.',
            'cancelled' => 'Tournage marqué comme annulé.',
        ];

        // Rester sur la page actuelle au lieu de rediriger vers le dashboard
        return redirect()->route('shootings.show', $shooting)
            ->with('success', $messages[$status]);
    }

    /**
     * Reprogramme un tournage (crée un nouveau avec une nouvelle date)
     */
    public function reschedule(Request $request, Shooting $shooting)
    {
        $validated = $request->validate([
            'new_date' => ['required', 'date'],
        ], [
            'new_date.required' => 'La nouvelle date est obligatoire.',
            'new_date.date' => 'La nouvelle date doit être une date valide.',
        ]);

        // Créer un nouveau tournage avec la nouvelle date
        $newShooting = Shooting::create([
            'client_id' => $shooting->client_id,
            'date' => $validated['new_date'],
            'status' => 'pending',
        ]);

        // Copier l'idée de contenu associée (première si plusieurs)
        $firstContentIdea = $shooting->contentIdeas->first();
        if ($firstContentIdea) {
            $newShooting->contentIdeas()->attach($firstContentIdea->id);
        }

        // Marquer l'ancien comme annulé
        $shooting->status = 'cancelled';
        $shooting->save();

        return redirect()->route('shootings.show', $newShooting)
            ->with('success', 'Tournage reprogrammé avec succès.');
    }

    /**
     * Exporte le calendrier des tournages en Excel
     */
    public function exportCalendar(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $shootings = Shooting::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['client', 'contentIdeas'])
            ->orderBy('date')
            ->get()
            ->groupBy(function($shooting) {
                return Carbon::parse($shooting->date)->format('Y-m-d');
            });

        $calendar = $this->buildCalendar($startDate, $shootings);
        
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $filename = 'calendrier_tournages_' . $months[$month] . '_' . $year . '.csv';
        
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
        
        fputcsv($output, ['Calendrier Tournages - ' . $monthName . ' ' . $year], ';');
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
                        
                        $cellContent[] = $icon . ' ' . $shooting->client->nom_entreprise;
                        $cellContent[] = '   Statut: ' . $statusText;
                        if ($shooting->contentIdeas->count() > 0) {
                            $cellContent[] = '   Idées de contenu (' . $shooting->contentIdeas->count() . '):';
                            foreach ($shooting->contentIdeas as $idea) {
                                $cellContent[] = '     • ' . $idea->titre;
                            }
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
}

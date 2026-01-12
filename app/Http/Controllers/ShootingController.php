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

        return view('shootings.index', compact('calendar', 'shootings', 'clients', 'month', 'year', 'startDate'));
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
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'shootings' => $shootings->get($dateKey, collect()),
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return $calendar;
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

        $shooting = Shooting::create([
            'client_id' => $validated['client_id'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
        ]);

        $shooting->contentIdeas()->attach($validated['content_idea_id']);

        // Redirection intelligente : si on vient d'un calendrier avec une date, on y retourne
        if ($request->has('return_to_calendar')) {
            $date = Carbon::parse($validated['date']);
            return redirect()->route('shootings.index', ['month' => $date->month, 'year' => $date->year])
                ->with('success', 'Tournage créé avec succès.');
        }

        // Si on demande de créer une publication après, on redirige vers le formulaire de création de publication
        if ($request->input('action') === 'create_and_publish') {
            return redirect()->route('publications.create', [
                'client_id' => $shooting->client_id,
                'date' => $shooting->date->format('Y-m-d'),
                'shooting_id' => $shooting->id
            ])->with('success', 'Tournage créé avec succès. Vous pouvez maintenant créer la publication associée.');
        }

        return redirect()->route('shootings.show', $shooting)
            ->with('success', 'Tournage créé avec succès.');
    }

    /**
     * Affiche les détails d'un tournage
     */
    public function show(Shooting $shooting)
    {
        $shooting->load(['client', 'contentIdeas']);
        return view('shootings.show', compact('shooting'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Shooting $shooting)
    {
        $shooting->load('contentIdeas');
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

        $shooting->update([
            'client_id' => $validated['client_id'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
        ]);

        $shooting->contentIdeas()->sync([$validated['content_idea_id']]);

        return redirect()->route('shootings.show', $shooting)
            ->with('success', 'Tournage modifié avec succès.');
    }

    /**
     * Supprime un tournage
     */
    public function destroy(Request $request, Shooting $shooting)
    {
        $clientId = $shooting->client_id;
        $shooting->delete();

        // Si on vient de la page du client, on y retourne
        if ($request->has('return_to_client')) {
            return redirect()->route('clients.show', $clientId)
                ->with('success', 'Tournage supprimé avec succès.');
        }

        return redirect()->route('shootings.index')
            ->with('success', 'Tournage supprimé avec succès.');
    }

    /**
     * Change le statut d'un tournage
     */
    public function toggleStatus(Request $request, Shooting $shooting)
    {
        $status = $request->input('status', 'completed');
        
        if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
            return back()->withErrors(['status' => 'Le statut sélectionné est invalide.']);
        }

        $shooting->status = $status;
        $shooting->save();

        $messages = [
            'completed' => 'Tournage marqué comme complété.',
            'pending' => 'Tournage marqué comme en attente.',
            'cancelled' => 'Tournage marqué comme échec/annulé.',
        ];

        return back()->with('success', $messages[$status]);
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

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

        return view('publications.index', compact('calendar', 'publications', 'clients', 'month', 'year', 'startDate'));
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
                    $date = Carbon::parse($pub->date);
                    $dayOfWeek = $this->getDayOfWeekInFrench($date);
                    if ($pub->client->isDayNotRecommended($dayOfWeek)) {
                        $warnings[] = $pub->id;
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
        $selectedShootingId = $request->get('shooting_id');

        // Toutes les idées de contenu sont disponibles pour tous les clients
        $contentIdeas = ContentIdea::orderBy('titre')->get();

        // Récupérer uniquement les tournages disponibles (qui n'ont pas déjà de publication liée)
        // ou le tournage sélectionné si fourni
        $shootings = $selectedClient
            ? Shooting::where('client_id', $selectedClient)
                ->where('date', '<=', $selectedDate ?? now())
                ->where(function($query) use ($selectedShootingId) {
                    $query->whereDoesntHave('publications');
                    if ($selectedShootingId) {
                        $query->orWhere('id', $selectedShootingId);
                    }
                })
                ->orderBy('date', 'desc')
                ->get()
            : collect();

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
                $warnings[] = 'Une publication existe déjà pour ce client le ' . $date->format('d/m/Y');
            }

            // Vérifier si le jour est non recommandé
            if ($client && $client->isDayNotRecommended($dayOfWeek)) {
                $warnings[] = 'Ce jour (' . ucfirst($dayOfWeek) . ') est non recommandé pour la publication pour ce client.';
            }
        }

        return view('publications.create', compact('clients', 'selectedClient', 'selectedDate', 'contentIdeas', 'shootings', 'warnings'));
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
            'shooting_id' => ['nullable', 'exists:shootings,id'],
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
        $publication = Publication::create($validated);

        $message = 'Publication créée avec succès.';
        if (!empty($warnings)) {
            $message .= ' Avertissements : ' . implode(' ', $warnings);
        }

        // Redirection intelligente : si on vient d'un calendrier avec une date, on y retourne
        if ($request->has('return_to_calendar')) {
            $date = Carbon::parse($validated['date']);
            return redirect()->route('publications.index', ['month' => $date->month, 'year' => $date->year])
                ->with('success', $message)
                ->with('warnings', $warnings);
        }

        return redirect()->route('publications.show', $publication)
            ->with('success', $message)
            ->with('warnings', $warnings);
    }

    /**
     * Affiche les détails d'une publication
     */
    public function show(Publication $publication)
    {
        $publication->load(['client', 'contentIdea', 'shooting']);
        return view('publications.show', compact('publication'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Publication $publication)
    {
        $publication->load(['client', 'contentIdea', 'shooting']);
        $clients = Client::orderBy('nom_entreprise')->get();
        // Toutes les idées de contenu sont disponibles pour tous les clients
        $contentIdeas = ContentIdea::orderBy('titre')->get();
        // Récupérer uniquement les tournages disponibles (qui n'ont pas déjà de publication liée)
        // ou le tournage actuellement lié à cette publication
        $shootings = Shooting::where('client_id', $publication->client_id)
            ->where(function($query) use ($publication) {
                $query->whereDoesntHave('publications')
                      ->orWhere('id', $publication->shooting_id);
            })
            ->orderBy('date', 'desc')
            ->get();

        return view('publications.edit', compact('publication', 'clients', 'contentIdeas', 'shootings'));
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
            'shooting_id' => ['nullable', 'exists:shootings,id'],
            'description' => ['nullable', 'string'],
        ], [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date doit être une date valide.',
            'content_idea_id.required' => 'L\'idée de contenu est obligatoire.',
            'content_idea_id.exists' => 'L\'idée de contenu sélectionnée n\'existe pas.',
            'shooting_id.exists' => 'Le tournage sélectionné n\'existe pas.',
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

        $publication->update($validated);

        $message = 'Publication modifiée avec succès.';
        if (!empty($warnings)) {
            $message .= ' Avertissements : ' . implode(' ', $warnings);
        }

        return redirect()->route('publications.show', $publication)
            ->with('success', $message)
            ->with('warnings', $warnings);
    }

    /**
     * Supprime une publication
     */
    public function destroy(Request $request, Publication $publication)
    {
        $clientId = $publication->client_id;
        $publication->delete();

        // Si on vient de la page du client, on y retourne
        if ($request->has('return_to_client')) {
            return redirect()->route('clients.show', $clientId)
                ->with('success', 'Publication supprimée avec succès.');
        }

        return redirect()->route('publications.index')
            ->with('success', 'Publication supprimée avec succès.');
    }

    /**
     * Change le statut d'une publication
     */
    public function toggleStatus(Request $request, Publication $publication)
    {
        $status = $request->input('status', 'completed');
        
        if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
            return back()->withErrors(['status' => 'Le statut sélectionné est invalide.']);
        }

        $publication->status = $status;
        $publication->save();

        $messages = [
            'completed' => 'Publication marquée comme complétée.',
            'pending' => 'Publication marquée comme en attente.',
            'cancelled' => 'Publication marquée comme échec/annulée.',
        ];

        return back()->with('success', $messages[$status]);
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
            'shooting_id' => $publication->shooting_id,
            'status' => 'pending',
        ]);

        // Marquer l'ancienne comme annulée
        $publication->status = 'cancelled';
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

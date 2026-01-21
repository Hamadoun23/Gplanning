<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PublicationRule;
use Illuminate\Http\Request;

class PublicationRuleController extends Controller
{
    /**
     * Affiche la liste des règles de publication pour un client
     */
    public function index(Client $client)
    {
        $rules = $client->publicationRules()->orderBy('day_of_week')->get();
        return $this->viewForRole('publication-rules.index', compact('client', 'rules'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create(Client $client)
    {
        $daysOfWeek = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        $existingDays = $client->publicationRules()->pluck('day_of_week')->toArray();
        $availableDays = array_diff($daysOfWeek, $existingDays);

        return view('publication-rules.create', compact('client', 'availableDays'));
    }

    /**
     * Enregistre une nouvelle règle de publication
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
        ], [
            'day_of_week.required' => 'Le jour de la semaine est obligatoire.',
            'day_of_week.in' => 'Le jour de la semaine sélectionné est invalide.',
        ]);

        // Vérifier que la règle n'existe pas déjà
        if ($client->publicationRules()->where('day_of_week', $validated['day_of_week'])->exists()) {
            return back()->withErrors(['day_of_week' => 'Cette règle existe déjà pour ce client.']);
        }

        $client->publicationRules()->create($validated);

        // Redirection intelligente : retourner à la page du client après création
        if ($request->has('return_to_client')) {
            return redirect()->route('clients.dashboard', $client)
                ->with('success', 'Règle de publication créée avec succès.');
        }

        return redirect()->route('clients.publication-rules.index', $client)
            ->with('success', 'Règle de publication créée avec succès.');
    }

    /**
     * Supprime une règle de publication
     */
    public function destroy(Client $client, PublicationRule $publicationRule)
    {
        $publicationRule->delete();

        return redirect()->route('clients.publication-rules.index', $client)
            ->with('success', 'Règle de publication supprimée avec succès.');
    }
}

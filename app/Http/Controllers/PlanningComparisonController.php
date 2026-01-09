<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shooting;
use App\Models\Publication;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlanningComparisonController extends Controller
{
    /**
     * Affiche la page de comparaison des plannings
     */
    public function index(Request $request)
    {
        $clients = Client::orderBy('nom_entreprise')->get();
        $selectedClientIds = array_map('intval', $request->get('clients', []));
        
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $comparisonData = [];
        
        if (!empty($selectedClientIds)) {
            foreach ($selectedClientIds as $clientId) {
                $client = Client::find($clientId);
                if (!$client) continue;
                
                $shootings = Shooting::where('client_id', $clientId)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->with('contentIdeas')
                    ->orderBy('date')
                    ->get()
                    ->groupBy(function($shooting) {
                        return Carbon::parse($shooting->date)->format('Y-m-d');
                    });
                
                $publications = Publication::where('client_id', $clientId)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->with(['contentIdea', 'shooting'])
                    ->orderBy('date')
                    ->get()
                    ->groupBy(function($publication) {
                        return Carbon::parse($publication->date)->format('Y-m-d');
                    });
                
                $comparisonData[$clientId] = [
                    'client' => $client,
                    'shootings' => $shootings,
                    'publications' => $publications,
                ];
            }
        }
        
        // Construire le calendrier combiné
        $calendar = $this->buildComparisonCalendar($startDate, $comparisonData);
        
        return view('planning-comparison.index', compact(
            'clients',
            'selectedClientIds',
            'calendar',
            'month',
            'year',
            'startDate',
            'comparisonData'
        ));
    }
    
    /**
     * Construit le calendrier de comparaison
     */
    private function buildComparisonCalendar(Carbon $startDate, $comparisonData)
    {
        $calendar = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);
        
        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $dayData = [];
                
                foreach ($comparisonData as $clientId => $data) {
                    $dayData[$clientId] = [
                        'shootings' => $data['shootings']->get($dateKey, collect()),
                        'publications' => $data['publications']->get($dateKey, collect()),
                    ];
                }
                
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month == $startDate->month,
                    'data' => $dayData,
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }
        
        return $calendar;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    /**
     * Handle an incoming request.
     * Vérifie que les clients n'accèdent qu'à leur propre espace client
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Si l'utilisateur est un admin, il a accès à tout
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Si l'utilisateur est un client, vérifier qu'il accède uniquement à son propre client
        if ($user->isClient()) {
            $client = $request->route('client');
            
            // Si aucun client dans la route, refuser l'accès
            if (!$client) {
                abort(403, 'Accès non autorisé');
            }
            
            // Si c'est un modèle Client, récupérer l'ID
            $clientId = is_object($client) ? $client->id : $client;
            
            // Vérifier que le client_id correspond à celui de l'utilisateur
            if ($user->client_id != $clientId) {
                abort(403, 'Accès non autorisé à cet espace client');
            }
        }
        
        return $next($request);
    }
}

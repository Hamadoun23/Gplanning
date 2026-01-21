<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamReadOnly
{
    /**
     * Handle an incoming request.
     * Bloque toute modification pour les comptes team (lecture seule)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isTeam()) {
            $method = strtoupper($request->method());

            if (!in_array($method, ['GET', 'HEAD'], true)) {
                abort(403, 'Accès en lecture seule pour les membres team');
            }

            $routeName = $request->route()?->getName();
            if ($routeName && (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit'))) {
                abort(403, 'Accès en lecture seule pour les membres team');
            }
        }

        return $next($request);
    }
}

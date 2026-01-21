<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrTeam
{
    /**
     * Handle an incoming request.
     * Vérifie que l'utilisateur est un admin ou un membre d'equipe
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !(Auth::user()->isAdmin() || Auth::user()->isTeam())) {
            abort(403, 'Accès réservé aux administrateurs et membres team');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($user->isAdmin() || $user->isTeam()) {
            return $next($request);
        }
        
        if ($user->isClient()) {
            $client = $request->route('client');
            
            if (!$client) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
            
            $clientId = is_object($client) ? $client->id : $client;
            
            if ($user->client_id != $clientId) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
        }
        
        return $next($request);
    }
}

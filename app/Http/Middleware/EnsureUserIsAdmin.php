<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            // Rediriger vers la page de connexion du panel admin
            return redirect()->route('filament.admin.auth.login');
        }

        // Vérifier si l'utilisateur est un administrateur

        return $next($request);
    }
}

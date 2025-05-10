<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\District; // Adaptez selon votre modèle de tenant
use Symfony\Component\HttpFoundation\Response;

class TenantAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('filament.app.auth.login');
        }

        $user = Auth::user();
        $tenant = $request->route('tenant');

        // Si l'utilisateur est admin, il a accès à tous les tenants
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a accès au tenant
        if (!$tenant || !$user->districts()->where('tenants.id', $tenant->id)->exists()) {
            // L'utilisateur n'a pas accès à ce tenant
            session()->flash('notification', [
                'type' => 'danger',
                'message' => 'Vous n\'avez pas accès à ce tenant.',
            ]);

            // Rediriger vers la sélection de tenant ou le dashboard app
            return redirect()->route('filament.app.pages.dashboard');
        }

        return $next($request);
    }
}

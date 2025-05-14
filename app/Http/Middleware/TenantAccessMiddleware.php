<?php

namespace App\Http\Middleware;

use App\Models\District; // Assurez-vous que c'est le bon modèle pour vos "tenants"
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            // Si vous êtes dans un contexte Filament, la redirection vers la page de login
            // du panel est généralement gérée par le middleware Authenticate de Filament.
            // Cette redirection pourrait être redondante ou entrer en conflit.
            // Considérez de la retirer si le middleware Authenticate de Filament est déjà actif pour ces routes.
            return redirect()->route(config('filament.auth.pages.login')); // ou le nom de la route de login de votre panel app
        }

        $user = Auth::user();
        $tenantSlug = $request->route('tenant'); // Récupère le slug du tenant depuis la route

        // Si l'utilisateur est admin, il a accès à tous les tenants (Districts)
        // Assurez-vous que la méthode isAdmin() existe et fonctionne correctement sur votre modèle User.
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Vérifier si un slug de tenant est présent dans la route
        if (!$tenantSlug) {
            // Si aucun slug n'est fourni, l'utilisateur ne cible pas un tenant spécifique.
            // Cela peut arriver s'il essaie d'accéder à une URL de base du panel.
            // Filament avec tenantMenu(true) devrait gérer la redirection vers un tenant valide
            // ou une page de sélection. S'il arrive ici, c'est peut-être une situation anormale.
            session()->flash('notification', [
                'type' => 'warning',
                'message' => 'Aucun district spécifié.',
            ]);
            // Rediriger vers la page de tableau de bord principale du panel peut causer une boucle
            // si cette page elle-même nécessite un tenant.
            // Rediriger vers le path du panel est souvent plus sûr, Filament gèrera la suite.
            return redirect(filament()->getCurrentPanel()->getPath());
        }

        // Vérifier si l'utilisateur a accès au tenant (District) via le slug
        // Assurez-vous que la relation 'districts' est correctement définie sur votre modèle User
        // et qu'elle lie aux Districts auxquels l'utilisateur a accès.
        if (!$user->districts()->where('districts.slug', $tenantSlug)->exists()) {
            // L'utilisateur n'a pas accès à ce tenant
            session()->flash('notification', [
                'type' => 'danger',
                'message' => 'Vous n\'avez pas accès à ce district.',
            ]);

            // Rediriger vers une page sûre. Le path du panel est une bonne option,
            // Filament tentera de rediriger vers un tenant valide ou la sélection.
            return redirect(filament()->getCurrentPanel()->getPath());
        }

        return $next($request);
    }
}

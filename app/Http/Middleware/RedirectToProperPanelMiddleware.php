<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToProperPanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $role = $user->role;
        $uri = $request->path();

        // For customers, ensure email is verified before allowing access to customer panel/dashboard
        if ($role === 'customer') {
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                // Allow access to admin email verification routes and prompt to avoid loops
                if (
                    str_starts_with($uri, 'email-verification') ||
                    str_starts_with($uri, 'admin/email-verification') ||
                    $request->routeIs('filament.admin.auth.email-verification.*') ||
                    $request->routeIs('verification.*')
                ) {
                    return $next($request);
                }
                return redirect()->route('filament.admin.auth.email-verification.prompt');
            }
            // After email verification, redirect customers to customer panel
            if (str_starts_with($uri, 'admin') && !str_contains($uri, 'email-verification')) {
                return redirect()->route('filament.customer.pages.customer-dashboard');
            }
        }

        // Define dashboard routes per role
        $dashboards = [
            'admin' => 'admin-dashboard',
            'boar-raiser' => 'boar-raiser-dashboard',
            'customer' => 'customer-dashboard',
        ];

        // Define redirect routes for each role
        $redirectRoutes = [
            'admin' => 'filament.admin.pages.admin-dashboard',
            'boar-raiser' => 'filament.admin.pages.boar-raiser-dashboard',
            'customer' => 'filament.customer.pages.customer-dashboard',
        ];

        // Ensure roles are properly defined
        if (!isset($dashboards[$role]) || !isset($redirectRoutes[$role])) {
            abort(403, 'Unauthorized role');
        }

        // Check if the user is accessing a dashboard that doesn't belong to their role
        foreach ($dashboards as $allowedRole => $dashboardSlug) {
            if (
                $role !== $allowedRole &&
                str_contains($uri, $dashboardSlug)
            ) {
                return redirect()->route($redirectRoutes[$role]);
            }
        }

        // Additional check: redirect customers away from admin panel entirely
        if ($role === 'customer' && str_starts_with($uri, 'admin')) {
            return redirect()->route('filament.customer.pages.customer-dashboard');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        $route = $request->route()->getName();
        foreach (config('menu_access') as $key => $option) {
            if (in_array($route, $option['rutas'], true) && $user->canAccessMenu($key)) {
                return $next($request);
            }
        }

        abort(403);
    }
}

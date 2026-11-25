<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JeroenNoten\LaravelAdminLte\AdminLte;
use Symfony\Component\HttpFoundation\Response;

class ResetAdminMenu
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetInstance(AdminLte::class);

        return $next($request);
    }
}

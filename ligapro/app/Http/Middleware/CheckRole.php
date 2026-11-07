<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verifica si el usuario está autenticado y si su rol está incluido en los permitidos
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json([
                'error' => 'Acceso no autorizado. Área protegida para este perfil.'
            ], 403);
        }

        return $next($request);
    }
}

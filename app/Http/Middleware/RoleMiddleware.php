<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response {
        $userRole = strtolower(trim(auth()->user()->adm_privi ?? ''));

        $allowedRoles = array_map(
            fn ($role) => strtolower(trim($role)),
            $roles
        );

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        abort(404);
    }
}
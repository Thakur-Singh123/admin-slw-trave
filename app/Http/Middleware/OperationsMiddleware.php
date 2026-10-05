<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OperationsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (strtolower(trim(auth()->user()->adm_privi ?? '')) !== 'operations') {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

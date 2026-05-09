<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userRole = $request->session()->get('user.role.type')
            ?? $request->session()->get('user.role.name')
            ?? $request->session()->get('user.role');

        if ($userRole !== $role) {
            abort(403);
        }

        return $next($request);
    }
}

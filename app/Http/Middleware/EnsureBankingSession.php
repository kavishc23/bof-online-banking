<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBankingSession
{
    /**
     * Security aspect: centralizes the customer session authentication check
     * so controllers stay focused on MVC request/response orchestration.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('jwt')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

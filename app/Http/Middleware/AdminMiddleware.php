<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('jwt')) {
            return redirect('/login');
        }

        $role = $request->session()->get('user_role')
            ?? $request->session()->get('customer.userRole')
            ?? $request->session()->get('user.userRole')
            ?? 'Customer';

        if ($role !== 'Admin') {
            return redirect('/dashboard')->with('error', 'You are not authorized to access the admin area.');
        }

        return $next($request);
    }
}

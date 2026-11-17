<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->must_change_password) {
            if (! $request->routeIs('password.change.form', 'password.change.update', 'logout')) {
                return redirect()->route('password.change.form');
            }
        }

        return $next($request);
    }
}

<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if ($role == 'master' && !$user->isMaster()) {
            abort(403, 'Unauthorized access. Master role required.');
        }
        
        if ($role == 'administrator' && !$user->isAdministrator()) {
            abort(403, 'Unauthorized access. Administrator role required.');
        }

        return $next($request);
    }
}

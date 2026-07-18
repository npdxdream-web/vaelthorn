<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('admin');

        if (! $user || ! $user->isAtLeastModerator()) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}

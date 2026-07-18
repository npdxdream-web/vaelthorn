<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UseAdminSessionCookie
{
    public function handle(Request $request, Closure $next)
    {
        config(['session.cookie' => 'vaelthorn_admin_session']);

        return $next($request);
    }
}

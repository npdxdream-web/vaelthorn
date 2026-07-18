<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCitySelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $character = $user->character;
        if (! $character) {
            return $next($request);
        }

        // Admins and moderators bypass — they can access everything
        if ($user->isAtLeastModerator()) {
            return $next($request);
        }

        // Active character with no kingdom and level 1+ → must choose city first
        if (
            $character->status === 'active'
            && $character->city_id === null
            && ($character->stats?->level ?? 0) >= 1
        ) {
            return redirect()->route('choose-city');
        }

        return $next($request);
    }
}

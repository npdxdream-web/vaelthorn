<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureKingdomSelected
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

        // Active character with no kingdom and level 1+ → must choose a kingdom first
        if (
            $character->status === 'active'
            && $character->kingdom_id === null
            && ($character->stats?->level ?? 0) >= 1
        ) {
            return redirect()->route('choose-kingdom');
        }

        return $next($request);
    }
}

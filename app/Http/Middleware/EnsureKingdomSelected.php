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

        // Approved (or legacy active-with-no-kingdom) + no kingdom + level 1+ → must choose first
        if (
            in_array($character->status, ['approved', 'active'], true)
            && $character->kingdom_id === null
            && ($character->stats?->level ?? 0) >= 1
        ) {
            return redirect()->route('choose-kingdom');
        }

        return $next($request);
    }
}

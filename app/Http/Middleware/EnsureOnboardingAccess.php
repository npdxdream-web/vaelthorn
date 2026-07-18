<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;

/**
 * Blocks level-0 characters from posting in threads outside of:
 *   (a) a training-zone village, or
 *   (b) the designated onboarding event.
 *
 * Apply to routes that create posts / threads where the $villageId or $threadId
 * is resolvable from the route parameter. For the Blade reply/thread-create flows
 * the gate is enforced directly in ThreadController — this middleware is available
 * for future route-level application.
 */
class EnsureOnboardingAccess
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

        $stats = $character->stats;
        if (! $stats || $stats->level !== 0) {
            // Not in onboarding — let through
            return $next($request);
        }

        // Resolve the thread from the route to check its context
        $thread = $request->route('thread')
            ?? ($request->route('id') ? \App\Models\Thread::with('village')->find($request->route('id')) : null);

        if (! $thread) {
            return $next($request);
        }

        $village = $thread->village ?? $thread->relationLoaded('village') ? $thread->village : $thread->load('village')->village;
        $isTrainingZone   = $village?->is_training_zone;
        $onboardingEventId = AppSetting::onboardingEventId();
        $isOnboardingEvent = $onboardingEventId && $thread->event_id == $onboardingEventId;

        if (! $isTrainingZone && ! $isOnboardingEvent) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'คุณยังไม่ผ่าน Onboarding — ใช้ Training Zone หรือ Event Onboarding ก่อน'], 403);
            }

            return back()->with('error', 'คุณยังไม่ผ่าน Onboarding — ใช้ Training Zone หรือ Event Onboarding ก่อน');
        }

        return $next($request);
    }
}

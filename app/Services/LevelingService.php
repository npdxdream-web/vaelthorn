<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Event;
use App\Models\Inventory;
use App\Models\Post;
use App\Models\RewardLog;
use App\Models\Thread;

class LevelingService
{
    // ─── Public entry-points called by controllers ────────────────────────────

    /**
     * Called when a post is APPROVED (any context).
     * Level-0 characters are still in onboarding (see OnboardingService) and
     * never reach here since they cannot write posts yet.
     */
    public function handlePostApproved(Post $post): void
    {
        $post->loadMissing(['character.stats', 'thread.event', 'thread.city']);
        $character = $post->character;

        if (! $character) {
            return;
        }

        $stats = $character->stats;
        if (! $stats) {
            return;
        }

        if ($stats->level >= 1) {
            $this->handleNormalApproval($character, $post);
        }
    }

    /**
     * Add exp to a character, create a RewardLog, and check for level-up.
     * Level-0 characters never reach here (see handlePostApproved).
     */
    public function addExp(Character $character, int $amount, ?Post $sourcePost = null): void
    {
        $stats = $character->stats ?? $character->load('stats')->stats;
        if (! $stats) {
            return;
        }

        // Daily EXP cap — applies only to Level 1+ (onboarding Level 0 bypasses entirely)
        if ($stats->level >= 1) {
            $today = now()->setTimezone('Asia/Bangkok')->toDateString();

            if ($stats->daily_exp_date !== $today) {
                $stats->update(['daily_exp' => 0, 'daily_exp_date' => $today]);
            }

            $cap       = config("leveling.daily_exp_cap.{$stats->level}")
                         ?? config('leveling.daily_exp_cap_default', 200);
            $remaining = $cap - $stats->daily_exp;

            if ($remaining <= 0) {
                return; // cap reached — no EXP, no RewardLog
            }

            $amount = min($amount, $remaining); // partial if needed
            $stats->increment('daily_exp', $amount);
        }

        $stats->increment('exp', $amount);

        if ($sourcePost) {
            $event = $sourcePost->thread?->event;
            RewardLog::create([
                'character_id'  => $character->id,
                'event_id'      => $event?->id,
                'post_id'       => $sourcePost->id,
                'exp_received'  => $amount,
                'item_quantity' => 0,
                'gold_received' => 0,
            ]);
        }

        if ($stats->level >= 1) {
            $this->checkLevelUp($character);
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    public function promoteToLevel1(Character $character): void
    {
        $stats = $character->stats;
        $stats->level                 = 1;
        $stats->exp_to_next           = config('leveling.exp_to_next.1', 10);
        $stats->stat_points_available += config('leveling.stat_points_per_level', 3);
        $stats->save();

        app(NotificationService::class)->notifyLevelUp($character, 0, 1);
    }

    private function handleNormalApproval(Character $character, Post $post): void
    {
        $thread = $post->thread;
        $event  = $thread?->event;

        $expAmount = $this->resolveExpAmount($thread, $event);
        if ($expAmount > 0) {
            $this->addExp($character, $expAmount, $post);
        }

        if ($event) {
            $this->distributeEventRewards($character, $event);
        }
    }

    /**
     * EXP precedence: an explicit per-thread override always wins; otherwise a
     * self-serve (no-approval-required) zone pays a flat 1 EXP; otherwise fall
     * back to the thread's Event reward. Threads with neither an override nor
     * an event, in a zone that requires approval, pay 0 EXP (unchanged from
     * the pre-existing behavior for plain moderated threads).
     */
    private function resolveExpAmount(?Thread $thread, ?Event $event): int
    {
        if ($thread && $thread->exp_override !== null) {
            return (int) $thread->exp_override;
        }

        if ($thread?->city && ! $thread->city->require_approval) {
            return 1;
        }

        if ($event) {
            return (int) ($event->exp_reward ?? 1);
        }

        return 0;
    }

    private function checkLevelUp(Character $character): void
    {
        $stats = $character->fresh()->stats;
        if (! $stats) {
            return;
        }

        $expNeeded = config("leveling.exp_to_next.{$stats->level}", PHP_INT_MAX);

        if ($stats->exp >= $expNeeded) {
            $oldLevel                     = $stats->level;
            $stats->exp                  -= $expNeeded;
            $stats->level                += 1;
            $stats->exp_to_next           = config("leveling.exp_to_next.{$stats->level}", PHP_INT_MAX);
            $stats->stat_points_available += config('leveling.stat_points_per_level', 3);
            $stats->save();

            app(NotificationService::class)->notifyLevelUp($character, $oldLevel, $stats->level);
        }
    }

    /**
     * Distribute item/gold rewards from an Event to a Character — once per reward template per character.
     * Called only from handleNormalApproval; skipped for onboarding posts.
     */
    private function distributeEventRewards(Character $character, Event $event): void
    {
        $event->loadMissing(['rewards.item']);

        foreach ($event->rewards as $reward) {
            // Skip if character already received this specific reward template
            if (RewardLog::where('character_id', $character->id)
                ->where('reward_id', $reward->id)
                ->where('revoked', false)
                ->exists()) {
                continue;
            }

            // Distribute item
            if ($reward->item_id && $reward->item_quantity > 0) {
                $inv = Inventory::firstOrCreate(
                    ['character_id' => $character->id, 'item_id' => $reward->item_id],
                    ['quantity' => 0]
                );
                $inv->increment('quantity', $reward->item_quantity);

                if ($reward->item && $character->user) {
                    app(NotificationService::class)->notifyItemReceived(
                        $character->user, $reward->item, $reward->item_quantity
                    );
                }
            }

            // Distribute gold
            if ($reward->gold_amount > 0) {
                $character->increment('gold', $reward->gold_amount);
            }

            // Audit log — separate entry per reward template
            RewardLog::create([
                'character_id'  => $character->id,
                'event_id'      => $event->id,
                'reward_id'     => $reward->id,
                'item_id'       => $reward->item_id,
                'item_quantity' => $reward->item_quantity ?? 0,
                'gold_received' => $reward->gold_amount ?? 0,
                'exp_received'  => 0,
                'given_at'      => now(),
            ]);
        }
    }
}

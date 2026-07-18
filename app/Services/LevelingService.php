<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Character;
use App\Models\Event;
use App\Models\Inventory;
use App\Models\OnboardingSlot;
use App\Models\Post;
use App\Models\RewardLog;

class LevelingService
{
    // ─── Public entry-points called by controllers ────────────────────────────

    /**
     * Called when a post is CREATED in a training-zone village.
     * Fills the next empty onboarding slot and grants 1 EXP immediately (no approval needed).
     * Returns true if a slot was filled, false if all 3 were already full.
     */
    public function handleTrainingZonePost(Post $post): bool
    {
        $post->loadMissing(['character.stats']);
        $character = $post->character;

        if (! $character) {
            return false;
        }

        $stats = $character->stats;
        if (! $stats || $stats->level !== 0 || $stats->stage_a_completed) {
            return false;
        }

        // Find or create slots for this character (3 total)
        $this->ensureSlotsExist($character->id);

        $emptySlot = OnboardingSlot::where('character_id', $character->id)
            ->where('status', 'empty')
            ->orderBy('slot_number')
            ->first();

        if (! $emptySlot) {
            return false; // all slots already filled
        }

        $emptySlot->update([
            'post_id' => $post->id,
            'status'  => 'filled',
        ]);

        // Auto-approve the training zone post
        $post->update(['status' => 'approved']);

        $this->addExp($character, 1, $post);

        // Check if Stage A is now complete (all 3 slots filled)
        $filledCount = OnboardingSlot::where('character_id', $character->id)
            ->where('status', 'filled')
            ->count();

        if ($filledCount >= 3) {
            $stats->stage_a_completed = true;
            $stats->save();
        }

        return true;
    }

    /**
     * Called when a post in the designated onboarding event is APPROVED.
     * Increments stage_b_exp (a separate counter from total exp).
     * Also awards normal exp via addExp for the audit trail.
     * Triggers promoteToLevel1 when the stage_b threshold is met.
     */
    public function handleOnboardingEventPost(Post $post): void
    {
        $post->loadMissing(['character.stats', 'thread.event']);
        $character = $post->character;

        if (! $character) {
            return;
        }

        $stats = $character->stats;

        if (! $stats || $stats->level !== 0 || ! $stats->stage_a_completed) {
            return;
        }

        $expReward = (int) ($post->thread?->event?->exp_reward ?? 1);
        if ($expReward <= 0) {
            return;
        }

        // Increment the onboarding gate counter (separate from total exp)
        $stats->increment('stage_b_exp', $expReward);

        // Also give the actual exp (creates RewardLog for audit trail)
        $this->addExp($character, $expReward, $post);
    }

    /**
     * Called when a post is APPROVED (any context).
     * Routes to the correct handler based on character level + post context.
     */
    public function handlePostApproved(Post $post): void
    {
        $post->loadMissing(['character.stats', 'thread.event']);
        $character = $post->character;

        if (! $character) {
            return;
        }

        $stats = $character->stats;
        if (! $stats) {
            return;
        }

        if ($stats->level === 0 && $stats->stage_a_completed) {
            $onboardingEventId = AppSetting::onboardingEventId();
            if ($onboardingEventId && $post->thread?->event_id == $onboardingEventId) {
                $this->handleOnboardingEventPost($post);
                return;
            }
        }

        if ($stats->level >= 1) {
            $this->handleNormalApproval($character, $post);
        }
    }

    /**
     * Add exp to a character, create a RewardLog, and check for progression.
     * For level-0 characters: calls checkOnboardingProgress (stage gate check).
     * For level-1+ characters: calls checkLevelUp.
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

        if ($stats->level === 0) {
            $this->checkOnboardingProgress($character);
        } elseif ($stats->level >= 1) {
            $this->checkLevelUp($character);
        }
    }

    /**
     * Check whether the onboarding gate has been cleared (level 0 → 1).
     * Condition: stage_a_completed AND stage_b_exp >= required.
     */
    public function checkOnboardingProgress(Character $character): void
    {
        $stats = $character->fresh()->stats;
        if (! $stats || $stats->level !== 0) {
            return;
        }

        $required = config('leveling.stage_b_required_exp', 6);

        if ($stats->stage_a_completed && $stats->stage_b_exp >= $required) {
            $this->promoteToLevel1($character);
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
        $event = $post->thread?->event;
        if (! $event) {
            return;
        }

        $expAmount = (int) ($event->exp_reward ?? 1);
        if ($expAmount > 0) {
            $this->addExp($character, $expAmount, $post);
        }

        $this->distributeEventRewards($character, $event);
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

    private function ensureSlotsExist(int $characterId): void
    {
        for ($i = 1; $i <= 3; $i++) {
            OnboardingSlot::firstOrCreate(
                ['character_id' => $characterId, 'slot_number' => $i],
                ['status' => 'empty']
            );
        }
    }
}

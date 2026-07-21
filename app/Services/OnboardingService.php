<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Notification;
use App\Models\OnboardingEntry;

class OnboardingService
{
    public function __construct(
        private LevelingService $leveling,
        private NotificationService $notifications,
    ) {}

    /**
     * Submit a stage entry for a character.
     * Returns true if the stage was saved, false if already submitted.
     */
    public function submitStage(Character $character, int $stage, string $content): bool
    {
        if (OnboardingEntry::where('character_id', $character->id)->where('stage', $stage)->exists()) {
            return false;
        }

        OnboardingEntry::create([
            'character_id' => $character->id,
            'stage'        => $stage,
            'content'      => $content,
            'submitted_at' => now(),
        ]);

        $stats = $character->stats ?? $character->load('stats')->stats;
        if (! $stats) {
            return true;
        }

        $flag         = "stage_{$stage}_completed";
        $stats->$flag = true;

        if ($stage === 1 && $stats->rejection_reason) {
            $stats->rejection_reason = null;
        }

        $stats->save();

        $this->checkAllComplete($character);

        return true;
    }

    /**
     * Returns the next incomplete stage number (1–3), or null if all done.
     */
    public function nextStage(Character $character): ?int
    {
        $stats = $character->stats ?? $character->load('stats')->stats;

        if (! $stats || ! $stats->stage_1_completed) {
            return 1;
        }
        if (! $stats->stage_2_completed) {
            return 2;
        }
        if (! $stats->stage_3_completed) {
            return 3;
        }

        return null;
    }

    private function checkAllComplete(Character $character): void
    {
        $stats = $character->fresh()->stats;

        if (! $stats || ! $stats->stage_1_completed || ! $stats->stage_2_completed || ! $stats->stage_3_completed) {
            return;
        }

        // Promote to level 1 if still at level 0
        if ($stats->level === 0) {
            $this->leveling->promoteToLevel1($character);
        }

        // Notify admins — send only once per character
        $this->notifications->notifyAdminsCharacterReady($character);
    }
}

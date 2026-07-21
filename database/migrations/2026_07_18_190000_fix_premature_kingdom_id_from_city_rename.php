<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The 2026_07_18_162500 rename migration carried the old
     * characters.city_id (set for every character at registration under the
     * legacy flow) straight into characters.kingdom_id, which is only
     * supposed to be set once a character finishes the onboarding chapters.
     * Characters that hadn't finished onboarding ended up looking
     * "already assigned a kingdom", which made OnboardingController
     * redirect every /onboarding/stage submission to /home instead of
     * saving it. Clear the incorrectly-carried-over value for anyone who
     * hasn't actually completed all 3 onboarding stages.
     */
    public function up(): void
    {
        DB::table('characters')
            ->join('character_stats', 'character_stats.character_id', '=', 'characters.id')
            ->where(function ($query) {
                $query->where('character_stats.stage_1_completed', false)
                    ->orWhere('character_stats.stage_2_completed', false)
                    ->orWhere('character_stats.stage_3_completed', false);
            })
            ->update([
                'characters.kingdom_id' => null,
                'characters.current_kingdom_id' => null,
            ]);
    }

    public function down(): void
    {
        // Not reversible — the original (incorrect) values are not recoverable.
    }
};

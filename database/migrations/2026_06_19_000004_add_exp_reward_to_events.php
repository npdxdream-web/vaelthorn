<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // EXP awarded per approved post in this event.
            // flash: must be 1; story_arc/crisis/location: 3–15.
            // Validated in EventResource, not enforced as a DB constraint.
            $table->unsignedTinyInteger('exp_reward')->default(1)->after('end_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('exp_reward');
        });
    }
};

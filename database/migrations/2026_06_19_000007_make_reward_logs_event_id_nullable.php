<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_logs', function (Blueprint $table) {
            // Onboarding stages 1 & 2 award EXP without an event, so event_id must be nullable.
            $table->dropForeign(['event_id']);
            $table->unsignedBigInteger('event_id')->nullable()->change();
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reward_logs', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }
};

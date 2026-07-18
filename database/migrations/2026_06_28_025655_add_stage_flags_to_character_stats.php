<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->boolean('stage_1_completed')->default(false)->after('stage_b_exp');
            $table->boolean('stage_2_completed')->default(false)->after('stage_1_completed');
            $table->boolean('stage_3_completed')->default(false)->after('stage_2_completed');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn(['stage_1_completed', 'stage_2_completed', 'stage_3_completed']);
        });
    }
};

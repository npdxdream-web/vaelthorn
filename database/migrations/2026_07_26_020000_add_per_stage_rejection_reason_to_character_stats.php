<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->text('stage_1_rejection_reason')->nullable()->after('rejection_reason');
            $table->text('stage_2_rejection_reason')->nullable()->after('stage_1_rejection_reason');
            $table->text('stage_3_rejection_reason')->nullable()->after('stage_2_rejection_reason');
            $table->dropColumn('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('stage_3_completed');
            $table->dropColumn(['stage_1_rejection_reason', 'stage_2_rejection_reason', 'stage_3_rejection_reason']);
        });
    }
};

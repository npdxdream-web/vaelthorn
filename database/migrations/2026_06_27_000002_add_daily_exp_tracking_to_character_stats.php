<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->unsignedSmallInteger('daily_exp')->default(0)->after('stat_points_available');
            $table->date('daily_exp_date')->nullable()->after('daily_exp');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn(['daily_exp', 'daily_exp_date']);
        });
    }
};

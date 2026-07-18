<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->unsignedTinyInteger('stat_points_available')->default(0)->after('int');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn('stat_points_available');
        });
    }
};

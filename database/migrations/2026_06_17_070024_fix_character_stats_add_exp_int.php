<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->integer('int')->default(10)->after('str');
            $table->integer('exp')->default(0)->after('int');
            $table->integer('exp_to_next')->default(100)->after('exp');
        });
    }

    public function down(): void
    {
        Schema::table('character_stats', function (Blueprint $table) {
            $table->dropColumn(['int', 'exp', 'exp_to_next']);
        });
    }
};

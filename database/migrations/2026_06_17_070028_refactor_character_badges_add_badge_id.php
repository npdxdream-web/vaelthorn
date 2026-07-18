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
        Schema::table('character_badges', function (Blueprint $table) {
            $table->dropColumn(['name', 'icon', 'description']);
        });
        Schema::table('character_badges', function (Blueprint $table) {
            $table->foreignId('badge_id')->after('character_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('acquired_at')->useCurrent()->after('badge_id');
            $table->unique(['character_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::table('character_badges', function (Blueprint $table) {
            $table->dropUnique(['character_id', 'badge_id']);
            $table->dropForeign(['badge_id']);
            $table->dropColumn(['badge_id', 'acquired_at']);
        });
        Schema::table('character_badges', function (Blueprint $table) {
            $table->string('name')->after('character_id');
            $table->string('icon')->nullable()->after('name');
            $table->string('description')->nullable()->after('icon');
        });
    }
};

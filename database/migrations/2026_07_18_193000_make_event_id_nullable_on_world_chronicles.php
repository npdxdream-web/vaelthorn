<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * World Chronicle entries are now written as freeform admin lore and
     * are no longer required to link back to a specific Event. Deleting an
     * event should unlink the chronicle, not delete it.
     */
    public function up(): void
    {
        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });

        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->change();
        });

        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });

        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
        });

        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }
};

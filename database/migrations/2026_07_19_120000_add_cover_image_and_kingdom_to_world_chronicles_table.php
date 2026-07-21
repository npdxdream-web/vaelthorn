<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * cover_image backs the new gallery-card cover art. kingdom_id lets a
     * freeform chronicle (event_id nullable, per the 2026-07-18 restructure)
     * be tagged with a Kingdom directly, so the card-gallery fallback color
     * doesn't depend on an Event link that most future entries won't have.
     */
    public function up(): void
    {
        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('category');
            $table->foreignId('kingdom_id')->nullable()->after('event_id')
                ->constrained('kingdoms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('world_chronicles', function (Blueprint $table) {
            $table->dropForeign(['kingdom_id']);
            $table->dropColumn(['cover_image', 'kingdom_id']);
        });
    }
};

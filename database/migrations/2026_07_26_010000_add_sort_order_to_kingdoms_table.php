<?php

use App\Models\Kingdom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kingdoms', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });

        // Backfill using current id order so the admin-visible order doesn't
        // visibly change the moment this deploys — only future drag-reorders will.
        Kingdom::orderBy('id')->get()->each(
            fn (Kingdom $kingdom, int $index) => $kingdom->update(['sort_order' => $index + 1])
        );
    }

    public function down(): void
    {
        Schema::table('kingdoms', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};

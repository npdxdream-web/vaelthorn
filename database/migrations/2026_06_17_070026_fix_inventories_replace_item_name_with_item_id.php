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
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('item_id')->after('character_id')->constrained('items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('item_name')->after('character_id');
        });
    }
};

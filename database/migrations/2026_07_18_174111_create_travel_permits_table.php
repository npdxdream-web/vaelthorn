<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite has no real ENUM/MODIFY — Laravel's enum() column type becomes a CHECK
        // constraint there instead, so it must be widened via ->change() (needs doctrine/dbal,
        // which is installed), not skipped — skipping it would leave 'permit' unusable on SQLite.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('items', function (Blueprint $table) {
                $table->enum('type', ['weapon', 'armor', 'consumable', 'material', 'key_item', 'currency', 'permit'])->change();
            });
        } else {
            DB::statement("ALTER TABLE items MODIFY COLUMN type ENUM('weapon', 'armor', 'consumable', 'material', 'key_item', 'currency', 'permit') NOT NULL");
        }

        Schema::create('travel_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kingdom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('valid_days');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_permits');

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('items', function (Blueprint $table) {
                $table->enum('type', ['weapon', 'armor', 'consumable', 'material', 'key_item', 'currency'])->change();
            });
        } else {
            DB::statement("ALTER TABLE items MODIFY COLUMN type ENUM('weapon', 'armor', 'consumable', 'material', 'key_item', 'currency') NOT NULL");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite's grammar can't drop a foreign key by its constraint name (only by column),
     * and has no real named constraints to mismatch after a table rename — so on sqlite we
     * always drop by column. On MySQL we must keep dropping by the exact original constraint
     * name where a table was renamed since the constraint name predates the rename.
     */
    private function dropForeignPortable(string $table, string $column, string $mysqlConstraintName): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column, $mysqlConstraintName) {
            if (DB::getDriverName() === 'sqlite') {
                $blueprint->dropForeign([$column]);
            } else {
                $blueprint->dropForeign($mysqlConstraintName);
            }
        });
    }

    public function up(): void
    {
        // 1. Free up the name "cities" by renaming the old (Kingdom-level) table first.
        Schema::rename('cities', 'kingdoms');

        // 2. The old "villages" table becomes the new City tier.
        Schema::rename('villages', 'cities');

        // 3. characters.city_id -> characters.kingdom_id (home Kingdom, FK constraint name unchanged by the table rename above)
        $this->dropForeignPortable('characters', 'city_id', 'characters_city_id_foreign');
        Schema::table('characters', function (Blueprint $table) {
            $table->renameColumn('city_id', 'kingdom_id');
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->foreign('kingdom_id')->references('id')->on('kingdoms')->cascadeOnDelete();
        });

        // 4. characters.current_city_id -> characters.current_kingdom_id
        $this->dropForeignPortable('characters', 'current_city_id', 'characters_current_city_id_foreign');
        Schema::table('characters', function (Blueprint $table) {
            $table->renameColumn('current_city_id', 'current_kingdom_id');
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->foreign('current_kingdom_id')->references('id')->on('kingdoms')->nullOnDelete();
        });

        // 5. New field: characters.current_city_id — last-visited secondary City (new tier), nullable, not a rename.
        Schema::table('characters', function (Blueprint $table) {
            $table->foreignId('current_city_id')->nullable()->after('current_kingdom_id')
                ->constrained('cities')->nullOnDelete();
        });

        // 6. events.city_id -> events.kingdom_id
        $this->dropForeignPortable('events', 'city_id', 'events_city_id_foreign');
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('city_id', 'kingdom_id');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('kingdom_id')->references('id')->on('kingdoms')->nullOnDelete();
        });

        // 7. cities.city_id (was villages.city_id, FK to old cities/new kingdoms) -> cities.kingdom_id
        // Constraint name predates the villages->cities rename above, hence "villages_city_id_foreign" on MySQL.
        $this->dropForeignPortable('cities', 'city_id', 'villages_city_id_foreign');
        Schema::table('cities', function (Blueprint $table) {
            $table->renameColumn('city_id', 'kingdom_id');
        });
        Schema::table('cities', function (Blueprint $table) {
            $table->foreign('kingdom_id')->references('id')->on('kingdoms')->cascadeOnDelete();
        });

        // 8. threads.village_id -> threads.city_id (FK to the renamed cities table)
        $this->dropForeignPortable('threads', 'village_id', 'threads_village_id_foreign');
        Schema::table('threads', function (Blueprint $table) {
            $table->renameColumn('village_id', 'city_id');
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->renameColumn('city_id', 'village_id');
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('village_id')->references('id')->on('cities')->cascadeOnDelete();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['kingdom_id']);
        });
        Schema::table('cities', function (Blueprint $table) {
            $table->renameColumn('kingdom_id', 'city_id');
        });
        Schema::table('cities', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('kingdoms')->cascadeOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['kingdom_id']);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('kingdom_id', 'city_id');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('kingdoms')->nullOnDelete();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['current_city_id']);
            $table->dropColumn('current_city_id');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['current_kingdom_id']);
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->renameColumn('current_kingdom_id', 'current_city_id');
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->foreign('current_city_id')->references('id')->on('kingdoms')->nullOnDelete();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['kingdom_id']);
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->renameColumn('kingdom_id', 'city_id');
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('kingdoms')->cascadeOnDelete();
        });

        Schema::rename('cities', 'villages');
        Schema::rename('kingdoms', 'cities');
    }
};

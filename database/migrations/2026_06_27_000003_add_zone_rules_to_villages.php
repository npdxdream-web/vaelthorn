<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->unsignedTinyInteger('write_min_level')->default(0)->after('is_training_zone');
            $table->string('write_min_role')->nullable()->default(null)->after('write_min_level');
            $table->boolean('require_approval')->default(false)->after('write_min_role');
            $table->unsignedTinyInteger('read_min_level')->default(0)->after('require_approval');
            $table->string('read_min_role')->nullable()->default(null)->after('read_min_level');
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn(['write_min_level', 'write_min_role', 'require_approval', 'read_min_level', 'read_min_role']);
        });
    }
};

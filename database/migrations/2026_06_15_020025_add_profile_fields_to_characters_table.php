<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('status');
            $table->unsignedBigInteger('current_city_id')->nullable()->after('avatar');
            $table->integer('gold')->default(0)->after('current_city_id');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'current_city_id', 'gold']);
        });
    }
};
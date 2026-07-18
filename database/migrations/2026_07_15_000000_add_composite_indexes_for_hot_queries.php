<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['thread_id', 'status']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->index(['village_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['thread_id', 'status']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['village_id', 'status']);
        });
    }
};

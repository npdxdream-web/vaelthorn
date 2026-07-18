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
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->renameColumn('user_id', 'created_by');
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete()->after('village_id');
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
            $table->dropForeign(['created_by']);
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->renameColumn('created_by', 'user_id');
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

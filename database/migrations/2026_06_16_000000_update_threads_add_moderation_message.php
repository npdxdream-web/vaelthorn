<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->text('moderation_message')->nullable()->after('status');
        });

        DB::table('threads')
            ->where('status', 'open')
            ->update(['status' => 'approved']);

        DB::statement("ALTER TABLE threads MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('moderation_message');
        });
    }
};
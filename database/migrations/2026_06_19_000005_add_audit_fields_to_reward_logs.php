<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_logs', function (Blueprint $table) {
            // Link to the post that triggered this reward — plain integer, no FK cascade.
            // We intentionally avoid a cascading FK so that deleting / soft-deleting a post
            // never silently removes the audit trail.
            $table->unsignedBigInteger('post_id')->nullable()->after('reward_id');

            $table->boolean('revoked')->default(false)->after('given_at');
            $table->timestamp('revoked_at')->nullable()->after('revoked');
            $table->foreignId('revoked_by')
                ->nullable()
                ->after('revoked_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reward_logs', function (Blueprint $table) {
            $table->dropForeign(['revoked_by']);
            $table->dropColumn(['post_id', 'revoked', 'revoked_at', 'revoked_by']);
        });
    }
};

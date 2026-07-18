<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->string('link_type')->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::drop('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('message');
            $table->string('channel')->default('in_app');
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }
};

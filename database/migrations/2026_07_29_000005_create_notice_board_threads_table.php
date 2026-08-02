<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_board_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_board_threads');
    }
};

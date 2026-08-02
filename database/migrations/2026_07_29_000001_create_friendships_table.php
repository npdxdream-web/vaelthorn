<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id_1')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('character_id_2')->constrained('characters')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['character_id_1', 'character_id_2']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};

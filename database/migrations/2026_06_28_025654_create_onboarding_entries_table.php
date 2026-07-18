<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('stage'); // 1, 2, 3
            $table->text('content');
            $table->timestamp('submitted_at')->useCurrent();

            $table->unique(['character_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_entries');
    }
};

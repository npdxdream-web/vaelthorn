<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ai_logs');
    }

    public function down(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost_thb', 10, 4)->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });
    }
};

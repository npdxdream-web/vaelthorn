<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('islands', 'notice_boards');
    }

    public function down(): void
    {
        Schema::rename('notice_boards', 'islands');
    }
};

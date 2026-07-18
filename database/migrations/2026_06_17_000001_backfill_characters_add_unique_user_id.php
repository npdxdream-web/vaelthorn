<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now        = now();
        $defaultCity = DB::table('cities')->where('is_active', 1)->value('id') ?? 1;

        $orphanUsers = DB::table('users')
            ->leftJoin('characters', 'users.id', '=', 'characters.user_id')
            ->whereNull('characters.id')
            ->select('users.id', 'users.name')
            ->get();

        foreach ($orphanUsers as $user) {
            $charId = DB::table('characters')->insertGetId([
                'user_id'    => $user->id,
                'city_id'    => $defaultCity,
                'name'       => $user->name,
                'status'     => 'approved',
                'title'      => 'Administrator',
                'gold'       => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('character_stats')->insert([
                'character_id' => $charId,
                'level'        => 1,
                'hp'           => 100,
                'mana'         => 50,
                'agi'          => 10,
                'str'          => 10,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        Schema::table('characters', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};

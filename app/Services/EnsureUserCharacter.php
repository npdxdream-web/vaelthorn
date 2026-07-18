<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterStat;
use App\Models\City;
use App\Models\User;

class EnsureUserCharacter
{
    public static function for(User $user): Character
    {
        if ($character = $user->character) {
            return $character;
        }

        $cityId = City::where('is_active', true)->value('id') ?? 1;

        $character = Character::create([
            'user_id'  => $user->id,
            'city_id'  => $cityId,
            'name'     => $user->name,
            'status'   => 'active',
            'title'    => 'Administrator',
            'gold'     => 0,
        ]);

        CharacterStat::create([
            'character_id' => $character->id,
            'level'        => 1,
            'hp'           => 100,
            'mana'         => 50,
            'agi'          => 10,
            'str'          => 10,
        ]);

        return $character;
    }
}

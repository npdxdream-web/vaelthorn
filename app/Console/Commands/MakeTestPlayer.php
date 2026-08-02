<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\CharacterStat;
use App\Models\Kingdom;
use App\Models\User;
use Illuminate\Console\Command;

class MakeTestPlayer extends Command
{
    protected $signature = 'make:test-player {email=test@vaelthorn.test : Email for the test player account}';
    protected $description = 'Create (or fix up) an active, level-1 player account with a character — for local testing';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Test Player', 'password' => bcrypt('password'), 'role' => 'player']
        );

        $this->info($user->wasRecentlyCreated
            ? "Created user {$user->email} (password: password)"
            : "Using existing user {$user->email}");

        $character = $user->character;

        if (! $character) {
            $kingdom = Kingdom::where('is_active', true)->first() ?? Kingdom::first();

            $character = Character::create([
                'user_id'    => $user->id,
                'kingdom_id' => $kingdom?->id,
                'name'       => 'Test Player',
                'backstory'  => 'Auto-created test character',
                'status'     => 'active',
            ]);

            CharacterStat::create([
                'character_id'       => $character->id,
                'level'              => 1,
                'exp'                => 0,
                'exp_to_next'        => config('leveling.exp_to_next.1', 10),
                'hp'                 => 100,
                'mana'               => 50,
                'agi'                => 10,
                'str'                => 10,
                'int'                => 10,
                'stage_1_completed'  => true,
                'stage_2_completed'  => true,
                'stage_3_completed'  => true,
            ]);

            $this->info("Created character #{$character->id} (kingdom: " . ($kingdom?->name ?? 'none') . ')');
        } else {
            $character->update(['status' => 'active']);

            if (! $character->stats) {
                CharacterStat::create([
                    'character_id'      => $character->id,
                    'level'             => 1,
                    'exp'               => 0,
                    'exp_to_next'       => config('leveling.exp_to_next.1', 10),
                    'hp'                => 100,
                    'mana'              => 50,
                    'agi'               => 10,
                    'str'               => 10,
                    'int'               => 10,
                    'stage_1_completed' => true,
                    'stage_2_completed' => true,
                    'stage_3_completed' => true,
                ]);

                $this->info("Created missing stats for character #{$character->id}");
            }

            $this->info("Character #{$character->id} already existed — ensured status=active");
        }

        $this->info("Done. {$user->email} / role={$user->role->value} / character #{$character->id} status={$character->status}");

        return self::SUCCESS;
    }
}

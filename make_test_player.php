<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Character;
use App\Models\CharacterStat;
use App\Models\City;
use Illuminate\Support\Str;

$email = 'frarence.c@gmail.com';
$loginNameCandidates = ['test', 'Test', 'tester'];

// Find existing user by common test names
$user = null;
foreach ($loginNameCandidates as $name) {
    $user = User::where('name', $name)->first();
    if ($user) break;
}

// If not found by name, try by email 'test@' patterns
if (! $user) {
    $user = User::where('email', 'like', 'test@%')->first();
}

// If still not found, create a new test user
$created = false;
if (! $user) {
    $user = User::create([
        'name' => 'test',
        'email' => $email,
        'password' => bcrypt('password'),
        'role' => 'player',
    ]);
    $created = true;
    echo "Created new user 'test' with email {$email}\n";
} else {
    echo "Found user: id={$user->id}, name={$user->name}, email={$user->email}\n";
    // Update email and role
    $user->email = $email;
    $user->role = 'player';
    $user->save();
    echo "Updated user email to {$email} and role to 'player'.\n";
}

// Ensure character exists
$character = $user->character()->first();
if (! $character) {
    // pick an active city or first city
    $city = City::where('is_active', true)->first() ?: City::first();
    $cityId = $city?->id ?? null;

    $character = Character::create([
        'user_id' => $user->id,
        'city_id' => $cityId,
        'name' => 'Test Player',
        'backstory' => 'Auto-created test character',
        'status' => 'active',
    ]);

    CharacterStat::create([
        'character_id' => $character->id,
        'level' => 1,
        'hp' => 100,
        'mana' => 50,
        'agi' => 10,
        'str' => 10,
    ]);

    echo "Created character id={$character->id} for user id={$user->id}\n";
} else {
    echo "User already has character id={$character->id}\n";
    // ensure status is active
    $character->status = 'active';
    $character->save();
    // ensure stats exist
    if (! $character->stats) {
        CharacterStat::create([
            'character_id' => $character->id,
            'level' => 1,
            'hp' => 100,
            'mana' => 50,
            'agi' => 10,
            'str' => 10,
        ]);
        echo "Created missing stats for character id={$character->id}\n";
    }
}

$roleString = null;
try {
    $roleString = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
} catch (\Throwable $e) {
    $roleString = (string) $user->role;
}

echo "Done. User id={$user->id}, email={$user->email}, role={$roleString}\n";
echo "Character: id={$character->id}, name={$character->name}, status={$character->status}\n";

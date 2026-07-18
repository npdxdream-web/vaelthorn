<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'frarence.c@gmail.com')->first();

echo 'Role: ' . ($user ? $user->role : 'NOT FOUND') . "\n";
echo 'Character: ' . ($user && $user->character ? 'YES' : 'NO') . "\n";

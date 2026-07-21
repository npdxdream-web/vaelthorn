<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature   = 'user:make-superadmin {email : Email of an existing registered user}';
    protected $description = 'Promote an existing user to SuperAdmin — for bootstrapping the first admin panel account on a new environment';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email [{$email}]. They must register a normal account first.");

            return self::FAILURE;
        }

        if ($user->role === UserRole::SuperAdmin) {
            $this->info("{$user->name} <{$email}> is already a SuperAdmin.");

            return self::SUCCESS;
        }

        $previousRole = $user->role?->label() ?? 'none';
        $user->update(['role' => UserRole::SuperAdmin]);

        $this->info("{$user->name} <{$email}> promoted to SuperAdmin (was: {$previousRole}).");

        return self::SUCCESS;
    }
}

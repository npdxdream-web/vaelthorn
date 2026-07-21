<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotes_an_existing_user_to_superadmin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Player]);

        $this->artisan('user:make-superadmin', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertSame(UserRole::SuperAdmin, $user->fresh()->role);
    }

    public function test_fails_for_an_unknown_email(): void
    {
        $this->artisan('user:make-superadmin', ['email' => 'nobody@example.com'])
            ->assertExitCode(1);
    }

    public function test_is_a_no_op_when_already_superadmin(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->artisan('user:make-superadmin', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertSame(UserRole::SuperAdmin, $user->fresh()->role);
    }
}

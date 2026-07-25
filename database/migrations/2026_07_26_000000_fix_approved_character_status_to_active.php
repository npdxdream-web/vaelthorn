<?php

use App\Models\Character;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * 'approved' was a selectable status in the admin UI but no code path (onboarding
     * redirect, EnsureKingdomSelected middleware, etc.) recognizes anything but 'active'
     * as "fully approved" — characters left at 'approved' get stuck on /onboarding forever.
     * This is a one-time data fix; the UserResource dropdown no longer offers 'approved'.
     */
    public function up(): void
    {
        Character::where('status', 'approved')->update(['status' => 'active']);
    }

    public function down(): void
    {
        // Not reversible — we can't tell which 'active' rows were previously 'approved'.
    }
};

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Regression test for the /admin session-cookie collision bug documented in
 * CLAUDE.md — before the fix, guard `web` and guard `admin` shared the same
 * session cookie name, so an admin logging into /admin (which regenerates the
 * session id AND rotates the CSRF token — see Illuminate\Session\Store::regenerate())
 * could invalidate a concurrently logged-in player's session in the same
 * browser, breaking their pending form's CSRF token (419).
 *
 * These tests drive real HTTP requests instead of actingAs(), because the bug
 * only exists at the cookie-name level, which actingAs() bypasses entirely.
 */
class AdminSessionCookieTest extends TestCase
{
    use RefreshDatabase;

    private function makePlayer(): User
    {
        $user = User::factory()->create(['role' => UserRole::Player]);
        $character = $user->character()->create(['name' => 'Player One', 'status' => 'active']);
        $character->stats()->create(['level' => 1, 'exp' => 0, 'exp_to_next' => 10]);

        return $user;
    }

    private function findCookie($response, string $name)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }

    /**
     * Real per-request boot builds a brand new SessionManager driver (and thus a
     * fresh Store bound to whatever config('session.cookie') currently is) every
     * time, since production runs one PHP process per request. This test's
     * simulated requests all share one process/container, so Illuminate\Support\
     * Manager's internal driver cache would otherwise keep returning the FIRST
     * Store it ever built — permanently bound to whichever cookie name was active
     * at that first call, ignoring every later config change. Resetting the cookie
     * name and immediately forgetting cached drivers reproduces true per-request
     * isolation for every simulated "request" in this test.
     */
    private function useCookieName(string $name): void
    {
        config(['session.cookie' => $name]);
        $this->app['session']->forgetDrivers();
    }

    /**
     * Attaches every cookie currently "held" by the simulated browser, mimicking
     * how a real browser auto-attaches every cookie for the domain+path to every
     * request — regardless of which route actually needs it.
     */
    private function withJar(array $jar)
    {
        $request = $this;
        foreach ($jar as $name => $value) {
            $request = $request->withCookie($name, $value);
        }

        return $request;
    }

    public function test_admin_and_player_routes_issue_different_session_cookie_names(): void
    {
        config(['session.driver' => 'database']);
        $this->useCookieName('vaelthorn_session');

        $playerResponse = $this->get('/login');
        $this->assertNotNull(
            $this->findCookie($playerResponse, 'vaelthorn_session'),
            'Frontend must still issue the vaelthorn_session cookie.'
        );

        $this->useCookieName('vaelthorn_session');

        $adminResponse = $this->get('/admin/login');
        $this->assertNotNull(
            $this->findCookie($adminResponse, 'vaelthorn_admin_session'),
            'Admin panel must issue its own vaelthorn_admin_session cookie.'
        );
        $this->assertNull(
            $this->findCookie($adminResponse, 'vaelthorn_session'),
            'Admin panel response must not also touch the player cookie.'
        );
    }

    public function test_admin_login_does_not_break_a_players_pending_form(): void
    {
        config(['session.driver' => 'database']);

        // Laravel's own CSRF middleware (PreventRequestForgery::runningUnitTests())
        // skips verification entirely whenever app()->environment() === 'testing',
        // which is exactly the scenario this test needs to exercise for real. Force
        // a non-testing environment string just for this test so CSRF actually runs
        // — every other testing-env config (sqlite DB, array mail, etc.) was already
        // resolved by config-loading before this line, so it's unaffected.
        $this->app->instance('env', 'local');

        $player = $this->makePlayer();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $jar = [];

        // 1) Player opens the login page — starts a session, browser gets vaelthorn_session.
        $this->useCookieName('vaelthorn_session');
        $this->get('/login');
        $jar['vaelthorn_session'] = $this->app['session']->getId();
        $playerToken = $this->app['session']->token();

        // 2) Player logs in for real — AuthController::login() regenerates the session
        // (Store::regenerate() also rotates the CSRF token).
        $this->useCookieName('vaelthorn_session');
        $this->withJar($jar)
            ->post('/login', [
                '_token' => $playerToken,
                'name' => $player->name,
                'password' => 'password',
            ])
            ->assertRedirect(route('home'));
        $jar['vaelthorn_session'] = $this->app['session']->getId();

        // 3) Player loads a form — this is the CSRF token that must survive.
        $this->useCookieName('vaelthorn_session');
        $this->withJar($jar)->get('/character/edit');
        $playerFormToken = $this->app['session']->token();
        $jar['vaelthorn_session'] = $this->app['session']->getId();

        // 4) Admin opens /admin/login in another tab of the SAME browser. A real browser
        // auto-attaches every cookie it currently holds to every same-origin request —
        // pre-fix that's the player's vaelthorn_session cookie, since /admin has never
        // issued a cookie of its own yet. This is exactly how the collision happens.
        $this->useCookieName('vaelthorn_session');
        $this->withJar($jar)->get('/admin/login');

        // Authenticate using the same primitives Filament's real Login::authenticate()
        // uses (guard attempt + session()->regenerate()), inside the session context
        // this request just established.
        $this->assertTrue(
            Auth::guard('admin')->attempt(['name' => $admin->name, 'password' => 'password']),
            'Admin credentials should authenticate.'
        );
        $this->app['session']->regenerate();

        // Whichever cookie name is active right now is what a real browser's
        // Set-Cookie from this response would have just written into its jar —
        // pre-fix this overwrites $jar['vaelthorn_session']; post-fix it adds a
        // brand new, separate 'vaelthorn_admin_session' key instead.
        $jar[config('session.cookie')] = $this->app['session']->getId();

        // 5) Player resubmits their original, now-stale form.
        $this->useCookieName('vaelthorn_session');
        $replay = $this->withJar($jar)->put('/character', [
            '_token' => $playerFormToken,
            'name' => 'Renamed By Player',
        ]);

        $this->assertNotSame(
            419,
            $replay->getStatusCode(),
            'Player got a 419 — admin login on /admin invalidated their session/CSRF token.'
        );
        $replay->assertRedirect(route('character.show', $player->character->id));
    }
}

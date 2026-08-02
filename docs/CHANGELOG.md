# Changelog

Snapshot of in-progress work on the `main` branch. All work described below is **committed and pushed to `origin/main`** (github.com/npdxdream-web/vaelthorn) — working tree is clean. The single-commit/122-uncommitted-files note that used to be here described the state on 2026-07-18, before the first push; kept no longer, see the dated updates below for what actually shipped.

> **⚠️ Correction (2026-08-02): the "pushed / clean working tree" claim above is stale.** As of the 2026-08-02 update further down, local `main` is 5 commits ahead of `origin/main` (not yet pushed), and the working tree has a substantial amount of **unrelated, uncommitted** work sitting in it (a Friend system, a NoticeBoard/Islands system, a Player Directory/Leaderboard pair of pages) that predates and is untouched by the 2026-08-02 session. Don't assume "clean" or "pushed" from this banner — check `git status` / `git log origin/main..HEAD` directly.

_Last updated: 2026-08-02_

---

## What this session's changes are

A structural rename + three new game systems, done together because the rename touched shared foreign keys:

1. **Kingdom/City hierarchy rename** — the old `cities` (5 kingdoms) → `villages` (sub-areas) tables become `kingdoms` → `cities`. New `Kingdom` model; `City` model renamed-in-place from `Village`. Every FK, relation, controller, Filament resource, and frontend label that referenced the old naming was updated (`app/Models/Character.php`, `Event.php`, `Thread.php`, `EventResource`, `CharacterResource`, `AppServiceProvider`, `routes/web.php`, `CityController`/`CityPage.tsx`/`ThreadPage.tsx`, seeders, etc).
2. **Onboarding cleanup** — removed the legacy Stage A (training-zone auto-post slots) / Stage B (onboarding-event EXP counter) system entirely (`onboarding_slots` table, ~150 lines of `LevelingService`, `AppSetting::onboardingEventId()`). What remains is the 3-essay `stage_1/2/3_completed` review that was already current. Added: admin rejection now requires a `rejection_reason` and is non-terminal (resets stages, doesn't set a dead-end `status`), and kingdom selection (`/choose-kingdom`) is folded into the same `EnsureKingdomSelected` middleware that used to be two separate middlewares (`EnsureCitySelected` + `EnsureOnboardingAccess`, both deleted).
3. **New economy systems**: `TravelPermit` (temporary non-home-Kingdom write access, minted as a unique `permit`-type Item per grant), and a reshaped `crafting_recipes` table backing two new player-facing systems — **Shop** (`ShopController`, instant buy with gold or materials) and **Blacksmith** (`BlacksmithController`, multi-player `CraftingOrder` + `CraftingOrderContribution` — start an order, others contribute materials, it "cooks," then gets claimed).

Full schema/route/model details are now documented in [CLAUDE.md](CLAUDE.md), which was updated in this same session to match.

---

## File-level shape of the change

- **9 new migrations** (`2026_07_18_*`), none yet confirmed run against the local DB in this session (`php artisan migrate:status` wasn't reachable from the sandboxed shell — verify manually with your Laragon PHP).
- **6 new models**: `Kingdom`, `TravelPermit`, `CraftingRecipe`, `CraftingRecipeMaterial`, `CraftingOrder`, `CraftingOrderContribution`.
- **4 new controllers**: `CityController` (replaces `VillageController`), `KingdomSelectionController` (replaces `CitySelectionController`), `BlacksmithController`, `ShopController`.
- **1 new middleware**: `EnsureKingdomSelected` (aliased `kingdom.selected`), replacing `EnsureCitySelected` + `EnsureOnboardingAccess` (both deleted).
- **3 new Filament resources**: `KingdomResource`, `CraftingRecipeResource`, `TravelPermitResource`; 1 new relation manager: `BadgesRelationManager` on `CharacterResource`.
- **Deleted**: `VillageResource` (+ its 3 pages), `VillageController`, `CitySelectionController`, `OnboardingSlot` model, `EnsureCitySelected`/`EnsureOnboardingAccess`, `VillagePage.tsx`, `village.blade.php`, `choose-city.blade.php`, `VillageSeeder`, `OnboardingSettings` Filament page + its Blade view.
- **New views**: `resources/views/shop/`, `resources/views/blacksmith/`, `city.blade.php`, `choose-kingdom.blade.php`.
- **New seeder**: `KingdomSeeder` (runs before the restructured `CitySeeder`, which now seeds sub-cities keyed by parent Kingdom name).

---

## Verified during this session

- Checked the removal of the `title` field from `CharacterController::update`'s validated fields against `character-edit.blade.php` — the form has no `title` input, so this is consistent (an intentional removal of player-editable title, not a missed regression). `UserResource`'s admin edit form also dropped its `backstory` textarea in favor of a read-only onboarding-answers panel — looks intentional, not verified against product intent.

## Not yet verified / open items

- ~~Migrations not confirmed run~~ — **confirmed 2026-07-19**: all 36 migrations (including all 9 new ones, batches 28–36) are already applied to the local DB; `php artisan migrate` reports "Nothing to migrate."
- **No test run performed.** `composer test` / `phpunit` wasn't executed this session — the existing test suite almost certainly references the old `Village`/`City`-as-kingdom naming and old onboarding stage-A/B behavior, and will need updates.
- **Frontend build not verified.** `CityPage.tsx`/`routes.tsx`/nav component changes weren't smoke-tested in a browser this session.
- **No travel-permit player-facing purchase/grant flow beyond admin-issued** — currently `TravelPermitResource` (Filament, admin-only issuance) + `POST /inventory/permits/{id}/activate` (player activates a granted permit) are the only entry points; there's no player-initiated way to buy/request one.
- **`EventRequirement::req_type` still lists `city`** (`app/Filament/Resources/EventResource.php` and the `event_requirements` table comment) — not confirmed whether this should now mean Kingdom or City tier under the new hierarchy; wasn't in scope of the files this session touched.

---

## Suggested next steps

1. Run `php artisan migrate` (fresh or incremental, depending on current DB state) and re-seed, then smoke-test onboarding → kingdom choice → posting → shop/blacksmith by hand.
2. Update/port `tests/` for the new Kingdom/City naming and the simplified onboarding flow; the old Stage A/B tests (if any) should be deleted rather than patched.
3. Decide whether `EventRequirement.req_type = 'city'` needs a matching rename or a new `kingdom` option.
4. Review whether Travel Permits need a player-facing acquisition path (currently admin-grant only), or if that's intentionally admin-gated.
5. Once verified, commit — likely as a small number of logically-scoped commits (rename, onboarding cleanup, economy systems) rather than one giant commit, if the user wants clean history.

---

## Update 2026-07-21 — re-verified against working tree (code-level, no PHP CLI in this session)

User confirmed the 3 items below were greenlit and asked to re-check whether they actually landed. Re-audited by reading the working tree directly (migrations, models, controllers, routes, Filament resources, frontend). No `php` binary available in this session's shell, so this is a **static re-check**, not a re-run of `migrate`/tests — the "confirmed 2026-07-19, all applied" migration note above is still the last DB-level verification.

### 1. Kingdom/City rename — **done**
All 9 `2026_07_18_*` migrations present. `Kingdom`/`City` models exist with correct relations. `Village` model, `VillageController`, `VillageResource` (+3 pages), `VillageSeeder` all deleted. Grepped `app/` and `routes/` for `Village`/`village_id` — zero hits.

### 2. Onboarding cleanup (Stage A/B removal) — **done**
`OnboardingSlot`, `EnsureCitySelected`, `EnsureOnboardingAccess`, the `OnboardingSettings` Filament page all deleted with no remaining references. `OnboardingService::submitStage`/`nextStage`/`checkAllComplete` implements the clean 3-stage flow. `CharacterResource` rejection correctly resets `stage_1/2/3_completed` + sets `rejection_reason`, non-terminal (matches CLAUDE.md). `kingdom.selected` (`EnsureKingdomSelected`) middleware is wired into `/cities/*`, `/threads/*` routes in `routes/web.php`.

### 3. New economy systems (TravelPermit, Shop, Blacksmith) — **done**
`TravelPermit`, `CraftingRecipe`, `CraftingRecipeMaterial`, `CraftingOrder`, `CraftingOrderContribution` models + migrations all present. Read `ShopController` and `BlacksmithController` in full: both are transaction-wrapped, write `reward_logs` before touching inventory (per convention), routes for shop/blacksmith/permit-activate all present in `routes/web.php`. Filament resources `KingdomResource`, `CraftingRecipeResource`, `TravelPermitResource` all present.

### Gap found and fixed same session: frontend SPA prototype (`resources/frontend/vaelthorn-ui`) was NOT migrated

The rename above covers backend + Blade + `CityPage.tsx`/`ThreadPage.tsx` (both already fetch real data from `/api/cities/*`/`/api/threads/*`), but four other SPA files — `app/pages/HomePage.tsx`, `app/pages/CharacterPage.tsx`, `app/pages/RecentActivityPage.tsx`, `app/components/CharacterModule.tsx`, all sourced from `app/data/mockData.ts` (a self-contained, disconnected-from-API prototype dataset) — still used old `village`/`Village` naming. Worst offender: `HomePage.tsx` linked to `/village/${id}`, a route that no longer exists (`routes.tsx` only has `city/:cityId` since `VillagePage.tsx` was deleted) → dead link, 404 from the `/app` home page.

**Fixed 2026-07-21:**
- `mockData.ts`: top-level `cities` array → `kingdoms`, nested `villages` key → `cities`; `characters.*` consolidated redundant `city`/`cityName`/`kingdom` (all three held the same value) down to `kingdom`/`kingdomName`/`kingdomColor` + kept `location` (sub-city name, unchanged); `threads[].village`/`threads[].city` swapped to `threads[].city` (sub-city name) / `threads[].kingdom` (top-tier name) to match real semantics.
- `HomePage.tsx`: imports `kingdoms`; world-map markers and card list now link to `/city/${id}` (matches the real route); copy updated "four legendary kingdoms".
- `CharacterPage.tsx`: `character.cityColor`/`cityName` → `kingdomColor`/`kingdomName`; "Home City" label → "Home Kingdom"; `thread.village` → `thread.city`.
- `RecentActivityPage.tsx`: `thread.village` → `thread.city`.
- `CharacterModule.tsx`: `character.cityColor` → `character.kingdomColor`.

Verified with a repo-wide grep for `village`/`Village`/`cityColor`/`cityName` under `resources/frontend/` — zero remaining hits. Note: this mock dataset is still disconnected from the real `/api/cities`/`/api/kingdoms` endpoints (unlike `CityPage.tsx`/`ThreadPage.tsx`) — the mock IDs (e.g. `"forgeheart"`) don't correspond to real DB rows, so `HomePage.tsx`'s city cards now route correctly but will hit `CityPage`'s error state rather than load real data. Wiring `HomePage`/`CharacterPage`/`RecentActivityPage` to the real API instead of `mockData.ts` is a separate, larger task, not part of this rename — flagging here in case it's wanted next, but out of scope for what was asked.

### Other observation (out of scope of the 3 items, noted in passing)
`BlacksmithController::claim()` doesn't verify the claimant was the order's creator or a contributor — any authenticated character who has the shareable token URL can claim the finished item. Not part of this rename/cleanup work; worth a product decision on whether that's intentional (token = permission) or needs restricting.

### Still true / unchanged from the original entry
- Still all uncommitted (working tree only) — nothing above has been committed.
- Migration-run confirmation above (2026-07-19) was not re-verified live this session — re-confirm with `php artisan migrate:status` before deploying.

---

## Update 2026-07-21 (same day, later) — live smoke test against the dev DB + 2 real bugs found and fixed

MySQL wasn't running when this started; user started it manually. With a live DB, ran actual verification instead of only static code reading:

### `php artisan migrate:status` — all 38 migrations confirmed `Ran`, including all 9 from the 2026-07-18 rename/economy work plus 2 more recent ones not previously logged here (`2026_07_19_120000_add_cover_image_and_kingdom_to_world_chronicles_table`, `2026_07_19_140000_create_council_letters_table`, batches 37–38).

### `php artisan route:list` — all 127 routes register with no boot errors (confirms controllers/middleware/service providers all load cleanly).

### Live data sanity via tinker — 6 kingdoms, 12 cities, 8 characters (2 with `kingdom_id` set, 6 still pre-kingdom — normal for a dev DB), 15 crafting recipes, 18 items, 8 users (1 superadmin, 1 admin, 6 players), 2 threads, 6 posts. No orphaned/broken data from the rename.

### Real bug found #1: test suite (SQLite) was completely broken by 2 migrations using raw MySQL-only syntax
`2026_06_16_000000_update_threads_add_moderation_message.php` and `2026_07_18_174111_create_travel_permits_table.php` both used `DB::statement("ALTER TABLE ... MODIFY ...")` — valid MySQL, but SQLite has no `MODIFY` and no real `ENUM`, so `php artisan test`/`composer test` (configured for in-memory SQLite in `phpunit.xml`) failed on migration before any test could even run. **Fixed**: both statements now guarded with `if (DB::getDriverName() !== 'sqlite')` — production/MySQL behavior is byte-for-byte unchanged (the guard just skips a no-op-on-sqlite statement), and SQLite testing now works.

### Real bug found #2: the rename migration itself (`2026_07_18_162500_rename_city_kingdom_village_city_hierarchy.php`) used named-string `dropForeign('...')` calls, which SQLite's grammar doesn't support at all (only column-array form)
4 of the 8 FK drops in `up()` used explicit MySQL constraint names (necessary in 2 of those cases because the constraint predates a table rename, so Laravel's naming convention wouldn't resolve correctly on MySQL). **Fixed**: extracted a `dropForeignPortable()` helper that branches by `DB::getDriverName()` — MySQL keeps the exact original explicit constraint names (unchanged behavior), SQLite uses column-array form. `down()` already used the portable array form throughout, untouched.

### New regression test: `tests/Feature/OnboardingKingdomFlowTest.php`
After the 2 fixes above, wrote and passed (26 assertions) a full end-to-end test of the flow this changelog's original "Suggested next steps #1" asked to smoke-test by hand: register → submit all 3 onboarding essays → level auto-promotes to 1 but `status` stays `pending` → `/choose-kingdom` correctly 403s pre-approval → admin `CharacterResource::approveCharacter()` flips `status` to `active` → `/onboarding` now redirects to `/choose-kingdom` → choosing a kingdom sets `kingdom_id`/`current_kingdom_id` → a second choice is correctly rejected (permanent-once-set enforcement) → posting in a home-kingdom city with `require_approval=false` goes live (`approved`) immediately. This exercises `OnboardingService`, `CharacterResource::approveCharacter`, `KingdomSelectionController`, `EnsureKingdomSelected`, and `ThreadController::storeThread` together — the exact cross-cutting path the 2026-07-18 restructure touched.

Also fixed the stale default `tests/Feature/ExampleTest.php` (asserted `/` returns 200; app now redirects guests to `/login`) — trivial 1-line fix, `assertRedirect(route('login'))` instead.

**Full suite now: 3/3 tests, 29 assertions, 0 failures** (`php artisan test`). This was 0/1 passing at the start of this session (SQLite couldn't even migrate).

### Not done / still open (superseded — see next update, all of this got done)
- ~~Did not run `migrate:fresh` against MySQL~~ — done next, via a throwaway DB, see below.
- ~~Shop/blacksmith/travel-permit flows unverified by automated test~~ — done next, see below.

---

## Update 2026-07-21 (same day, third pass) — deep deploy-bug hunt: MySQL-from-scratch proof, 2 more real bugs fixed, 3 concurrency bugs fixed, full economy test coverage added

User asked to specifically hunt for anything that would cause deploy problems, fix all of it, thoroughly, no time limit. Went beyond the migration-portability fixes from the previous update:

### `migrate:fresh --seed` proven clean on real MySQL from scratch
Created a throwaway database (`vaelthorn_migrate_test`, not the dev `Vaelthorn` DB), pointed `.env` at it temporarily, ran `migrate:fresh --seed`. **All 63 migrations + all 3 seeders (`KingdomSeeder`, `CitySeeder`, `ItemSeeder`) ran clean with zero errors.** This is the exact scenario a fresh Laravel Cloud deploy faces (empty managed MySQL DB, migrations run as a release step) — now empirically proven, not just inferred from code reading. Verified seeded data (6 kingdoms including Celestia, 12 cities, 18 items) and confirmed `items.type` ENUM correctly includes `'permit'` on MySQL. Dropped the throwaway DB and restored `.env` afterward — dev DB untouched throughout.

### Real bug #3: `items.type` ENUM widening was silently broken on SQLite by the previous session's own fix
The earlier fix (guard the MySQL-only `ALTER TABLE ... MODIFY ... ENUM(...)` with `if (DB::getDriverName() !== 'sqlite')`) was itself subtly wrong: SQLite implements Laravel's `enum()` column type as a real `CHECK` constraint, so *skipping* the widening left SQLite's constraint permanently stuck on the original 6-value enum — meaning `Item::create(['type' => 'permit', ...])` would throw a constraint violation on SQLite forever, even though the column exists and works fine on MySQL. Caught this via the new `EconomyFlowTest::test_travel_permit_activation` test (below), which needs to create a `permit`-type item. **Fixed properly**: on SQLite, use `Schema::table('items', fn($t) => $t->enum('type', [...])->change())` (needs `doctrine/dbal`, confirmed installed) instead of skipping; MySQL keeps the original raw `ALTER ... MODIFY` untouched. Re-verified against both a fresh SQLite run (via the test suite) and a fresh MySQL run (`migrate:fresh` above, confirmed the enum in `SHOW COLUMNS`).

### Real bug #4: avatar and World Chronicle cover-image uploads were hardcoded to the local disk, bypassing Tigris entirely in production
`Character::getAvatarUrlAttribute()`, `WorldChronicle::getCoverImageUrlAttribute()`, and `WorldChronicleResource`'s `FileUpload` fields all call `Storage::disk('public')` / `->disk('public')` explicitly. The `'public'` disk in `config/filesystems.php` was *always* the local disk (`storage_path('app/public')`), completely independent of `FILESYSTEM_DISK`/`TIGRIS_*` env vars — meaning even with `.env.cloud.example`'s `FILESYSTEM_DISK=tigris` set in production, **uploaded avatars and chronicle cover images would still write to the Cloud container's local filesystem**, not Tigris, likely lost on redeploy/restart and never actually served correctly. Nothing in the codebase referenced the `'tigris'` disk key at all except its own definition — so the whole Tigris storage setup for user uploads was dead configuration. **Fixed**: `config/filesystems.php`'s `'public'` disk is now conditional — when `TIGRIS_BUCKET` is set (i.e. in production per `.env.cloud.example`) it transparently becomes the Tigris S3 config; otherwise (local dev) it's unchanged. Zero application code needed to change — `Storage::disk('public')` now just resolves correctly per environment. Verified locally via tinker that `config('filesystems.disks.public.driver')` is still `local` with `TIGRIS_BUCKET` unset.

### Real bug #5: `bootstrap/app.php` had no `trustProxies()` — HTTPS misdetection behind Laravel Cloud's load balancer
Laravel Cloud (like most PaaS) terminates TLS at a load balancer and forwards plain HTTP internally with `X-Forwarded-*` headers. Without `trustProxies()`, Laravel doesn't trust those headers, so `Request::secure()`/`url()`/`isSecure()` would think the connection is plain HTTP even when the visitor is on HTTPS — breaking `SESSION_SECURE_COOKIE`-gated cookies and any `https://` URL generation. **Fixed**: added `$middleware->trustProxies(at: '*')` — the standard Laravel-documented setting for PaaS hosts without a fixed proxy IP.

### Real bug #6 (security/correctness): `BlacksmithController::claim()` had no authorization check at all
Anyone logged in with the shareable order-token URL could claim a finished blacksmith order's result item — not just the order's creator or a material contributor, contradicting the documented design ("creator/contributors can claim"). **Fixed**: added an eligibility check (`created_by === character->id` OR has a contribution row), plus wrapped the claim itself in a row-locked (`lockForUpdate`) transaction re-checking status, since two eligible claimants racing the original code could otherwise both receive the item.

### Real bug #7 & #8 (concurrency): unlocked read-then-write races in money/item-granting paths
- `MarketController::buy()` — two concurrent buyers could both pass the `active()` status check on the same listing before either transaction committed, both paying gold and both receiving the item for one listing. **Fixed** with `lockForUpdate()` inside the transaction, re-checking `status === 'active'`.
- `BlacksmithController::contribute()` — two concurrent contributions to the same order could both read the same "remaining" total and together overshoot `quantity_required`, or one could land just after another request flipped the order to `crafting`. **Fixed** the same way — lock the order row, recompute remaining/accepted inside the lock.

At this app's target scale (~20 users/day) these races are unlikely to bite often, but they're cheap, well-contained fixes with no behavior change on the non-concurrent happy path, so fixed rather than deferred per this session's "fix everything found" instruction.

### New test coverage: `tests/Feature/EconomyFlowTest.php`
5 new tests, all passing: Shop buy-with-gold (success + insufficient-gold rejection), full Blacksmith order lifecycle (two contributors → auto-transition to `crafting` → not-ready rejection → non-contributor rejection → eligible contributor claims successfully → double-claim rejection), TravelPermit activation (+ re-activation rejection), and Market double-purchase protection. These directly exercise every fix in this update.

### Full verification after all fixes
- **`php artisan test`: 8/8 tests, 66 assertions, 0 failures** (up from 3/3, 29 assertions before this pass).
- **`npm run build`: clean**, no errors.
- **`migrate:fresh --seed` on real MySQL**: clean, confirmed via `SHOW COLUMNS`.
- All migration/config edits verified to leave the MySQL code path byte-for-byte or functionally identical to before — SQLite-only branches added, nothing removed from the production path except adding `trustProxies` (additive) and reclassifying the `public` disk (backward compatible when `TIGRIS_BUCKET` is unset, which is exactly today's local/dev state).

### Not done / still open (superseded — this got committed next, see below)
- ~~Still all uncommitted~~ — committed as 3 logically-scoped commits (rename+onboarding+economy backend, SPA rename fix, deploy-bugfixes+tests). Not yet pushed to `origin/main` (local is 3 commits ahead).
- Did not audit every controller in the app (e.g. `ThreadController`, `EventController`, `NotificationController`, `CharacterController`, `WorldChronicleController` were read in earlier passes but not re-audited for concurrency/auth issues this pass) — focused on the newest, least-battle-tested systems (2026-07-18 economy/onboarding work) plus universal deploy-blockers (migrations, storage, proxy config). A full line-by-line audit of the entire controller layer was not performed.
- Rate limiting (throttle on login/register — the "AI endpoints" part of this original item is now moot, see below) and a superadmin-bootstrap command — flagged in the original pre-deploy checklist — are still not done; out of scope for "bugs," these are missing features/hardening.
- `Filament` admin panel resources (`KingdomResource`, `CraftingRecipeResource`, `TravelPermitResource`) were read during the earlier verification pass but have no automated test coverage — admin-side flows are still only manually-reasoned-about, not exercised by a test.

---

## Update 2026-07-21 (fourth pass) — AI feature removed from scope entirely

User decided to cut the AI (Anthropic Claude API) feature out of the system and adjust deployment plans accordingly, rather than build/harden it before launch.

### What existed: only unused scaffolding, no working integration
Researched the full codebase (not just docs) before touching anything. Result: **the AI feature was never actually implemented.** No service class calls Anthropic, no `AiLog` model exists, no controller/route/Filament action triggers an AI call, no frontend UI for it (no "Summarize" button, no "Generate with AI" button, no writing-assist textarea). What existed was pure scaffolding, unused end-to-end:
- `ai_logs` table (migration `2026_06_17_070032_create_ai_logs_table`) — no model, never queried or written to anywhere in `app/`.
- `posts.ai_summary` column (migration `2026_06_17_070027_fix_posts_add_ai_summary`) — only referenced as a `Post::$fillable` entry; never read or displayed anywhere.
- `ANTHROPIC_API_KEY` env placeholder in `.env`/`.env.example`/`.env.cloud.example` — not even registered in `config/services.php`, so it wasn't wired into the framework at all.
- A single Thai helper-text string on `WorldChronicleResource`'s content field mentioning "AI หรือ Admin" (AI or Admin) — cosmetic label only, chronicles have always been 100% admin-authored freeform text.
- `docs/roadmap.md`'s "Priority 2 — AI Features" section (Post Summarizer, World Chronicle Generator, Writing Assistant) — all 3 items unchecked, unbuilt.

This meant removal was low-risk cleanup, not unwinding a live integration.

### Removed
- New migrations `2026_07_21_000001_drop_ai_logs_table` and `2026_07_21_000002_drop_ai_summary_from_posts_table` (added as new migrations rather than editing the already-run originals, per standard practice — faithful `down()` on both for rollback).
- `'ai_summary'` removed from `Post::$fillable`.
- AI mention removed from `WorldChronicleResource`'s content field helper text.
- `ANTHROPIC_API_KEY` removed from `.env`, `.env.example`, `.env.cloud.example` — one less production secret to configure.
- `CLAUDE.md`: removed the AI stack bullet, the `ai_logs` schema table entry, `ai_summary` from the `posts` schema row, and reworded the Target Scale paragraph to explicitly note AI tooling was considered but is out of scope.
- `docs/roadmap.md`: reworded the WorldChronicleResource checklist item (was "AI-generated", now "freeform admin-written"), replaced the "Priority 2 — AI Features" task list with a dated note explaining the cut, removed the now-moot AI rate-limiting question from the open-questions list.

### Verified after removal
- `php artisan migrate` on the dev MySQL DB: both new migrations ran clean.
- `migrate:fresh --seed` on a throwaway MySQL DB (same safe pattern as previous passes — dev DB untouched): clean from scratch with the 2 new migrations included.
- `php artisan test`: still 8/8, 66 assertions, 0 failures.
- `npm run build`: still clean.
- Repo-wide grep for `anthropic|claude|ai_logs|ai_summary` (case-insensitive): zero remaining functional references — only the 4 migration files themselves (2 old creates + 2 new drops) and the intentional historical note in `docs/roadmap.md`.

### Deploy plan impact
`ANTHROPIC_API_KEY` is no longer part of the production secrets checklist — one less thing to configure/rotate/monitor cost on when setting up Laravel Cloud env vars.

---

## Update 2026-07-21 (fifth pass) — Phase 2 pre-deploy hardening: rate limiting + superadmin bootstrap

Closed the last 2 open items from the original pre-deploy checklist (both were "missing features/hardening," not bugs).

### Rate limiting on login/register
`POST /login` and `POST /register` now carry `throttle:5,1` (5 attempts/minute, keyed by IP by default). Guards against brute-force login attempts and register-spam/bot signups. Simple inline numeric throttle — no named `RateLimiter::for()` needed since nothing else in the app uses one yet.

### Superadmin bootstrap command
New `php artisan user:make-superadmin {email}` (`app/Console/Commands/MakeSuperAdmin.php`) — promotes an already-registered user to `UserRole::SuperAdmin`. This was a real gap: there was previously no way to create the first admin-panel account on a fresh environment except a raw `tinker` one-liner. Handles 3 cases: unknown email (fails with exit code 1, tells the operator to register first), already-superadmin (no-op, success), normal promotion (success). New `tests/Feature/MakeSuperAdminCommandTest.php` covers all 3 paths via the isolated SQLite test DB — also manually verified against the dev MySQL DB (both the not-found and already-superadmin branches; didn't mutate a real player's role just to test the promotion branch, that's what the SQLite test covers).

**Full suite now: 11/11 tests, 71 assertions, 0 failures.** `npm run build`: clean.

### Deploy plan impact
Both original Phase 2 items are now done — nothing missing/hardening-wise is known to be outstanding before a first deploy, beyond the operational step of actually running `php artisan user:make-superadmin <your-email>` once against production after the first real account registers.

---

## Update 2026-07-25 — city/thread banners, Witness System removed, typography cleanup, deploy survey

Long multi-part session. Grouped by theme; commits are `6419fdc`, `3faae36`, `a4deaa3` (all local, still not pushed to `origin/main` — now 6 commits ahead).

### Shipped (committed)

**City banner images + thread header redesign (`6419fdc`, `3faae36`)**
- `cities.banner_image` (migration + `City::banner_image_url` accessor + Filament `FileUpload` on `CityResource`).
- Thread page header rebuilt as `.thread-header-compact` (breadcrumb/title/meta, same shape with or without a banner).
- "Lore post" concept: the first post in a thread, when authored by an admin/mod, renders in a distinct manuscript style (`.lore-post-body`).
- Quill editor additions across `thread-create.blade.php`/`post-edit.blade.php`: drop cap toggle, right-indent (`ql-indentright`/`ql-rindent-N`), image insert, link/image handlers switched to `window.prompt()` (Quill's tooltip UI breaks under the site's `zoom: 0.9`).
- Data migration stripped a duplicate "Central Market" heading baked into post id 12's content (the real Central Market lore post), now superseded by the header itself.

**Banner pivot + Witness System removal + typography (`a4deaa3`)** — came from live QA against the real Central Market thread with real uploaded artwork, which surfaced that the city banner showing on *every thread in that city* was the wrong place for it:
- City banner moved to render on the city page (`/cities/{id}`) itself, hero-style, title overlaid.
- New `threads.banner_image` column — any thread creator can upload a per-thread banner on both `thread-create` and `thread-edit`, rendered via the same hero-overlay treatment the city page uses, just sourced from the thread instead.
- Witness System (Witness/Inspired/Moved reaction buttons, `post.react` route, `ThreadController::reactPost()`) removed outright at the owner's explicit request, despite CLAUDE.md documenting it as a core "makes players feel seen" mechanic — confirmed twice before removing. `post_reactions` table and `Post`/`PostReaction` model relations left in place, unused, in case of a future revival.
- Reply-post and lore-post reading size unified to `18px` (was `1.35rem` vs `14px` — read as jarringly mismatched in practice).
- Drop cap switched from a bare `font-size` to a fixed 2-body-line box (`line-height: 72px`, `font-size` well under that) so Thai vowel/tone marks (e.g. ่ in "ที่") don't make some glyphs look taller/misaligned than others; later bumped 44px → 58px on request.
- Quill's semantic H1/H2/H3 header dropdown replaced with an explicit-px size dropdown (12–48px) using Quill's built-in `attributors/style/size`.
- Scattered 9–10px/0.44–0.6rem label and badge text (timestamps, city sidebar stats, status badges) standardized to `11px`.
- "Approved" status badge on posts hidden (it's the default state, was just noise) — Pending/rejected still show.

### Verified (this session)
- `php artisan test`: still 11/11, 71 assertions after every change in this session.
- Playwright-driven live QA (via a temporary throwaway admin login, cleaned up after) confirmed: hero banner renders correctly when `banner_image` is set, indent-both-sides narrows a paragraph symmetrically and persists after reload, lore-post duplicate heading fix rendered correctly server-side.
- Server-side Blade render check (via `tinker` + `view()->render()`) confirmed the lore-post font-size fix actually reaches the HTML — a user report of "still looks big" turned out to need a hard browser refresh, not a code fix.

### Two audits done — **reports/plans only, zero implementation** (this is the biggest open item)

**1. Character status system audit** (`characters.status`: pending/approved/active/rejected/suspended)
- Finding: only `active` has any real business logic anywhere in the app (`EnsureKingdomSelected` middleware, `OnboardingController`). `approved`, `rejected`, `suspended` are selectable in Filament dropdowns but **nothing reads or writes them** in the live code paths — `rejectCharacter()` deliberately keeps status at `pending` rather than setting `rejected`.
- Found a live bug from this: character id 6 has `status = approved` with a `kingdom_id` already set — since no code recognizes `approved`, this character will loop back into `/onboarding` forever despite having already picked a kingdom. Real data casualty of the dead state, not hypothetical.
- Recommendation given (not actioned): collapse to `pending`/`active` only; drop `approved`/`rejected`/`suspended` from the schema (or migrate `approved` rows to `active`); if a real ban feature is wanted later, build it with actual enforcement rather than reviving `suspended` as-is.
- **Owner's call**: parked for now, no decision made on if/when to execute the cleanup.

**2. Filament UserResource/CharacterResource redesign** — full audit + a resolved Q&A on design intent, but **no code changed yet**. Agreed direction, ready to implement whenever picked back up:
- `status` becomes single-source-of-truth on `CharacterResource` only (drop the 5-option editable dropdown from `UserResource`, keep it there read-only).
- Same single-source-of-truth principle extends to all overlapping fields (name/kingdom_id/title/gold/stats) — `CharacterResource` becomes the sole editor, `UserResource` gets a read-only preview + "edit →" link.
- `custom_frame` (currently `UserResource`-only, silently unreachable from `CharacterResource`) moves into `CharacterResource` too.
- **Real bug to fix**: `InventoryRelationManager`'s form/table still reference `item_name`, a column dropped from `inventories` back in mid-2026 (replaced by `item_id` FK→items) — Create/Edit on this relation manager is currently broken (SQL error). Confirmed as a bug to fix, not intentional.
- `user_id` reassignment on `CharacterResource` stays, but needs a confirmation modal added (currently silent).
- Add `int`, `exp`, `exp_to_next`, `stat_points_available` as editable RPG Stats fields (currently missing from both resources).
- Consolidate the Onboarding-answers placeholder (currently duplicated with different code in both resources) to `CharacterResource` only.
- Remove dead code: `CreateCharacter` page + `CreateAction` (canCreate() is false, no 'create' route registered — unreachable).
- Consolidate the approve/reject action logic (currently copy-pasted identically between the table row action and the `EditCharacter` page header action) into one shared implementation.
- **Still genuinely unresolved**: `users.email` unique constraint was dropped in a mid-2026 migration with zero explanation anywhere in code/git history/docs — owner wanted to "check first" before deciding whether to restore it; no further info surfaced since.

### Deploy-readiness survey (read-only, no code touched)
- PHP `^8.3`, Laravel `^13.8` (running 13.15.0 locally) — **CLAUDE.md said "Laravel 11", now corrected to "Laravel 13, PHP 8.3+"**.
- No queue/job usage anywhere (`ShouldQueue`/`dispatch()` — zero hits) despite `QUEUE_CONNECTION=database` being configured; no worker needed at deploy.
- One scheduled task: `threads:purge-trashed` daily (`routes/console.php`) — needs the Laravel scheduler cron wired up on whatever host is used.
- No Redis in actual use — cache/session both `database`; `REDIS_*` env vars are unused Laravel boilerplate.
- Dev data is tiny: `storage/app/public` ~8.3MB, DB ~1.4MB (8 users, 7 threads, 9 posts) — not representative of any real load yet.
- `DISCORD_WEBHOOK_URL` is declared in `.env`/`.env.cloud.example` but **wired into zero code** — setting it does nothing today; CLAUDE.md's "Notifications: Discord Webhook" line is aspirational, not current fact.
- Found `.env.cloud.example` — a ready-made Laravel Cloud deploy template (Tigris S3 storage, managed MySQL, DB-backed session/cache/queue). This confirms Laravel Cloud is the intended deploy target.
- Node: no version pinned anywhere before this session (dev machine had v24.18.0) — **added `.nvmrc` with `24.18.0`** to prevent a version-drift build break on deploy.
- React SPA confirmed at `resources/frontend/vaelthorn-ui/` (React 18 + TS + Vite 8), still a separate prototype layer per earlier sessions' notes — not re-audited this pass.

### Not done / still open
- **Not pushed** — local `main` is 6 commits ahead of `origin/main`.
- The two audits above (character status, Filament resources) are fully specced but **not implemented** — highest-value next session if the owner wants to act on them.
- `users.email` uniqueness decision still pending (see above).
- No automated test coverage added for any of this session's new features (thread/city banner upload, Witness removal, etc.) — the existing 11 tests don't touch these paths, so they pass by not being exercised, not by verifying the new behavior.
- Owner's stated plan: deploy for real testers next rather than keep solo-iterating or relying on AI-driven QA alone, on the reasoning that only real usage surfaces the UX confusion an author testing their own app (or an AI) won't hit. Next session likely starts from tester feedback rather than a pre-planned task list — check here first for what that feedback was before assuming these open items are still the top priority.

### Suggested next steps (if picked up cold)
1. If tester feedback exists, read that first — it likely reprioritizes everything below.
2. Decide + execute the Filament resource redesign (plan is fully agreed, just needs implementing) — start with the `InventoryRelationManager` bug since that's an active break, not just cleanup.
3. Decide the character-status cleanup (collapse to 2-3 states) — the `approved`+`kingdom_id` stuck-character bug found during the audit is a real, if minor, live issue worth fixing regardless of the larger cleanup's timing.
4. Resolve the `users.email` uniqueness question one way or the other — currently silently allows duplicates with no validation anywhere.
5. Push local commits to `origin/main` once the above (or whatever's judged ready) is settled — nothing here is blocking that, it's just been sequenced after in case the owner wants to review history before publishing.

---

## Update 2026-07-25 (later same day) — domain + Laravel Cloud purchased; storage switched Tigris → ~~Cloudflare R2~~

> **⚠️ Correction (2026-07-26): everything below about "Cloudflare R2" was a mistaken guess and has been superseded.** The owner has no Cloudflare R2 account and never intended to use it — the `AWS_*` env vars this update wired up are consumed by **Laravel Cloud's own built-in Object Storage**, not R2. The hardcoded bucket ID/region/R2 endpoint mentioned below were fake/invented values, since removed. See the 2026-07-26 update further down for the actual fix. Left in place only as a historical record of what happened and why; don't act on the R2-specific details here.

Owner bought a custom domain and a Laravel Cloud subscription, pushed the prior session's commits to `origin/main`, then provided real Cloudflare R2 credentials (bucket, region, endpoint — access key/secret still separate) to wire up as the production file storage, replacing the previously-planned Tigris.

- `config/filesystems.php`: `public` disk's cloud-vs-local switch now keys off `AWS_BUCKET` (was `TIGRIS_BUCKET`); it and the existing `s3` disk both read the standard `AWS_*` env vars pointing at the R2 endpoint. The dedicated `tigris` disk definition was removed as dead weight (nothing referenced it by name — app code always uses `->disk('public')`).
- `.env.example` / `.env.cloud.example`: `TIGRIS_*` blocks replaced with `AWS_*`. `.env.cloud.example` now has the real R2 bucket ID/region/endpoint pre-filled (not secret on their own — no access key/secret in them) with `[FILL]` left only for the actual R2 API token and public bucket URL.
- Local `.env` deliberately does **not** have `AWS_BUCKET` filled in — filling it would flip local dev's `public` disk over to S3 too (the check is a plain truthy env check), and no access key/secret were provided for local testing, so local avatar/banner uploads would silently break. Local dev stays on the `local` disk; the real R2 values live only in the Cloud template/dashboard.
- Verified: `artisan tinker` confirms `public` disk resolves to `s3`/R2 when `AWS_BUCKET` is set, and back to `local` when it's blank; `php artisan test` still 11/11 after the config change.
- **Still needed before storage actually works on Laravel Cloud**: paste the real `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY` (R2 API token) and the bucket's public URL into the Laravel Cloud dashboard's Environment tab — `.env.cloud.example` is a copy-paste template, not consumed automatically.
- Domain: purchased, but no code/config in this repo references it yet — `APP_URL` in `.env.cloud.example` is still a `[FILL]` placeholder pointing at the domain once DNS/Cloud routing is set up.

---

## Update 2026-07-26 — storage corrected to Laravel Cloud Object Storage, two deploy-blocking bugs fixed, onboarding approve/reject redesigned, **first real deploy attempt hit a wall**

Owner clarified they have **no Cloudflare R2 account** — the prior session's R2 setup was a mistaken guess about what "the AWS_* values" referred to. Then attempted (and is still attempting) the first real Laravel Cloud deploy; hit a chain of build/runtime failures fixed one at a time, then a real onboarding-flow bug report from live testing, and finally discovered the deploy pipeline itself isn't updating production at all. Commits `affecd0` through `43d4073`.

### Fixed: storage config corrected (`affecd0`, `517b692`)
- `.env.cloud.example`'s hardcoded R2 bucket ID/region/endpoint were **not real** — replaced with blank `AWS_*` values + a comment explaining Laravel Cloud's built-in Object Storage auto-populates these once a bucket is created in the dashboard's Storage panel. Nothing to fill in by hand.
- Two stray "Cloudflare R2" comments left behind in `config/filesystems.php` (disk logic itself was already correct — comment-only fix).

### Fixed: two deploy-blocking build failures (`c52b766`, `8ee49f4`)
- **Missing package**: Laravel Cloud refused to deploy with *"attached bucket but missing [league/flysystem-aws-s3-v3]"* — the `s3` disk driver was configured but the Flysystem adapter was never `composer require`d. Added it (^3.35).
- **npm ERESOLVE**: `@tailwindcss/vite@4.1.12` only accepted `vite ^5-7`, but the project pins `vite ^8`. Bumped `@tailwindcss/vite` + `tailwindcss` to 4.3.3 (added Vite 8 support in 4.3.0) rather than downgrading Vite — `@vitejs/plugin-react`/`laravel-vite-plugin` already required `vite ^8` even before the bump, so they weren't the cause. Regenerated `package-lock.json` from a clean install, verified `npm ci` + `npm run build` both succeed.

### Fixed: two real onboarding-flow bugs found via live testing (`5146449`, `6b6fac4`)
- **Kingdom pre-assignment bug**: `UserResource` (the page admins actually use to approve characters — `CharacterResource` isn't in the nav) had `kingdom_id` marked `->required()`. Approving = flipping status to active + hitting Save on that same form, so the admin was forced to pre-pick a Kingdom every time, silently overriding the player's own `/choose-kingdom` choice. Fixed by dropping `->required()`.
- **Stuck-on-"approved" bug**: `UserResource`'s status dropdown offered "Approved" as an option, but the app only ever checked for literal `status === 'active'` — picking the naturally-labeled "Approved" choice (there was no dedicated Approve button at the time) left characters stuck on the onboarding wait screen forever. Confirmed live: 2 local characters + the reported `test1` production account were stuck this way. Data migration flipped existing `'approved'` rows to `'active'`; dropdown temporarily trimmed to pending/active/rejected only (superseded by the redesign below, which brings `'approved'` back with real meaning).

### Redesigned: proper Approved→Active state machine + per-stage Reject (`e25a6c5` unrelated Kingdom-reorder work, then `43d4073`)
Owner clarified the *intended* design during live testing: Approve should not jump straight to "Active" — it should force the player through kingdom choice first, only becoming Active once they actually pick one. Separately, Reject turned out to be **completely non-functional** in daily use: `UserResource` had no Approve/Reject actions at all, just the inert status dropdown — picking "Rejected" didn't delete entries, reset stages, or notify the player. The real logic only existed on `CharacterResource`, which nobody sees.
- New state machine: `pending` → `approved` (forced to `/choose-kingdom`, not yet playable) → `active` (set automatically, only by `KingdomSelectionController::store()`, the instant a kingdom is chosen).
- Both `UserResource` and `CharacterResource` now have real Approve/Reject row actions, sharing `CharacterResource::approveCharacter()`/`rejectCharacter()`/`rejectFormSchema()`/`handleRejectSubmit()`.
- Reject is now **per-stage**: admin picks which specific stage(s) failed (only stages actually submitted are offered) with a **separate reason each** — new `character_stats.stage_1/2/3_rejection_reason` columns replace the old single shared `rejection_reason`. Resubmitting a stage clears only that stage's own reason.
- `/onboarding` shows each stage's rejection reason as a bubble on that stage's own card, not one generic top banner.
- Raw status Select kept on `UserResource` as a manual-override escape hatch, per explicit request, alongside the new buttons.
- (Separately, same batch: Kingdoms admin list got drag-to-reorder (`sort_order` column) and "closed" (`is_active=false`) now hard-blocks city entry for regular players — 404, not just hidden from the picker — even for their own home kingdom or an active travel permit. Flagged limitation: direct thread URLs aren't independently kingdom-gated anywhere in the app, closed or not — pre-existing, not addressed.)

All of the above: `php artisan test` 11/11 passing throughout (72 assertions after the last change), verified via `tinker` at each step (schema checks, per-stage reject only touching selected stages, resubmission clearing the right reason, view rendering with the new bubble markup).

### ✅ Resolved: production deploy succeeded, site is live at the real domain
The stale-deploy issue above (production running old code, migrations 3 behind) was resolved — root cause/fix isn't detailed here (owner sorted it on the Laravel Cloud side), but as of now:
- **The site is live at `https://vaelthorn.world`.**
- `APP_URL` is set correctly in the production environment (no longer the `.laravel.cloud` placeholder).
- DNS is pointed via **2 `A` records at Porkbun** (the domain registrar) instead of a `CNAME`.

### Outstanding before opening to real players
- **Not yet re-confirmed**: run `php artisan migrate:status` against production again to verify all migrations (including the 3 from 2026-07-26: stuck-`approved` fix, Kingdom `sort_order`, per-stage rejection reasons) actually show `Ran` now that a real deploy has gone through — this was the whole symptom last time, worth a fresh check rather than assuming it's fixed just because the site loads.
- **`APP_KEY` needs rotating** — the current production value was exposed in a chat screenshot during this session. Generate a fresh one (`php artisan key:generate --show`) and replace it in the Laravel Cloud environment variables. Do this deliberately (not repeatedly) — changing `APP_KEY` invalidates existing sessions/encrypted data, so once is enough, but the currently-live one should be treated as compromised.
- **Unpatched dependency vulnerabilities**, found via `composer audit`/`npm audit` earlier this session, not yet triaged or fixed:
  - `react-router` — 1 high-severity advisory (version untouched by anything done this session).
  - `guzzlehttp/guzzle` + `guzzlehttp/psr7` — 9 medium-severity advisories (transitive dependencies, pre-existing).
- **Database is on a Dev-tier Laravel Cloud plan** — only 1 day of backup retention. Worth deciding whether to upgrade to a Production-tier plan (longer retention) before real player data accumulates that you'd actually want to restore from.

### Suggested next steps (if picked up cold)
1. Re-run `php artisan migrate:status` on production and confirm all migrations show `Ran` — don't assume this is fixed just because the deploy succeeded.
2. Rotate `APP_KEY` in the Laravel Cloud environment now that the old one is known-exposed.
3. Triage the `react-router` (high) and `guzzlehttp`/`psr7` (medium ×9) advisories — decide what's worth bumping vs. accepting for now.
4. Decide on the Dev→Production DB plan upgrade (backup retention) before opening to real testers.
5. Once the above is settled, proceed with the owner's stated plan from the previous update: open to real testers and let their feedback reprioritize the still-open Filament redesign / `users.email` uniqueness items further up this file.

---

## Update 2026-08-02 — Event↔Thread UX system built end-to-end (5 phases) + bug fixes

Owner had a pre-written design doc, `docs/plans/event-thread-ux.md` (its own header said "not started yet" — stale, superseded by this update), laying out 5 phases to fix a real pain point: Threads and Events were two disconnected systems, so a Flash Event's thread couldn't be told apart from a regular one, admins had to hunt down and hand-grant rewards after a post was approved, and there was no way to close an Event without manually locking every thread one at a time. Built all 5 phases in one session, then did a dedicated bug-hunt pass on the finished work. Commits `b65a49a` → `8328e3c` (local `main`, **not yet pushed** — see the correction banner at the top of this file).

**Phase 1 (`b65a49a`)** — `threads.event_id` FK (column already existed, just never wired up); admin-only dropdown on the create-thread form links a Thread to an active Event; `Thread::display_tag` auto-derives its color/label from the linked Event's type (falls back to the pre-existing `thread_category` enum for non-Event threads, unaffected); posting in an Event-linked thread auto-joins `event_participants` and auto-grants that Event's `rewards` the moment the post is approved (deduped per character+reward via `reward_logs`, revocable). Server-side hardened: `event_id` is silently stripped from the request unless the poster is an admin, regardless of what's smuggled into the payload.

Side effects fixed along the way (not in the original plan, found via testing): threads no longer need a *second*, separate "approve the thread" click after its first post is approved (previously two disconnected steps); players now get a notification when their thread is rejected or sent back for edits (methods existed, were never wired up); a plain-EXP zone with no Event linked now shows an upfront warning that the post won't earn EXP, instead of leaving the player to wonder. All three of these turned out to already be logged as open bugs in `docs/audits/2026-07-29-qa-audit.md` (items #4, #6, #8) — marked resolved there too.

**Phase 2 (`7688294`)** — the admin create-thread form now shows a live, no-reload preview once an Event is picked: type color/icon/label, a Flash countdown, and that Event's configured reward list, all sourced from `Event::typeMeta()` (single source of truth, no duplicated color maps).

**Phase 3 (`d203bd0`)** — a "ผูก Event นี้" disclosure appears prominently on the thread's viewer page itself: type/countdown, the reward list, and — per-viewing-character — whether each reward has already been granted (checked live against `reward_logs`, not a fabricated progress counter). **Deviation from the plan**: the plan assumed a "3/5 posts" threshold model; verified via testing that no such threshold exists anywhere in the reward-granting code — it grants on the *first* approved post, full stop — so the UI was built to disclose that real behavior instead of inventing a mechanic that isn't there. Also added: gold rewards previously granted completely silently (item rewards already notified); added the missing notification.

**Phase 4 (`2cba01a`)** — a "ปิด Event" row action on the Filament Events table (visible only while `status = active`): a confirmation modal shows participant count, how many already got a reward, and how many threads will be affected before you commit; confirming sets the Event to `closed` and bulk-locks every one of its still-live threads in one transaction, notifying each thread's participants (reusing the existing `notifyThreadLocked`). **Deviation from the plan**: the plan envisioned this button also *distributing* rewards on close — no longer needed, since Phase 1 already grants them the moment a post is approved, not at Event-close time. The button's actual remaining job is the bulk-lock, which was still a real unsolved pain point.

**Phase 5 (`2cba01a`, same commit)** — two new read-only columns on the same Events table: participant count and `rewarded/participants` ratio. **Deviation from the plan**: the plan assumed this phase would need a brand-new stats table ("ต้องมีตารางข้อมูลใหม่") — turned out not to be true once Phase 1 existed, since `event_participants` + `reward_logs` already hold everything these numbers need. Per-type frequency is answered by the existing type filter on that same table; no new UI needed there either.

**Bug-hunt pass (`8328e3c`)**, after all 5 phases were built and summarized: re-reviewed the whole diff and found 4 real defects, all fixed with a regression test each (31/31 tests passing, 182 assertions total):
- **The one worth flagging hardest**: the Phase 4 "close Event" bulk-lock matched threads by `whereNotIn(status, [locked, archived])` — which also caught threads still `pending`/`draft`/`rejected`, i.e. **never approved by a moderator**. Force-locking one of those flips `Thread::isPubliclyVisible()` to true, publishing unmoderated content to every visitor. Fixed to only touch already-`approved`/`open` threads.
- `Notification::getUrlAttribute()` required `link_id` to be truthy for every link type, but `route('inventory')` takes no id — so item/gold reward notifications (the exact ones added in Phase 1/3) never had a working "view" link, silently, since the method predates this session.
- `Event::getFlashTimeRemainingLabelAttribute()`'s `diffInMinutes()` returns a float in the Carbon version pinned here; confirmed via a live repro that a Flash Event under an hour remaining would show players literally "เหลือ 45.77106495 นาที" instead of a whole number.
- The reward-preview panels (Phases 2 and 3) showed an item name even when its configured `item_quantity` was 0 (a value the admin Reward form does allow) — cosmetic mismatch against the real grant condition, now consistent.

Explicitly **not** touched this session, sitting in the working tree as pre-existing uncommitted work: a Friend system (`FriendController`, `Friendship`/`FriendRequest` models), a NoticeBoard/Islands system, and a small Player Directory + Leaderboard page pair built earlier in the same conversation but unrelated to the Event-Thread work. `ThreadController::moderate()`'s separate `lock` action has the same "doesn't check current status" shape as the Phase 4 bug described above, but is pre-existing code untouched by this session — noted, not fixed.

**Not yet done**: a real browser/UI pass (only automated tests + `tinker`-driven manual verification so far, no browser tooling available in-session); `event_id` validation on thread creation doesn't re-check the Event is still `active` at submit time (very low-risk race given ~20 players/day scale).

# Status.md

Snapshot of in-progress work on the `main` branch. Repo has a single commit ("Initial commit"); everything below is **uncommitted working-tree state** (122 changed files: modified/deleted/new, `git diff --stat` ≈ +1058/−2116 across tracked files, plus new untracked files/dirs).

_Last updated: 2026-07-18_

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
After the 2 fixes above, wrote and passed (26 assertions) a full end-to-end test of the flow Status.md's original "Suggested next steps #1" asked to smoke-test by hand: register → submit all 3 onboarding essays → level auto-promotes to 1 but `status` stays `pending` → `/choose-kingdom` correctly 403s pre-approval → admin `CharacterResource::approveCharacter()` flips `status` to `active` → `/onboarding` now redirects to `/choose-kingdom` → choosing a kingdom sets `kingdom_id`/`current_kingdom_id` → a second choice is correctly rejected (permanent-once-set enforcement) → posting in a home-kingdom city with `require_approval=false` goes live (`approved`) immediately. This exercises `OnboardingService`, `CharacterResource::approveCharacter`, `KingdomSelectionController`, `EnsureKingdomSelected`, and `ThreadController::storeThread` together — the exact cross-cutting path the 2026-07-18 restructure touched.

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

### Not done / still open
- Still all uncommitted — with 8 real bugs now found and fixed across two passes (2 migration-portability, 2 storage/proxy config, 2 concurrency, 1 authorization, 1 enum), the case for committing in logically-scoped chunks soon is strong.
- Did not audit every controller in the app (e.g. `ThreadController`, `EventController`, `NotificationController`, `CharacterController`, `WorldChronicleController` were read in earlier passes but not re-audited for concurrency/auth issues this pass) — focused on the newest, least-battle-tested systems (2026-07-18 economy/onboarding work) plus universal deploy-blockers (migrations, storage, proxy config). A full line-by-line audit of the entire controller layer was not performed.
- Rate limiting (throttle on login/register/AI endpoints) and a superadmin-bootstrap command — flagged in the original pre-deploy checklist — are still not done; out of scope for "bugs," these are missing features/hardening.
- `Filament` admin panel resources (`KingdomResource`, `CraftingRecipeResource`, `TravelPermitResource`) were read during the earlier verification pass but have no automated test coverage — admin-side flows are still only manually-reasoned-about, not exercised by a test.

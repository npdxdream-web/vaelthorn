# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Commands

### Laravel / PHP

```bash
# Start all dev processes (server + queue + pail + vite)
composer dev

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Run tests (clears config cache first)
composer test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Fix PHP code style
./vendor/bin/pint

# Generate Filament resources / panels
php artisan make:filament-resource ModelName
```

### Frontend (React/Vite)

```bash
npm run dev    # Dev server only
npm run build  # Production build
```

### Full Project Setup

```bash
composer setup  # installs deps, creates .env, generates key, migrates, builds assets
```

---

## Architecture

### Stack

- **Backend**: Laravel 11, PHP 8.3, MySQL
- **Admin panel**: Filament 3.3 at `/admin` — amber theme, accessible to non-Player roles only
- **Frontend**: React 18 + TypeScript SPA at `/app` and `/app/*` (catch-all Blade view)
- **Build**: Vite 8 + `@vitejs/plugin-react` + Tailwind CSS 4 via `@tailwindcss/vite`
- **Local dev**: Laragon (vaelthorn.test)
- **Notifications**: Discord Webhook

### Routing split

| Path | Handler |
|---|---|
| `/register`, `/login`, `/logout` | `AuthController` (Blade + session auth) |
| `/onboarding`, `/onboarding/stage`, `/choose-kingdom` | `OnboardingController`, `KingdomSelectionController` |
| `/`, `/cities/*`, `/threads/*`, `/posts/*` | Protected web routes → Blade views |
| `/market/shop*` | `ShopController` (buy crafted goods with gold or materials) |
| `/blacksmith*` | `BlacksmithController` (multi-player crafting orders) |
| `/api/cities/{id}`, `/api/threads/{id}/posts` | JSON API consumed by React |
| `/app`, `/app/{any}` | Catch-all → React SPA |
| `/admin/*` | Filament panel |

> **Terminology note**: as of the 2026-07-18 restructure, `CityController`/`/cities/*` is the **sub-city tier** (was `VillageController`/`/villages/*`), and `Kingdom` is the top-level tier (was the `City` model/`cities` table). See Database Schema below.

### Session separation

Admin panel (`/admin`) uses a **separate session cookie** (`vaelthorn_admin_session`) from the frontend (`vaelthorn_session`) — configured in `config/session.php` and `.env`. This prevents 419 conflicts when a player is logged in on the frontend and opens `/admin` in a new tab.

---

## Database Schema

> **2026-07-18 restructure**: the old 2-tier `cities` (5 kingdoms) → `villages` (sub-areas) hierarchy was renamed to a clearer 2-tier `kingdoms` → `cities` hierarchy (`cities` table was freed up by first renaming it to `kingdoms`, then `villages` → `cities`). Model classes: `Kingdom` (new) and `City` (renamed-in-place from `Village`). This is a straight rename, not a new concept — see [Status.md](Status.md) for the full migration list and current rollout state.

### All tables (in dependency order)

#### Core
| Table | Description |
|---|---|
| `users` | Auth + role. Fields: id, name, email, password, role, timestamps |
| `characters` | One per user. Fields: id, user_id, name, status, role, avatar, kingdom_id (home Kingdom, FK→kingdoms, cascadeOnDelete, null until onboarding+kingdom choice complete), current_kingdom_id (FK→kingdoms, nullOnDelete, last-visited), current_city_id (FK→cities, nullOnDelete, last-visited sub-city), gold, title, backstory, custom_frame, timestamps |
| `character_stats` | 1:1 with character. Fields: id, character_id, level, exp, exp_to_next, hp, mana, str, agi, int, stage_1/2/3_completed (onboarding essay gates), rejection_reason (nullable, set when admin sends onboarding back for revision), timestamps |
| `character_badges` | Badges earned. Fields: id, character_id, badge_id, acquired_at |

#### World
| Table | Description |
|---|---|
| `kingdoms` | 5 kingdoms + Celestia (top-level tier, was `cities`). Fields: id, name, description, color, icon, is_active, timestamps |
| `cities` | Sub-cities per kingdom (was `villages`). Fields: id, kingdom_id (FK→kingdoms), name, description, is_training_zone, write_min_level, write_min_role, require_approval, read_min_level, read_min_role, timestamps |
| `travel_permits` | Grants a character temporary write-access to a non-home Kingdom. Fields: id, item_id (FK→items, its own minted `permit`-type Item), character_id, kingdom_id, granted_by (FK→users), valid_days, activated_at, expires_at, timestamps |

#### Event System
| Table | Description |
|---|---|
| `events` | Admin-created events. Fields: id, title, type (flash/location/story_arc/crisis), kingdom_id (was city_id), created_by, status (draft/active/closed/archived), description, start_at, end_at, timestamps |
| `event_participants` | Characters joined. Fields: id, event_id, character_id, joined_at. Unique: (event_id, character_id) |
| `event_requirements` | Stat/item gates. Fields: id, event_id, req_type (stat/item/level/city), req_key, min_value |

#### RP System
| Table | Description |
|---|---|
| `threads` | RP story threads. Fields: id, city_id (was village_id), event_id, title, exp_override (nullable, explicit per-thread EXP override — see `LevelingService::resolveExpAmount`), status (open/pending/rejected/locked/archived), created_by, timestamps |
| `posts` | RP posts. Fields: id, thread_id, character_id, content, status (pending/approved), timestamps |
| `post_reactions` | Witness System reactions. Fields: id, post_id, character_id, type, timestamps |

#### Economy
| Table | Description |
|---|---|
| `items` | Master item catalog. Fields: id, name, type (weapon/armor/consumable/material/key_item/currency/permit), rarity (common→legendary), description, bonus_str/agi/int/hp/mana, is_tradeable, is_active, timestamps |
| `inventories` | Character inventory. Fields: id, character_id, item_id (FK→items), quantity, timestamps |
| `market_listings` | Player market. Fields: id, seller_id, item_id, quantity, price, status, timestamps |
| `crafting_recipes` | Shop + Blacksmith craft formulas. Fields: id, name, category (shop/blacksmith), result_item_id, result_quantity, gold_cost (shop), craft_duration_minutes (blacksmith), is_active, timestamps |
| `crafting_recipe_materials` | Materials required per recipe (many per recipe). Fields: id, recipe_id (FK→crafting_recipes), material_item_id (FK→items), quantity_required, timestamps |
| `crafting_orders` | A blacksmith crafting job multiple characters can contribute materials to. Fields: id, recipe_id, created_by (FK→characters), token (UUID, shareable), status (open/crafting/ready/claimed), started_at, ready_at, claimed_by, claimed_at, timestamps |
| `crafting_order_contributions` | Per-character material contributions to an order. Fields: id, order_id, character_id, item_id, quantity, timestamps |

#### Rewards
| Table | Description |
|---|---|
| `rewards` | Reward templates per event. Fields: id, event_id, item_id, item_quantity, gold_amount, exp_amount, note, timestamps |
| `reward_logs` | Permanent reward audit log. Fields: id, character_id, event_id, reward_id, item_id, item_quantity, gold_received, exp_received, given_at, timestamps |

#### Progression
| Table | Description |
|---|---|
| `badges` | Master badge catalog. Fields: id, name, icon, description, condition_type, condition_value |

#### Admin Tools
| Table | Description |
|---|---|
| `world_chronicles` | Freeform admin-written world lore, no longer required to link to an Event. Fields: id, event_id (nullable, nullOnDelete), title (nullable), category (nullable: Lore/History/War/Political/Other), content, generated_at, is_published |
| `notifications` | In-app + Discord webhook log. Fields: id, type, target_id, message, channel, sent_at, is_read |

### Key relationships

```
User (1) ──── (1) Character ──── (1) CharacterStat
                    │
                    ├──── (many) Inventory ──── (1) Item
                    ├──── (many) CharacterBadge
                    ├──── (many) TravelPermit ──── (1) Kingdom
                    ├──── (many via event_participants) Event
                    └──── (many) RewardLog

Kingdom (1) ──── (many) City ──── (many) Thread ──── (many) Post
Kingdom (1) ──── (many) Event
Kingdom (1) ──── (many) Character (home kingdom_id / current_kingdom_id)

Event (1) ──── (many) Reward
Event (1) ──── (many) EventRequirement
Event (1) ──── (many) EventParticipant

CraftingRecipe (1) ──── (many) CraftingRecipeMaterial ──── (1) Item
CraftingRecipe (1) ──── (many) CraftingOrder ──── (many) CraftingOrderContribution
```

---

## Domain Model & Game Mechanics

### Core game loop
```
Admin creates Event in a Kingdom
  → Players write RP Posts in Threads
  → Admin approves Posts
  → Rewards auto-sync to character Inventory + Stats
```

### Onboarding flow (level 0 → 1)

Pure 3-essay review, no in-game posting requirement. `character_stats.stage_1/2/3_completed` gate progress; `OnboardingService` handles submission + promotion:

1. Character registers at level 0, no `kingdom_id`.
2. Player submits 3 stage essays (`OnboardingController::submitStage`, stored as `OnboardingEntry` rows) via `/onboarding/stage`.
3. Admin reviews each character in `CharacterResource`/`EditCharacter` — **Approve** or **Reject with a required reason** (`rejection_reason`). Reject is *not* terminal: it deletes the character's `OnboardingEntry` rows, resets all 3 stage flags to false, and notifies the player (`NotificationService::notifyOnboardingRejected`) — a normal "send back for revision" loop, character `status` stays `pending`.
4. Once all 3 stages are approved, `OnboardingService::checkAllComplete()` promotes the character to level 1.
5. Level-1 character with no `kingdom_id` is redirected to `/choose-kingdom` (`KingdomSelectionController`) to pick a **permanent** home Kingdom (enforced client- and server-side — 403 if already set).
6. Only after a kingdom is chosen can the character post in Threads (`EnsureKingdomSelected` / `kingdom.selected` middleware gate).

> Superseded: an older 2-stage system (Stage A = 3 auto-approved posts in an `is_training_zone` City to fill "onboarding slots"; Stage B = earn `stage_b_exp` in a designated onboarding Event) was fully removed 2026-07-18 — no more `onboarding_slots` table, no training-zone/event-based EXP gating pre-level-1.

### Character rank (computed, not stored)
`auto_rank` is an appended attribute on Character model, derived from approved post count:

| Posts | Rank |
|---|---|
| 0–4 | Stranger |
| 5–19 | Wanderer |
| 20–49 | Traveler |
| 50–99 | Veteran |
| 100+ | Legend |

### Event types
| Type | Duration | Notes |
|---|---|---|
| flash | 2–6 hours | Auto-close when time expires |
| location | 1–2 weeks | Tied to specific city |
| story_arc | 1+ month | Changes world canon permanently |
| crisis | 24–48 hours | Unannounced emergency |

### Soft class system
Players allocate stat points on level-up. Stat thresholds + items gate event eligibility via `event_requirements`. No hard class selection — path emerges from stat choices (e.g. high Mana → mage path, high AGI → assassin path).

### Witness System
`post_reactions` table powers the emotional core: at least one person witnesses and reflects back a character's growth. This is what makes players feel "seen."

### 5 Kingdoms
| Kingdom | Capital (City) | Identity |
|---|---|---|
| Silvaria | Mokagi | Forest, magic |
| Aurantia | Viente | Plains, knights, law |
| Kalif | Akancia | Desert, assassins, central market |
| Frostwell | Alasia | Snow, warriors |
| Kyoren | Ainu | Eastern, spiritual |
| Celestia | — | Neutral landmark |

A character's home Kingdom is permanent once chosen (see Onboarding flow above). Writing in a non-home Kingdom's Cities requires an active `TravelPermit` for that Kingdom, unless the City is the character's `current_kingdom_id`/`current_city_id` (last-visited) or the character is moderator+.

### Economy: Market, Shop, Blacksmith
| System | Controller | Mechanic |
|---|---|---|
| Player Market | `MarketController` | Peer-to-peer listing/buying with gold |
| Shop | `ShopController` | Buy admin-defined `crafting_recipes` (category `shop`) instantly, paying gold **or** turning in materials |
| Blacksmith | `BlacksmithController` | Multi-player `CraftingOrder`: any character starts an order for a `blacksmith`-category recipe (shareable token URL), others contribute required materials (`CraftingOrderContribution`) until complete, then it "cooks" for `craft_duration_minutes` before the creator/contributors can claim the result item |

---

## User Roles (`app/Enums/UserRole.php`)

`superadmin` → `admin` → `moderator` → `player`

- Only non-Player roles can access `/admin`
- SuperAdmin is the only role that can view/edit UserResource
- `isAtLeastAdmin()` helper used for access checks
- `isSuperAdmin()` for superadmin-only actions

---

## Avatar Frame System

SVG Art Deco corner frames — overlay on top of avatar image, no database table needed. Frame is derived directly from character role in Blade component.

**Component**: `<x-avatar-frame role="{{ $character->role }}" initial="T" :size="140" />`

| Role | Color | Style |
|---|---|---|
| guardian / admin | #c8a84b | fan corners (gold) |
| knight / mage | #6890c8 / #9b8fc8 | step corners |
| archer | #c87c3a | fan corners (orange) |
| healer | #6abf88 | step corners (green) |
| wanderer | #8ab0c8 | simple L-bracket |

Sub-components: `resources/views/components/frames/_fan-corner.blade.php`, `_step-corner.blade.php`, `_simple-corner.blade.php`

---

## Filament Resources (`app/Filament/Resources/`)

| Resource | Notes |
|---|---|
| UserResource | SuperAdmin only. Password field uses `dehydrated(fn($s)=>filled($s))` + `dehydrateStateUsing(Hash::make)` — never load hash into field |
| CharacterResource | Admin approve/reject onboarding (reject requires a `rejection_reason`, non-terminal — see Onboarding flow). Has `BadgesRelationManager` + `InventoryRelationManager` |
| EventResource | Full CRUD + inline Rewards Repeater + Requirements Repeater. `created_by` auto-set in CreateEvent page |
| ItemResource | Master item catalog |
| RewardResource | Reward templates per event |
| KingdomResource | Top-level Kingdom CRUD (replaces old CityResource). SuperAdmin-only delete |
| CityResource | Sub-city CRUD (replaces old VillageResource) |
| CraftingRecipeResource | Shop/Blacksmith recipe CRUD with inline materials Repeater (auto-manages `crafting_recipe_materials`) |
| TravelPermitResource | Issuing a permit **mints a new dedicated `permit`-type Item** per issuance (not a shared item) and grants it via Inventory — see `CreateTravelPermit::mutateFormDataBeforeCreate` |
| ThreadResource, PostResource | Standard CRUD |
| WorldChronicleResource | Freeform `title`/`category` fields, no longer requires an Event link; `generated_at` auto-set on create |

Custom Filament login page: `app/Filament/Pages/Auth/Login.php`

**RewardLog has no Filament Resource** — read-only audit data, never editable by Admin.

---

## Frontend (`resources/frontend/vaelthorn-ui/`)

Entry: `main.tsx` → `app/App.tsx` (React Router) → routes in `app/routes.ts`

UI components: `app/components/ui/` — Radix UI primitives + Tailwind
Libraries: Recharts (charts), React DnD (drag-drop), React Hook Form (forms)

**Note**: as of the 2026-07-18 restructure, the SPA has no in-app city/kingdom browsing UI (map icons removed from `Navbar`/`BottomNav`/`Footer`). City browsing happens via Blade (`/cities/{id}`); the SPA's `CityPage.tsx` (route `city/:cityId`, was `VillagePage.tsx`/`village/:villageId`) is reachable only by direct link, not primary nav. Market/Events/Chronicles/Rewards nav items render as plain `<a>` (full page load) rather than React Router `<Link>` since those pages aren't SPA-ported.

---

## Key Conventions

- **Registration**: creates User + Character + CharacterStat atomically in `AuthController::register`
- **Moderation**: thread approve/reject, post approve/delete restricted to `admin`/`moderator` via inline middleware
- **Password**: never load existing hash into Filament form field — use `dehydrated(fn($s)=>filled($s))` only
- **Reward distribution**: always write to `reward_logs` first before updating inventory/stats — prevents double-reward
- **exp_to_next**: stored in `character_stats`, not computed — Admin/system sets the threshold per level
- **item_id in inventories**: always FK→items, never free-text item_name
- **current_city_id / current_kingdom_id in characters**: FK with nullOnDelete — never plain unsignedBigInteger
- **kingdom_id in characters**: home Kingdom, permanent once set (post-onboarding) — treat as immutable outside `KingdomSelectionController::store`
- **Onboarding rejection**: never set character `status` to a terminal `rejected` value — reset stage flags + store `rejection_reason` instead, so the player can resubmit (see `CharacterResource::rejectCharacter`)
- **Crafting order completion**: check materials-complete and flip `crafting_orders.status` inside the same DB transaction as the triggering contribution — see `BlacksmithController::contribute`

---

## Testing

PHPUnit, two suites (`Unit`, `Feature`) under `tests/`. Test environment: in-memory SQLite (configured in `phpunit.xml`). No JS test runner configured.

---

## Target Scale

~20 active players/day, ~80 posts/day. Admin workload reduction is a critical design goal. AI-assisted tooling (post summarization, chronicle generation) was considered but is out of scope — not implemented and not planned.

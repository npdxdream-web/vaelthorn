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
- **AI**: Anthropic Claude API (summarize RP posts, generate World Chronicle, writing assistant)
- **Notifications**: Discord Webhook

### Routing split

| Path | Handler |
|---|---|
| `/register`, `/login`, `/logout` | `AuthController` (Blade + session auth) |
| `/`, `/villages/*`, `/threads/*`, `/posts/*` | Protected web routes → Blade views |
| `/api/villages/{id}`, `/api/threads/{id}/posts` | JSON API consumed by React |
| `/app`, `/app/{any}` | Catch-all → React SPA |
| `/admin/*` | Filament panel |

### Session separation

Admin panel (`/admin`) uses a **separate session cookie** (`vaelthorn_admin_session`) from the frontend (`vaelthorn_session`) — configured in `config/session.php` and `.env`. This prevents 419 conflicts when a player is logged in on the frontend and opens `/admin` in a new tab.

---

## Database Schema

### All tables (in dependency order)

#### Core
| Table | Description |
|---|---|
| `users` | Auth + role. Fields: id, name, email, password, role, timestamps |
| `characters` | One per user. Fields: id, user_id, name, status, role, avatar, current_city_id (FK→cities, nullOnDelete), gold, title, backstory, custom_frame, timestamps |
| `character_stats` | 1:1 with character. Fields: id, character_id, level, exp, exp_to_next, hp, mana, str, agi, int, timestamps |
| `character_badges` | Badges earned. Fields: id, character_id, badge_id, acquired_at |

#### World
| Table | Description |
|---|---|
| `cities` | 5 kingdoms. Fields: id, name, kingdom, description, is_locked, timestamps |
| `villages` | Sub-areas per city. Fields: id, city_id, name, description, timestamps |

#### Event System
| Table | Description |
|---|---|
| `events` | Admin-created events. Fields: id, title, type (flash/location/story_arc/crisis), city_id, created_by, status (draft/active/closed/archived), description, start_at, end_at, timestamps |
| `event_participants` | Characters joined. Fields: id, event_id, character_id, joined_at. Unique: (event_id, character_id) |
| `event_requirements` | Stat/item gates. Fields: id, event_id, req_type (stat/item/level/city), req_key, min_value |

#### RP System
| Table | Description |
|---|---|
| `threads` | RP story threads. Fields: id, village_id, event_id, title, status (open/pending/rejected/locked/archived), created_by, timestamps |
| `posts` | RP posts. Fields: id, thread_id, character_id, content, status (pending/approved), ai_summary, timestamps |
| `post_reactions` | Witness System reactions. Fields: id, post_id, character_id, type, timestamps |

#### Economy
| Table | Description |
|---|---|
| `items` | Master item catalog. Fields: id, name, type (weapon/armor/consumable/material/key_item/currency), rarity (common→legendary), description, bonus_str/agi/int/hp/mana, is_tradeable, is_active, timestamps |
| `inventories` | Character inventory. Fields: id, character_id, item_id (FK→items), quantity, timestamps |
| `market_listings` | Player market. Fields: id, seller_id, item_id, quantity, price, status, timestamps |
| `crafting_recipes` | Craft formulas. Fields: id, result_item_id, material_item_id, quantity_needed |

#### Rewards
| Table | Description |
|---|---|
| `rewards` | Reward templates per event. Fields: id, event_id, item_id, item_quantity, gold_amount, exp_amount, note, timestamps |
| `reward_logs` | Permanent reward audit log. Fields: id, character_id, event_id, reward_id, item_id, item_quantity, gold_received, exp_received, given_at, timestamps |

#### Progression
| Table | Description |
|---|---|
| `badges` | Master badge catalog. Fields: id, name, icon, description, condition_type, condition_value |

#### AI & Admin Tools
| Table | Description |
|---|---|
| `ai_logs` | Claude API cost tracking. Fields: id, type, input_tokens, output_tokens, cost_thb, reference_id, timestamps |
| `world_chronicles` | AI-generated world history after events. Fields: id, event_id, content, generated_at, is_published |
| `notifications` | In-app + Discord webhook log. Fields: id, type, target_id, message, channel, sent_at, is_read |

### Key relationships

```
User (1) ──── (1) Character ──── (1) CharacterStat
                    │
                    ├──── (many) Inventory ──── (1) Item
                    ├──── (many) CharacterBadge
                    ├──── (many via event_participants) Event
                    └──── (many) RewardLog

City (1) ──── (many) Village ──── (many) Thread ──── (many) Post
City (1) ──── (many) Event

Event (1) ──── (many) Reward
Event (1) ──── (many) EventRequirement
Event (1) ──── (many) EventParticipant
```

---

## Domain Model & Game Mechanics

### Core game loop
```
Admin creates Event in a City
  → Players write RP Posts in Threads
  → Admin approves Posts
  → Rewards auto-sync to character Inventory + Stats
```

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

### 5 Kingdoms (cities)
| Kingdom | Capital | Identity |
|---|---|---|
| Silvaria | Mokagi | Forest, magic |
| Aurantia | Viente | Plains, knights, law |
| Kalif | Akancia | Desert, assassins, central market |
| Frostwell | Alasia | Snow, warriors |
| Kyoren | Ainu | Eastern, spiritual |
| Celestia | — | Neutral landmark |

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
| CharacterResource | Admin approve/reject character status |
| EventResource | Full CRUD + inline Rewards Repeater + Requirements Repeater. `created_by` auto-set in CreateEvent page |
| ItemResource | Master item catalog |
| RewardResource | Reward templates per event |
| CityResource, VillageResource, ThreadResource, PostResource | Standard CRUD |

Custom Filament login page: `app/Filament/Pages/Auth/Login.php`

**RewardLog has no Filament Resource** — read-only audit data, never editable by Admin.

---

## Frontend (`resources/frontend/vaelthorn-ui/`)

Entry: `main.tsx` → `app/App.tsx` (React Router) → routes in `app/routes.ts`

UI components: `app/components/ui/` — Radix UI primitives + Tailwind
Libraries: Recharts (charts), React DnD (drag-drop), React Hook Form (forms)

---

## Key Conventions

- **Registration**: creates User + Character + CharacterStat atomically in `AuthController::register`
- **Moderation**: thread approve/reject, post approve/delete restricted to `admin`/`moderator` via inline middleware
- **Password**: never load existing hash into Filament form field — use `dehydrated(fn($s)=>filled($s))` only
- **Reward distribution**: always write to `reward_logs` first before updating inventory/stats — prevents double-reward
- **exp_to_next**: stored in `character_stats`, not computed — Admin/system sets the threshold per level
- **item_id in inventories**: always FK→items, never free-text item_name
- **current_city_id in characters**: FK→cities with nullOnDelete — never plain unsignedBigInteger

---

## Testing

PHPUnit, two suites (`Unit`, `Feature`) under `tests/`. Test environment: in-memory SQLite (configured in `phpunit.xml`). No JS test runner configured.

---

## Target Scale

~20 active players/day, ~80 posts/day. Admin workload reduction is a critical design goal — AI assists with summarizing RP for review, drafting event descriptions, and generating World Chronicles after events close.

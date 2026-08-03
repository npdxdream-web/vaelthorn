# CLAUDE.md

คู่มืออ้างอิงสำหรับ Claude Code เมื่อทำงานกับ repo นี้
**ตรวจสอบกับโค้ดจริงล่าสุด: 2026-08-03** (route:list, SHOW COLUMNS ของ DB จริง, grep ทั้ง repo)

> เอกสารอื่น (changelog, roadmap, แผนฟีเจอร์, QA audit) อยู่ใน [`docs/`](docs/INDEX.md)

---

## ⚠️ กฎการดูแลไฟล์นี้ — อ่านก่อนแก้

ไฟล์นี้เคยมีปัญหาร้ายแรง: มีการเขียนว่า "ระบบ X ทำเสร็จแล้ว" ทั้งที่ไม่เคยทำงานจริง
(กรณี `UseAdminSessionCookie` — เขียนไว้ว่าแยก session cookie แล้ว แต่ middleware ไม่เคยถูกเรียกใช้)
กฎด้านล่างนี้เขียนขึ้นเพื่อกัน "เอกสารโกหก" ที่ทำให้เข้าใจว่าระบบทำงานตามนี้ทั้งที่ยังไม่จริง

**กฎ 3 ข้อ:**

1. **เขียนได้เฉพาะสิ่งที่ยืนยันจากโค้ดจริงแล้ว** — ห้ามเขียนจากความจำ จากแผนเดิม หรือจากการคาดเดา
2. **ของที่ยังไม่เสร็จ ต้องอยู่ในหมวด "Known Issues & Debt" เท่านั้น** ห้ามปนกับส่วนที่อธิบายระบบที่ใช้งานจริง
3. **แก้โค้ดเรื่องไหน อัปเดตเอกสารเรื่องนั้นใน commit เดียวกัน** — ค้าง commit มักจะไม่ถูกอัปเดต

---

## Commands

```bash
# รัน dev ทั้งหมดพร้อมกัน (server + queue + pail + vite)
composer dev

php artisan migrate
php artisan db:seed

composer test                                       # ล้าง config cache ก่อนรันเทสต์
php artisan test tests/Feature/FriendshipTest.php    # รันไฟล์เดียว

./vendor/bin/pint                                    # จัด code style
php artisan make:filament-resource ModelName

npm run dev / npm run build                          # React SPA
composer setup                                       # ติดตั้งใหม่ทั้งหมดตั้งแต่ต้น
```

---

## Stack & Environments

| | |
|---|---|
| **Backend** | Laravel 13, PHP 8.3+, MySQL |
| **Admin panel** | Filament 3.3 ที่ `/admin` — guard `admin`, ฟอนต์ Noto Sans Thai |
| **Frontend หลัก** | **Blade** — หน้าเว็บทั้งหมดที่ผู้เล่นใช้จริงเป็น Blade |
| **React SPA** | React 18 + TS ที่ `/app` และ `/app/*` เท่านั้น (ขอบเขตจำกัด — ดูรายละเอียดหมวด Frontend) |
| **Build** | Vite 8 + `@vitejs/plugin-react` + Tailwind CSS 4 (`@tailwindcss/vite`) |
| **Local dev** | Laragon — `vaelthorn.test` |
| **Production** | Laravel Cloud — `vaelthorn.world` |
| **แจ้งเตือนภายนอก** | Discord Webhook (`DISCORD_WEBHOOK_URL`) |

### Production — ข้อควรระวังที่เคยทำให้เสียเวลา

- **Object storage ไม่ auto-inject** — Laravel Cloud ไม่ set ค่า bucket ให้อัตโนมัติ
  ต้องตั้ง `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_DEFAULT_REGION`,
  `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`, `AWS_URL` ด้วยมือใน **Custom environment variables**
- `config/filesystems.php` สลับ disk `public` เป็น driver `s3` **อัตโนมัติเมื่อมี `AWS_BUCKET`**
  ถ้าไม่มีจะ fallback เป็น local — เพราะฉะนั้นถ้าลืมตั้งค่าใน production จะเขียนไฟล์ลง container ชั่วคราวโดยไม่รู้ตัว
- **ห้ามพิมพ์ค่า secret ลงแชทกับ AI** โดยเฉพาะ 2 ตัว (`APP_KEY`, `AWS_SECRET`) ต้อง rotate ทันทีถ้าหลุด
  ให้ copy จากที่เก็บลับภายนอกเสมอ
- `SESSION_COOKIE` **ไม่มีใน `.env.example`** — ถ้าไม่ตั้งจะ fallback เป็น `vaelthorn_session`

---

## Routing Map

หน้าเว็บทั้งหมดเป็น Blade ผ่าน middleware `auth` บางส่วนมี `kingdom.selected` เพิ่ม

### Auth & Onboarding
| URI | Controller |
|---|---|
| `/register`, `/login`, `/logout` | `AuthController` |
| `/onboarding`, `/onboarding/stage` | `OnboardingController` |
| `/choose-kingdom` | `KingdomSelectionController` |
| `/pending` | closure → redirect ไป onboarding (legacy alias) |

### หน้าผู้เล่น
| หมวด | URI | Controller |
|---|---|---|
| หน้าแรก | `/` | `HomeController` |
| เมือง/กระทู้ | `/cities/{id}`, `/cities/{id}/threads[/create]`, `/threads/{id}[/edit]`, `/threads/{id}/{moderate,restore}`, `DELETE /threads/{id}/force` | `CityController`, `ThreadController` |
| โพสต์ | `/threads/{id}/posts`, `/posts/{id}/approve`, `/posts/{id}/edit` | `ThreadController` |
| ตัวละคร | `/character`, `/character/edit`, `/character/stat`, `/character/{id}` | `CharacterController` |
| เพื่อน | `/character/{character}/friend-request`, `DELETE .../friend`, `/friend-requests/{id}/{accept,reject}` | `FriendController` |
| Event | `/events[/{id}]`, `/events/{id}/{join,leave}` | `EventController` |
| คลังของ | `/inventory`, `/inventory/permits/{id}/activate` | `InventoryController` |
| แจ้งเตือน | `/notifications`, `.../{id}/open`, `.../{id}/read`, `.../read-all` | `NotificationController` |
| จดหมายถึงสภา | `/council/letters[/{id}]`, `.../reply` | `CouncilLetterController` |
| จดหมายเหตุ | `/archive`, `/chronicles[/{id}]` | `ArchiveController`, `WorldChronicleController` |
| ทำเนียบ/อันดับ | `/players`, `/leaderboard` | `PlayerController` |
| รางวัล/กิจกรรม | `/rewards`, `/activity` | `RewardHistoryController`, `RecentActivityController` |
| เศรษฐกิจ | `/market...`, `/market/shop...`, `/blacksmith...` | `MarketController`, `ShopController`, `BlacksmithController` |
| ป้ายประกาศ | `/notice-boards/{id}`, `.../threads[/create]`, `/notice-board-threads/{id}`, `.../posts` | `NoticeBoardController`, `NoticeBoardThreadController` |

### API (React SPA เรียกใช้)
`GET /api/cities/{id}` และ `GET /api/threads/{id}/posts` เป็น public
ส่วน `POST /api/threads/{id}/posts`, `/api/threads/{id}/moderate`, `/api/posts/{id}/approve`,
`PUT|DELETE /api/posts/{id}` ต้อง auth — ทั้งหมดอยู่ใน `ThreadController`

### Admin
`/admin/*` — Filament panel, guard `admin`, ผ่าน `EnsureAdminAccess` (ต้อง moderator ขึ้นไป)
มี custom page เพิ่ม: `/admin/thread-trash`

---

## Database

**41 ตารางใน DB จริง** (รวมตารางระบบของ Laravel: migrations, sessions, cache, cache_locks, jobs, job_batches, failed_jobs, password_reset_tokens)

> ⚠️ คอลัมน์ที่ระบุด้านล่างทั้งหมดมาจาก `SHOW COLUMNS` บน DB จริง — เพื่อยืนยัน
> อย่าเชื่อชื่อไฟล์ migration เพียงอย่างเดียว ให้ query ดูของจริงก่อนเสมอ

### Core

**`users`** — auth + role
**`characters`** — 1 ตัวต่อ user
```
id, user_id(unique), kingdom_id, name, backstory, status(default 'pending'),
avatar, current_kingdom_id, current_city_id, gold, title, custom_frame, timestamps
```
> **ไม่มีคอลัมน์ `city_id`** — มีแต่ `kingdom_id` (บ้านเกิด) และ `current_city_id` (ที่อยู่ล่าสุด)

**`character_stats`** — 1:1 กับ character
```
id, character_id, level, hp, mana, agi, str, int, stat_points_available,
daily_exp, daily_exp_date, exp, exp_to_next,
stage_1/2/3_completed, stage_1/2/3_rejection_reason, timestamps
```
> เหตุผลการปฏิเสธ onboarding เก็บ **ต่อด่าน** (3 คอลัมน์แยก) ไม่ใช่ตัวเดียวรวม

**`character_badges`**, **`badges`** — ระบบให้เหรียญตรา
**`onboarding_entries`** — คำตอบเรียงความ 3 ด่าน

### World

**`kingdoms`**
```
id, name, description, color(default #ffffff), icon, is_active, sort_order, timestamps
```
**`cities`** — เมืองย่อยใต้ kingdom
```
id, kingdom_id, name, description, banner_image, is_training_zone,
write_min_level, write_min_role, require_approval, read_min_level, read_min_role, timestamps
```
**`travel_permits`** — สิทธิ์ให้เดินทางข้ามอาณาจักรที่ไม่ใช่บ้านเกิด

### RP

**`threads`**
```
id, city_id, event_id, exp_override, created_by, title, banner_image, location_label,
status, thread_category, moderation_message, deleted_at, archived_at, timestamps
```
> มี **soft delete** (`deleted_at`) และ `thread_category` (ดู Enums)

**`posts`** — โพสต์ RP (`status`: pending/approved)

### Event

`events`, `event_participants`, `event_requirements`

### Economy

`items`, `inventories`, `market_listings`,
`crafting_recipes`, `crafting_recipe_materials`,
`crafting_orders`, `crafting_order_contributions`

### Rewards

`rewards` (template ต่อ event), `reward_logs` (audit log ถาวร — มี `post_id` ผูกกลับไปยังโพสต์ต้นทาง)

### Social & Admin tools

**`friend_requests`** — `id, from_character_id, to_character_id, status(pending/accepted/rejected), pair_key(UNIQUE), timestamps`
> `pair_key` unique เพื่อกันสร้างข้อมูลซ้ำจากคำขอ 2 ฝั่งพร้อมกัน — กันเคส race condition

**`friendships`** — `id, character_id_1, character_id_2, created_at` (ไม่มี `updated_at`)
> เก็บเป็น **2 แถวต่อ 1 คู่เพื่อน** (ทิศทางละแถว) — ตอน unfriend ต้องลบทั้ง 2 แถว

**`notifications`** — `id, user_id, type, title, body, data(json), link_type, link_id, read_at, timestamps`
**`council_letters`** — จดหมายถึงสภา (ตอบกลับได้จากฝั่ง moderator+)
**`notice_boards`**, **`notice_board_threads`**, **`notice_board_posts`** — ระบบป้ายประกาศ (เดิมชื่อ Islands)
**`world_chronicles`** — บันทึกโลกที่แอดมินเขียนเอง
**`app_settings`** — key/value store ทั่วไป (`AppSetting::get()/set()`)

### ตารางที่ตายแล้ว

**`post_reactions`** — เหลือจาก Witness System ที่ถอดออกแล้ว ไม่มี route/controller เรียกใช้
มีแต่ Model relation (`Post::reactions()`, `PostReaction`) ค้างอยู่

> `onboarding_slots` และ `ai_logs` ถูกลบออกจาก DB จริงเรียบร้อยแล้ว (มี migration drop ยืนยัน)

### ความสัมพันธ์หลัก

```
User (1) ── (1) Character ── (1) CharacterStat
                 ├── (many) Inventory ── (1) Item
                 ├── (many) CharacterBadge
                 ├── (many) TravelPermit ── (1) Kingdom
                 ├── (many) OnboardingEntry
                 ├── (many) RewardLog
                 ├── (many) FriendRequest (sent / received)
                 ├── (many) Friendship
                 └── (many via event_participants) Event

Kingdom (1) ── (many) City ── (many) Thread ── (many) Post
Kingdom (1) ── (many) Event
Thread  (many) ── (1) Event          → ต้องผูกถึงจะให้ reward ทำงาน

Event (1) ── (many) Reward / EventRequirement / EventParticipant
CraftingRecipe (1) ── (many) CraftingRecipeMaterial ── (1) Item
CraftingRecipe (1) ── (many) CraftingOrder ── (many) CraftingOrderContribution
```

---

## Game Mechanics

### Core loop
```
แอดมินสร้าง Event ในอาณาจักร
  → ผู้เล่นผูก Thread เข้ากับ Event
  → ผู้เล่นเขียนโพสต์ RP
  → แอดมินอนุมัติโพสต์
  → LevelingService แจก EXP + item + gold ให้อัตโนมัติ
```
**Thread ต้องผูกกับ Event ผ่าน `threads.event_id` เท่านั้น** reward ถึงจะทำงาน
โพสต์ที่อนุมัติในกระทู้ที่ไม่ผูก event จะไม่ได้อะไร (โดยตั้งใจ)

### Onboarding (level 0 → 1)

ระบบเรียงความ 3 ด่านล้วน ไม่มีการโพสต์ในเกมเป็นเงื่อนไข

1. สมัคร → character level 0, `status = pending`, ไม่มี `kingdom_id`
2. ส่งเรียงความ 3 ด่าน (`OnboardingController::submitStage` → `OnboardingEntry`)
3. แอดมินตรวจใน `CharacterResource`/`UserResource` — **Approve หรือ Reject เป็นรายด่าน**
   (เลือก checkbox ว่าด่านไหนไม่ผ่าน + ใส่เหตุผลลง `stage_N_rejection_reason`)
   Reject **ไม่ terminal** — reset flag ของด่านที่เลือกทั้งหมดให้ผู้เล่นแก้ไขใหม่ `status` ยังเป็น `pending`
4. ครบ 3 ด่าน → `OnboardingService::checkAllComplete()` เลื่อนเป็น level 1
5. level 1 ที่ยังไม่มี `kingdom_id` → บังคับไป `/choose-kingdom`
6. เลือกอาณาจักรแล้วเท่านั้นถึงจะโพสต์ได้ (middleware `kingdom.selected`)

> **ค่าที่เปลี่ยนไม่ได้**: `characters.kingdom_id` ถือเป็น immutable ยกเว้น `KingdomSelectionController::store`
> (พยายามตั้งค่าครั้งที่ 2 ได้ 403)

### EXP — ลำดับความสำคัญใน `LevelingService::resolveExpAmount()`
```
1. threads.exp_override        (ถ้ากำหนดไว้ ใช้เท่านี้)
2. โซน auto-approve            → flat 1
3. reward ของ event ที่ผูกอยู่
4. ไม่เข้าเงื่อนไขไหนเลย       → 0
```
มี **daily EXP cap ต่อ level** คิดตาม timezone `Asia/Bangkok` (`daily_exp`, `daily_exp_date`)

### Rank ตัวละคร (คำนวณสด ไม่เก็บใน DB)
`auto_rank` เป็น appended attribute บน Character คำนวณจากจำนวนโพสต์ที่อนุมัติแล้ว

| โพสต์ | Rank |
|---|---|
| 0–4 | Stranger |
| 5–19 | Wanderer |
| 20–49 | Traveler |
| 50–99 | Veteran |
| 100+ | Legend |

### 5 อาณาจักร

> อ้างอิงจาก lore/seeder — **ยังไม่ได้ตรวจสอบข้อมูลจากตาราง `kingdoms`/`cities` จริง**
> ก่อนใช้ชื่อหรือ id เป๊ะๆ ให้ query ดูก่อนเสมอ อย่าฮาร์ดโค้ดจากตารางนี้

| อาณาจักร | เมืองหลวง | เอกลักษณ์ |
|---|---|---|
| Silvaria | Mokagi | ป่า เวทมนตร์ |
| Aurantia | Viente | ที่ราบ อัศวิน กฎหมาย |
| Kalif | Akancia | ทะเลทราย นักฆ่า ตลาดกลาง |
| Frostwell | Alasia | หิมะ นักรบ |
| Kyoren | Ainu | ตะวันออก จิตวิญญาณ |
| Celestia | — | แลนด์มาร์คกลาง |

การเขียนในเมืองของอาณาจักรอื่นต้องมี `TravelPermit` ที่ active ยกเว้นตรงกับ `current_kingdom_id`/`current_city_id`
หรือเป็น moderator ขึ้นไป

### Event types

| Type | ระยะเวลา | หมายเหตุ |
|---|---|---|
| flash | 2–6 ชม. | `exp_reward` **ต้อง = 1** (validation ใน EventResource) |
| location | 1–2 สัปดาห์ | ผูกกับเมือง |
| story_arc | 1 เดือนขึ้นไป | เปลี่ยน canon ของโลกถาวร |
| crisis | 24–48 ชม. | ไม่ประกาศล่วงหน้า |

ประเภทอื่นนอกจาก flash: `exp_reward` ต้องอยู่ในช่วง **3–15**

### เศรษฐกิจ

| ระบบ | Controller | กลไก |
|---|---|---|
| ตลาดผู้เล่น | `MarketController` | ประกาศขาย/ซื้อต่อกันด้วย gold |
| ร้านค้า | `ShopController` | ซื้อ recipe หมวด `shop` ทันที จ่าย gold **หรือ** ส่งวัตถุดิบ |
| ช่างตีเหล็ก | `BlacksmithController` | `CraftingOrder` หลายคนช่วยกันลงวัตถุดิบผ่าน token URL → ครบแล้ว "หลอม" ตาม `craft_duration_minutes` → เคลม |

### Soft class
ไม่มีการเลือกอาชีพตายตัว — แจกแต้ม stat ตอน level up แล้ว `event_requirements` เป็นตัวกำหนดว่าใครเข้าร่วม event ไหนได้
เส้นทางเกิดจากการเลือกลงแต้ม (Mana สูง → สายเวท, AGI สูง → สายลอบเร้น)

---

## User Roles

`superadmin` → `admin` → `moderator` → `player` (`app/Enums/UserRole.php`)

- Player เข้า `/admin` ไม่ได้ (`canAccessPanel()`)
- `EnsureAdminAccess` ต้องการ **moderator ขึ้นไป**
- helper: `isSuperAdmin()`, `isAtLeastAdmin()`, `isAtLeastModerator()`, `isAdminGroup()`
- **เกือบทุก Filament Resource ตั้ง `canDelete()` = superadmin เท่านั้น** — pattern มาตรฐาน ทำตามทุกครั้งที่สร้าง resource ใหม่

---

## Enums

มีแค่ **2 ไฟล์** ใน `app/Enums/` — ที่เหลือใช้เป็น string/enum ระดับคอลัมน์ DB ล้วน
(ไม่มี Enum สำหรับ thread status, event type, item rarity)

**`UserRole`** — superadmin / admin / moderator / player

**`ThreadCategory`** (implements HasLabel, HasColor) — 8 ค่า:
`announcement`, `system_update`, `event`, `rule_policy`, `guide`, `faq`, `bug_report`, `general_discussion`
มี `getColor()` สำหรับ Filament และ `bgHex()`/`textHex()` สำหรับ badge ฝั่งเว็บ

---

## Services

| Service | หน้าที่ |
|---|---|
| `LevelingService` | หัวใจของระบบรางวัล: `handlePostApproved()`, `addExp()` (มี daily cap), `resolveExpAmount()`, `distributeEventRewards()` (แจกครั้งเดียวต่อ reward template ต่อ character), `promoteToLevel1()`, `checkLevelUp()` |
| `OnboardingService` | `submitStage()`, `nextStage()`, `checkAllComplete()` |
| `NotificationService` | `notifyXxx()` ~20 method — เขียน `Notification::create()` ตรงๆ **ไม่มี queue/job** |
| `FriendService` | `sendRequest()` (กันขอตัวเอง/ซ้ำ + cooldown 1 วันหลังถูกปฏิเสธ), `accept()` (สร้าง Friendship 2 แถวใน transaction แล้วลบ request), `reject()`, `unfriend()` |

---

## Middleware

| Middleware | Alias | หน้าที่ | สถานะ |
|---|---|---|---|
| `EnsureAdminAccess` | `admin.access` | guard `admin` + ต้อง moderator ขึ้นไป ไม่งั้น 403 | ✅ ใช้จริงใน `AdminPanelProvider` |
| `EnsureKingdomSelected` | `kingdom.selected` | ถ้า status approved/active + ไม่มี kingdom_id + level ≥1 + ไม่ใช่ mod → ส่งไป `/choose-kingdom` | ✅ ใช้จริงใน route กระทู้/เมือง |
| `UseAdminSessionCookie` | — | เปลี่ยนชื่อ cookie เป็น `vaelthorn_admin_session` | ✅ ลงทะเบียนเป็นตัวแรกใน `AdminPanelProvider::$middleware` (ก่อน `StartSession`) — มี `AdminSessionCookieTest` คุม regression |

---

## Filament — ข้อควรระวังรายตัว

**Custom pages:** `Pages/Auth/Login.php` (login ด้วย username แทน email),
`Pages/ThreadTrash.php` (ถังขยะกระทู้ soft-deleted, admin+ เท่านั้น, แสดงวันที่เหลือ = 3 − diffInDays)

| Resource | ข้อควรรู้ |
|---|---|
| `CharacterResource` | `canCreate()` = false. logic คุม Approve/Reject อยู่ใน **static method** (`approveCharacter`, `rejectCharacter`, `rejectFormSchema`, `handleRejectSubmit`) ที่ **`UserResource` เรียกใช้ร่วมกัน** — แก้ตรงนี้กระทบทั้ง 2 หน้า |
| `UserResource` | password ใช้ `dehydrated(fn($s)=>filled($s))` + `dehydrateStateUsing(Hash::make)` — **ห้ามให้โหลด hash เดิมเข้า field**. field `role` ถูก disable + ไม่ dehydrate ถ้าไม่ใช่ superadmin. ฝัง section RPG Stats/Onboarding ของ character ผ่าน relationship |
| `EventResource` | **ไม่มี Repeater ของ Rewards/Requirements ในฟอร์มเอง** ฟอร์มมีแค่ title/type/status/kingdom/exp_reward/description/start-end. `exp_reward` มี validation ผูกกับ type. `created_by` auto-set ใน `CreateEvent` |
| `RewardResource` | เป็น **top-level resource แยก** ผูกกับ event ด้วย `Select::relationship('event','title')` |
| `ThreadResource` | รองรับ soft-delete ครบ (TrashedFilter/restore/forceDelete). action เยอะมาก: approve / requestEdit / reject / lock / unlock / archive / unarchive / move |
| `TravelPermitResource` | `CreateTravelPermit::mutateFormDataBeforeCreate` **สร้าง Item ใหม่ทุกครั้งที่ออกใบอนุญาต** (type `permit`, `is_tradeable` = false) แล้วค่อยเรียก `Inventory::firstOrCreate` — ไม่ใช่ item ร่วมกัน |
| `CraftingRecipeResource` | มี `Repeater::make('materials')->relationship('materials')` จัดการ `crafting_recipe_materials` อัตโนมัติ |
| `RewardAuditResource` | อ่านอย่างเดียวเต็มรูปแบบ — `canCreate/canEdit/canDelete` = false ทั้งหมด, form = `[]` **ห้ามเปิดให้แก้ไข** |
| `KingdomResource`, `NoticeBoardResource` | create page auto-set `sort_order` = max + 1 |
| `WorldChronicleResource` | create page auto-set `generated_at` = now() |
| `PostResource` | `canCreate()` = false. ดู/แก้ไขต้อง moderator+, ลบต้อง admin+ |

> **`event_requirements` ไม่มี Filament Resource เลย** — ตารางและ model มีจริง และ `EventController` เอาไปตรวจสิทธิ์ join จริง
> แต่ไม่มีช่องทางสร้าง/แก้ไขจากหน้าแอดมินเลยแม้แต่นิดเดียว ต้องเขียนลง DB ตรงๆ

---

## Frontend

### Blade คือของจริง — React คือส่วนเสริม

หน้าเว็บที่ผู้เล่นใช้จริงทั้งหมดเป็น **Blade** React SPA อยู่ที่ `/app` เท่านั้นและเข้าถึงได้น้อยมาก
**สร้างของใหม่ให้ทำผ่าน Blade ก่อนเสมอ** เว้นแต่จะสั่งเป็นอย่างอื่น

### Quill Editor — จุดที่เปราะบางที่สุดจุดหนึ่ง

โหลดจาก CDN `quill@1.3.7` theme Snow ใน **5 view**:
`thread.blade.php`, `thread-create.blade.php`, `post-edit.blade.php`,
`notice-board-thread.blade.php`, `notice-board-thread-create.blade.php`

> ⚠️ มี partial เตรียมไว้ (`partials/quill-editor-{head,scripts}.blade.php`) แต่**ใช้จริงแค่ 2 ไฟล์ notice-board**
> อีก 3 ไฟล์ copy โค้ดเองทั้งดุ้น → **เกิด drift แล้ว** แต่ละจุดเวอร์ชันไม่เท่ากัน ต้องเช็คทุกจุดถ้าจะแก้

Custom format ที่เขียนเองทั้งหมด (อย่าลบโดยไม่เช็คก่อน):
- **font whitelist เอง**: sarabun, prompt, kanit, noto-serif-thai, mitr, charm, trirong, monospace
- **`dropcap`** — custom inline Blot ทำตัวอักษรใหญ่ขึ้นต้นย่อหน้า + ปุ่ม toolbar เอง
- **`indentright`** — custom block Attributor เลียนแบบ indent ด้านซ้ายแต่กลับด้านขวา
- **size whitelist เป็น px ตรงๆ** (12px–48px) แทน H1-H3
- **link/image handler ใช้ `window.prompt()`** — เพราะ CSS zoom ของเว็บทำให้ tooltip เดิมของ Quill วางตำแหน่งเพี้ยน
- **color picker เขียนเอง** (HSL slider + hex input) ไม่ใช่ module ของ Quill
- **keybinding Tab** — บรรทัดที่ไม่ใช่ list/indent/code-block จะแทรก NBSP แทน indent ของ Quill
  (indent ของ Quill กระทบทั้งย่อหน้าที่ wrap ไม่ตรงกับสไตล์ย่อหน้าไทย)
- **Tab-Tab ใส่เครื่องหมายคำพูด `"`** สำหรับเปิดบทสนทนา RP — **มีเฉพาะใน `thread.blade.php`**
  ยังไม่ถูกก็อปไปอีก 4 ไฟล์

### Design tokens — มี 2 ชุด แยกคนละระบบ

| | ไฟล์ | ตัวแปร |
|---|---|---|
| **Blade** (ใช้เป็นหลัก) | `resources/css/vaelthorn-theme.css` (Tailwind v4 `@theme`) | `--color-gold(-dark)`, `--color-copper`, `--color-bg(-elevated/-subtle)`, `--color-border`, `--color-text(-muted/-subtle)`, `--font-display`, `--font-decorative`, `--font-chronicle` |
| **React SPA** | `resources/frontend/vaelthorn-ui/styles/theme.css` (สไตล์ shadcn/ui) | `--background`, `--gold`, `--card`, `--primary`, `--sidebar-*`, `--city-*` ฯลฯ |

**สร้าง UI ใหม่ทั้งหมดให้ทำผ่าน Blade ก่อนเสมอ อย่าเข้าใจผิดว่า React คืออนาคตอย่างเดียว**

### ฟอนต์

| ที่ไหน | ฟอนต์ |
|---|---|
| Admin panel | Noto Sans Thai (ตั้งใน `AdminPanelProvider` ผ่าน GoogleFontProvider) |
| Frontend หัวข้อ/ตกแต่ง | Cinzel / Cinzel Decorative |
| Frontend เนื้อหา | EB Garamond |
| ตัวอักษรไทย | Noto Serif Thai (fallback) |
| โหลดมาแต่ไม่ผูกกับ `--font-*` | Crimson Text, Trirong |

---

## Testing

PHPUnit 2 suite (`Unit`, `Feature`) — test env เป็น **in-memory SQLite** (`phpunit.xml`) ไม่มี JS test runner

**สถานะล่าสุด: ผ่าน 36/36 tests, 195 assertions (~34 วินาที)**

| ไฟล์ | ทดสอบอะไร |
|---|---|
| `EconomyFlowTest` | ซื้อของ shop (จ่าย/ไม่พอ gold), blacksmith 2 คนช่วยกันคราฟต์ + เฉพาะผู้ร่วมเคลมได้, activate travel permit, กันซื้อ listing ซ้ำ |
| `OnboardingKingdomFlowTest` | flow เต็มตั้งแต่ onboarding จนโพสต์แรกได้ |
| `EventThreadIntegrationTest` | thread ผูก event แจก reward + auto-join, thread ไม่ผูก event ไม่กระทบ, tag แสดงถูก |
| `EventStatsTest` | จำนวนผู้เข้าร่วม + reward rate ในตาราง event |
| `EventCloseActionTest` | ปิด event → ล็อคกระทู้ + แจ้งเตือน, ไม่แตะกระทู้ที่ยังไม่ moderate |
| `FriendshipTest` | ครบวงจร: ส่ง/กันซ้ำ/accept 2 ฝั่ง/reject + cooldown/unfriend/user ไม่มี character ได้ 403 ไม่ใช่ 500/กดซ้ำไม่พัง |
| `NoticeBoardTest` | แอดมินสร้างได้ player สร้าง/ตอบไม่ได้, board ว่างไม่ error |
| `NotificationUrlTest` | URL resolve ถูกแม้ `link_id` เป็น null |
| `MakeSuperAdminCommandTest` | คำสั่ง promote superadmin |

---

## Conventions

> ส่วนนี้คือข้อบังคับที่ต้องเคารพเมื่อแก้โค้ดต่อจากนี้
> ยังไม่ได้เปิดโค้ดตรวจว่าเป็นจริงทุกจุด (โดยเฉพาะ `nullOnDelete` ของ FK
> และขอบเขต transaction ของ crafting/reward) — ถ้าจะแก้ตรงนี้ให้เปิดดูของจริงก่อน

- **สมัครสมาชิก**: สร้าง User + Character + CharacterStat แบบ atomic ใน `AuthController::register`
- **Reward**: เขียน `reward_logs` **ก่อน** อัปเดต inventory/stats เสมอ — กันแจกซ้ำ
- **`exp_to_next`**: เก็บใน DB ไม่คำนวณสด — แอดมิน/ระบบตั้งค่าต่อ level
- **`inventories.item_id`**: FK → `items` เสมอ ห้ามใช้ item_name แบบข้อความ
- **`current_city_id` / `current_kingdom_id`**: FK เป็น `nullOnDelete` ห้ามใช้ unsignedBigInteger เฉยๆ
- **`characters.kingdom_id`**: บ้านเกิด เปลี่ยนที่เดียวคือ `KingdomSelectionController::store`
- **แก้ไข onboarding**: อย่าตั้ง `status` เป็นค่า terminal — reset flag ของด่านนั้น + เก็บเหตุผลลง `stage_N_rejection_reason`
- **Crafting order**: เช็ควัตถุดิบครบและพลิก `crafting_orders.status` ใน **transaction เดียวกัน** กับการลงวัตถุดิบที่ทำให้ครบ
- **Moderation**: approve/reject กระทู้และโพสต์จำกัดที่ admin/moderator ผ่าน inline middleware

---

## 🧭 Known Issues & Debt

> ส่วนนี้คือ "ของที่ยังไม่จริง" ทั้งหมด — เขียนไว้เพื่อไม่ให้ใครเข้าใจว่าทำเสร็จแล้ว

> **ปิดแล้ว (2026-08-03):** session cookie ของ `/admin` เคยไม่แยกจาก frontend (ใช้ `vaelthorn_session`
> ร่วมกัน ทำให้แอดมิน login ไป regenerate session ของผู้เล่นที่ login ค้างอยู่ ได้ 419) — แก้แล้วโดยลงทะเบียน
> `UseAdminSessionCookie` เป็น middleware ตัวแรกใน `AdminPanelProvider` ก่อน `StartSession` ดูหมวด Middleware
> มี regression test คุมไว้ที่ `tests/Feature/AdminSessionCookieTest.php` (ยืนยัน red ก่อนแก้ / green หลังแก้แล้ว)

### 🟠 Dead code ที่ควรลบ (ถ้าไม่มีแผนจะกลับมาใช้)

| ไฟล์ | ปัญหา |
|---|---|
| `app/Services/EnsureUserCharacter.php` | ไม่มีจุดเรียกใช้เลย และ `Character::create([...'city_id'...])` อ้างคอลัมน์ที่**ไม่มีอยู่จริง** — เรียกเมื่อไรพังทันที |
| `app/Models/GameNotification.php` | ชี้ตาราง `notifications` เหมือน `Notification` แต่ `$fillable`/`$casts` อ้างคอลัมน์ `target_id, message, channel, sent_at, is_read` ที่**ไม่มีใน schema จริง** ไม่มีจุดเรียกใช้ ตัวที่ใช้จริงทั้งระบบคือ `Notification` |
| `post_reactions` + `Post::reactions()` + `PostReaction` | ซากของ Witness System ที่ถอดออกแล้ว |

### 🟡 จุดต้องระวัง

- **`event_requirements` ไม่มี UI** — แอดมินผูกเงื่อนไข event จากหน้าเว็บไม่ได้เลย
- **Quill drift** — custom format ถูก copy ทำใน 5 ไฟล์ Tab-Tab มีแค่ไฟล์เดียว ควรรวมเข้า partial ให้หมด
- **`characters.status` เก็บเป็น string ธรรมดา ไม่ใช่ enum** — DB default เป็น `pending` แต่ `EnsureKingdomSelected`
  เช็คทั้ง `approved` และ `active` ควรกลบให้เหลือชุดเดียวเลยถ้าเป็นไปได้
- **React SPA มีของเหลือจาก scaffold** — `.dark{}` OKLCH block (เว็บเป็น dark ทั้งหมดอยู่แล้ว),
  `styles/fonts.css` โหลดฟอนต์ซ้ำคนละ weight, `body { font-family: 'Inter' }` ไม่ผูกกับ `--font-*`
- **`SESSION_COOKIE` ไม่มีใน `.env.example`**

### ระบบที่เคยมีเอกสารอ้างถึงแต่**ไม่มีอยู่จริง**

- **Witness System** (post reactions) — ถอดออก 2026-07-25
- **Onboarding แบบ 2 stage** (training zone + EXP gate) — ถอดออก 2026-07-18 ตาราง `onboarding_slots` ถูกลบแล้ว
- **AI tooling** (สรุปโพสต์/สร้าง chronicle อัตโนมัติ) — ไม่เคยทำ ตาราง `ai_logs` ถูกลบแล้ว และ**ไม่อยู่ในแผน**

---

## Target Scale

ผู้เล่นต่อวันอยู่ที่ ~20 คน โพสต์ ~80 โพสต์/วัน
**การลดภาระแอดมินคือเป้าหมายการออกแบบที่สำคัญที่สุด** — ฟีเจอร์ใหม่ให้เอามุมมองนี้เป็นหลักเสมอ

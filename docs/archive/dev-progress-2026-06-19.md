# Vaelthorn — Dev Progress Log

> อัปเดตล่าสุด: 2026-06-19  
> Stack: Laravel 11 · Filament 3 · MySQL · React/TypeScript · Vite

---

## สิ่งที่ implement แล้ว (ลำดับเวลา)

---

### 1. Notification System

**เป้าหมาย:** แจ้งเตือน in-app ที่ลิงก์ไปยัง entity ได้ทันที ไม่ใช่แค่ข้อความ

#### Migration
| Migration | ผลลัพธ์ |
|-----------|---------|
| `2026_06_19_000001_upgrade_notifications_table` | ทิ้ง schema เดิม สร้างใหม่ทั้งหมด |

**Schema ใหม่ของตาราง `notifications`:**
```
id, user_id (FK→users), type, title, body, data (json),
link_type, link_id, read_at, created_at, updated_at
index: (user_id, read_at), (user_id, created_at)
```

#### Model ใหม่
- `app/Models/Notification.php`
  - `getUrlAttribute()` — resolve `link_type + link_id` เป็น URL จริง
  - `scopeUnread()` — whereNull('read_at')
  - `markAsRead()` — update read_at = now()
  - Backward-compat accessors: `is_read`, `sent_at`, `message` (ให้ view เก่าใช้ได้)

#### Service
- `app/Services/NotificationService.php` — single source สำหรับส่ง notification ทุกประเภท

| Method | Type | เรียกที่ |
|--------|------|---------|
| `notifyPostApproved(Post)` | `post_approved` | ThreadController, PostResource |
| `notifyPostRejected(Post, ?reason)` | `post_rejected` | ยังไม่มี hook (ไม่มี rejection flow) |
| `notifyItemReceived(User, Item, qty)` | `item_received` | ยังไม่มี hook |
| `notifyThreadReply(Thread, User, Post)` | `thread_reply` | ThreadController |
| `notifyThreadLocked(Thread)` | `thread_locked` | ThreadController |
| `notifyLevelUp(Character, old, new)` | `level_up` | LevelingService |
| `notifyEventStarted(Event, User)` | `event_started` | ยังไม่มี hook |
| `notifyEventEndingSoon(Event, User)` | `event_ending_soon` | ยังไม่มี hook |
| `notifyBadgeAwarded(User, name)` | `badge_awarded` | ยังไม่มี hook |
| `notifySystemAnnouncement(User, title, body)` | `system_announcement` | เรียก manual ได้ |

#### Controller / Routes
- `NotificationController` — `index()`, `open()`, `markRead()`, `markAllRead()`
- Route ใหม่: `GET /notifications/{id}/open` → mark as read + redirect

#### UI (Blade)
- `resources/views/notifications.blade.php` — เขียนใหม่ทั้งหมด
  - Filter sidebar: 6 category (post, event, reward, progression, system)
  - แต่ละ card มีไอคอน + สี per type + ปุ่ม "ดู →" ที่ redirect ไป entity
  - Mark all read (นับจาก total ไม่ใช่แค่ page)
  - Pagination + withQueryString

#### Hook ที่ active
- `ThreadController::approvePost()` → post_approved + thread_reply
- `ThreadController::apiApprovePost()` → เดียวกัน
- `PostResource` Filament approve action → post_approved
- `ThreadController::moderate(lock/archive)` → thread_locked

---

### 2. Leveling System

**เป้าหมาย:** ระบบ EXP ที่มี Onboarding Gate พิเศษสำหรับ Level 1→2

#### Migrations
| Migration | ผลลัพธ์ |
|-----------|---------|
| `2026_06_19_000002_add_onboarding_to_character_stats` | เพิ่ม 3 column ใน `character_stats` |
| `2026_06_19_000003_add_is_training_zone_to_villages` | เพิ่ม `is_training_zone` boolean ใน `villages` |

**Columns ใหม่ใน `character_stats`:**
```
onboarding_intro_posts_count  tinyint default 0   -- นับ stage 1 (max 3)
onboarding_awakening_approved boolean default false -- stage 2 ผ่านหรือยัง
onboarding_completed          boolean default false -- gate ครบทั้งหมด
```

#### Config
- `config/leveling.php` — ตาราง exp_to_next ต่อ level (แก้ balance ได้ที่นี่ที่เดียว)

```php
'exp_to_next' => [
    1 => 10,   // Level 1→2 (Onboarding Gate — ไม่ใช่ exp สะสมล้วน)
    2 => 15,
    3 => 20,
    4 => 30,
    5 => 45,
    6 => 65,
    7 => 90,
    8 => 120,
    9 => 160,
]
```

#### Onboarding Gate (Level 1→2 เท่านั้น — รวม 10 exp)

```
Stage 1: โพสต์ใน village ที่ is_training_zone = true
         → ได้ 1 exp ทันที (ไม่ต้อง approve) สูงสุด 3 ครั้ง = 3 exp

Stage 2: approved post แรก หลัง Stage 1 ครบ
         → onboarding_awakening_approved = true, +1 exp

Stage 3: approved post ใน event ประเภท story_arc / crisis / location
         → +event.exp_reward (min 3) จนครบ 6 exp
```

เมื่อ exp ≥ 10 + stage 1 ครบ + stage 2 ผ่าน → level 2 อัตโนมัติ + `notifyLevelUp`

**Level 2+ :** ทุก approved post ใน event → +event.exp_reward ตรงๆ ไม่มีเงื่อนไขเพิ่ม

#### Service
- `app/Services/LevelingService.php`

| Method | เรียกที่ |
|--------|---------|
| `handlePostCreated(Post)` | ThreadController::store(), ::apiStore() |
| `handlePostApproved(Post)` | ThreadController::approvePost(), ::apiApprovePost(), PostResource |
| `addExp(Character, amount, ?Post)` | internal + สร้าง RewardLog ทุกครั้ง |
| `checkOnboardingProgress(Character)` | internal — เช็คหลัง addExp เสมอ |

#### Bug fixes
- `AuthController` แก้ให้ตั้ง `exp_to_next = config('leveling.exp_to_next.1')` (10) ตอน register — ก่อนหน้าใช้ค่า default ของ migration (100)

---

### 3. EXP Cap per Event

**เป้าหมาย:** Admin กำหนด exp ต่อโพสต์ต่อ event ได้ พร้อม validation ตามประเภท

#### Migration
| Migration | ผลลัพธ์ |
|-----------|---------|
| `2026_06_19_000004_add_exp_reward_to_events` | เพิ่ม `exp_reward` tinyint default 1 ใน `events` |

#### Filament
- `EventResource` เขียนใหม่ — เพิ่ม `exp_reward` field พร้อม live validation:

| Event type | EXP ที่กำหนดได้ | Validation Rule |
|-----------|----------------|----------------|
| `flash` | 1 เท่านั้น | `in:1` |
| `story_arc` | 3–15 | `min:3\|max:15` |
| `crisis` | 3–15 | `min:3\|max:15` |
| `location` | 3–15 | `min:3\|max:15` |

---

### 4. Reward Audit

**เป้าหมาย:** Admin เห็น EXP ที่ผูกกับ post ก่อนลบ และสามารถ revoke ได้

#### Migrations
| Migration | ผลลัพธ์ |
|-----------|---------|
| `2026_06_19_000005_add_audit_fields_to_reward_logs` | เพิ่ม 4 column ใน `reward_logs` |
| `2026_06_19_000007_make_reward_logs_event_id_nullable` | แก้ `event_id` เป็น nullable (bug fix — stage 1/2 ไม่มี event) |

**Columns ใหม่ใน `reward_logs`:**
```
post_id      unsignedBigInteger nullable  -- ไม่มี FK cascade (post ลบ log ยังอยู่)
revoked      boolean default false
revoked_at   timestamp nullable
revoked_by   FK→users nullable nullOnDelete
```

> **หมายเหตุ:** `event_id` เดิมเป็น NOT NULL — แก้เป็น nullable แล้ว เพื่อรองรับ exp ที่ได้จาก onboarding stage 1/2 ที่ไม่มี event

#### Filament — PostResource
- Column "EXP" แสดง badge `+N` บน post ที่มี reward_log ผูกอยู่
- ปุ่มลบ post: ถ้ามี EXP ผูกอยู่ → modal พิเศษ 2 ตัวเลือก
  - **"ลบโพสต์เท่านั้น"** → soft delete ปกติ ไม่แตะ exp
  - **"ลบ + Revoke EXP +N"** → soft delete + set revoked = true + หัก exp จาก CharacterStat

#### Filament — RewardAuditResource (ใหม่)
- Route: `/admin/reward-audit`
- Read-only ทั้งหมด — ดูได้เฉพาะ Admin ขึ้นไป
- Filter ตาม character และ revoked status
- แสดง: character, EXP ที่ได้/ถูก revoke, event, post link, revoked by ใคร

---

### 5. Archive System

**เป้าหมาย:** เนื้อหา RP ไม่ถูกลบเมื่อ Story Arc จบ — เก็บเป็น archived อ่านได้เสมอ

#### Migration
| Migration | ผลลัพธ์ |
|-----------|---------|
| `2026_06_19_000006_add_archived_at_to_threads` | เพิ่ม `archived_at` timestamp nullable ใน `threads` |

#### พฤติกรรมที่เปลี่ยน
- **Village feed (Blade + API):** ซ่อน `archived` threads จาก main feed แล้ว — แสดงเฉพาะ `approved` และ `locked`
- **Thread view:** archived thread ยังเข้าได้ตรงผ่าน `/threads/{id}` อ่านปกติ (comment ปิดตาม status logic เดิม)

#### หน้า Archive ใหม่
- Route: `GET /archive` → `archive.index`
- Controller: `app/Http/Controllers/ArchiveController.php`
- View: `resources/views/archive.blade.php`
  - Filter ตาม city / village
  - แสดง archived threads เรียงตาม `archived_at` ใหม่สุดก่อน
  - Badge "Read Only" บน card
  - Pagination

#### Filament — ThreadResource
- Action "Archive Story Arc" → set `status = archived` + `archived_at = now()`
- Modal อธิบายว่า: "ไม่มีการลบข้อมูล ยังอ่านได้จาก Chronicle Archive"

---

## สิ่งที่ต้องตั้งค่าก่อน ระบบถึงจะทำงาน

| งาน | วิธี | หมายเหตุ |
|-----|------|---------|
| ตั้ง Training Zone | Filament → Village → `is_training_zone = true` | ต้องมีอย่างน้อย 1 village |
| ตั้ง EXP ต่อ event | ตอนสร้าง Event → ฟิลด์ "EXP ต่อโพสต์" | flash=1, อื่นๆ=3–15 |
| ผูก event กับ thread | Thread ต้องมี `event_id` ก่อนโพสต์จะให้ exp stage 3+ | |

---

## สิ่งที่ยังไม่ได้ implement (รอรอบถัดไป)

| Feature | หมายเหตุ |
|---------|---------|
| **Village access gate** | Level 1 ยังเข้า village อื่นได้ — ต้องเพิ่ม check `stats->level < 2 && !village->is_training_zone` ใน VillageController |
| **Notification hooks ที่ค้างอยู่** | `notifyItemReceived`, `notifyEventStarted`, `notifyEventEndingSoon`, `notifyBadgeAwarded` — methods พร้อมแล้วแต่ไม่มีจุดเรียก |
| **Reward distribution (item/gold)** | `RewardLog` บันทึก EXP ได้แล้ว แต่ยังไม่มี logic แจก item/gold ลง inventory |
| **Scheduled commands** | `notifyEventEndingSoon` ต้องการ artisan command + scheduler |
| **Real-time notifications** | ปัจจุบัน polling/refresh เท่านั้น — ถ้าต้องการ push ต้องเพิ่ม WebSocket ทีหลัง |
| **Character role change on level-up** | spec บอกว่า level 2 ควรเปลี่ยน character role จาก onboarding → player ปกติ |

---

## ไฟล์หลักที่แก้ไปทั้งหมด

```
app/
  Http/Controllers/
    AuthController.php          ← ตั้ง exp_to_next ถูกต้องตอน register
    ThreadController.php        ← hook LevelingService + NotificationService
    NotificationController.php  ← ใช้ Notification model ใหม่ + category filter
    VillageController.php       ← ซ่อน archived จาก main feed
    ArchiveController.php       ← ใหม่: หน้า Chronicle Archive
  Models/
    Notification.php            ← ใหม่: model หลัก notification
    CharacterStat.php           ← เพิ่ม fillable onboarding fields
    RewardLog.php               ← เพิ่ม post_id, revoked, revokedBy()
    Thread.php                  ← เพิ่ม archived_at fillable
    Event.php                   ← เพิ่ม exp_reward fillable
  Services/
    NotificationService.php     ← ใหม่: 10 notification methods
    LevelingService.php         ← ใหม่: onboarding gate + level up logic
  Filament/Resources/
    EventResource.php           ← เขียนใหม่ + exp_reward validation
    PostResource.php            ← EXP badge + delete modal + hooks
    RewardAuditResource.php     ← ใหม่: read-only audit log
    ThreadResource.php          ← Archive Story Arc action + archived_at
  Providers/
    AppServiceProvider.php      ← ใช้ Notification model ใหม่

config/
  leveling.php                  ← ใหม่: exp_to_next table

database/migrations/
  2026_06_19_000001_upgrade_notifications_table.php
  2026_06_19_000002_add_onboarding_to_character_stats.php
  2026_06_19_000003_add_is_training_zone_to_villages.php
  2026_06_19_000004_add_exp_reward_to_events.php
  2026_06_19_000005_add_audit_fields_to_reward_logs.php
  2026_06_19_000006_add_archived_at_to_threads.php
  2026_06_19_000007_make_reward_logs_event_id_nullable.php  ← bug fix

resources/views/
  notifications.blade.php       ← เขียนใหม่ทั้งหมด
  archive.blade.php             ← ใหม่: Chronicle Archive page

routes/web.php                  ← เพิ่ม /archive, /notifications/{id}/open
```

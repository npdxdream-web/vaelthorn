# VAELTHORN — Work Card
> Last updated: 2026-06-17 | Stack: Laravel 11 · Filament 3.3 · React 18 · MySQL · Vite 8

---

## Project Overview

Vaelthorn เป็น RPG Community Platform สำหรับนักเขียน RP (Role-Play) ออนไลน์
ผู้เล่นสร้างตัวละคร เขียน Post ใน Thread ของแต่ละ Village รับ Reward และพัฒนาตัวละคร
Admin บริหารผ่าน Filament panel (/admin) — อนุมัติ Post, สร้าง Event, แจก Reward

**Target scale:** ~20 players/day · ~80 posts/day
**Local dev:** vaelthorn.test (Laragon) · PHP 8.3 · Node 22

---

## Core Game Loop

```
Admin สร้าง Event ใน City
  → Player เขียน Post ใน Thread (Village)
  → Admin อนุมัติ Post
  → ระบบแจก Reward → Inventory + Stats อัปเดต
  → Character เติบโต (Rank, Level, Badge)
```

---

## สถานะงาน

### ✅ เสร็จแล้ว (Completed)

#### Infrastructure
- [x] Laravel 11 + Filament 3.3 + React 18 SPA setup
- [x] Auth system — register/login/logout (สร้าง User + Character + CharacterStat atomic)
- [x] Session separation (admin vs frontend cookie)
- [x] Vite 8 + Tailwind CSS 4 + vaelthorn-theme.css custom design system
- [x] 3-column layout shell (260px | 1fr | 260px)
- [x] Global zoom 0.9, font scaling, sidebar sizing

#### Database & Seeders
- [x] Migration ครบทุก table ตาม schema
- [x] CitySeeder — 6 kingdoms (Silvaria, Aurantia, Kalif, Frostwell, Kyoren, Celestia)
- [x] VillageSeeder — 12 villages กระจายทุก kingdom
- [x] ItemSeeder — 18 items (weapon/armor/consumable/material/key_item)

#### Frontend Pages
- [x] Home — แสดง Cities + Villages grid
- [x] Village — thread list ใน village
- [x] Thread — post viewer + post form (Quill editor) + collapsible rail
- [x] Character Show — profile + avatar frame + stats + badges + posts
- [x] Character Edit — แก้ name/title/backstory/avatar (player self-service)
- [x] Inventory — items จัดกลุ่มตาม type, rarity color system
- [x] Notifications — live จาก DB, filter by type, mark read
- [x] World Chronicles — list + show, kingdom color map
- [x] Reward History — all-time totals + per-event log
- [x] Market — index (buy/cancel) + create (sell) พร้อม DB transaction safety

#### Filament Admin Resources
- [x] UserResource (SuperAdmin only)
- [x] CharacterResource (approve/reject)
- [x] EventResource (CRUD + Rewards Repeater + Requirements Repeater)
- [x] ItemResource, RewardResource
- [x] CityResource, VillageResource, ThreadResource, PostResource
- [x] BadgeResource (condition_type: posts/events/manual/first_post/first_event)
- [x] WorldChronicleResource (AI-generated, is_published toggle)

#### UI Components
- [x] Avatar Frame — SVG Art Deco, 3 styles (fan/step/simple), role-based color
- [x] Collapsible charModule (scroll → mini, click mini → expand, pinned state)
- [x] Thread rail — Quick Actions, Notices (live DB), My Activity, World Navigation, In This Thread
- [x] Navbar icons — Home, Map, Chronicle, Inventory, Market, Rewards, Notifications, Avatar
- [x] Post author column — 240×450 portrait frame
- [x] Frame stroke thinned ทุก role

---

### 🔧 ต้องทำต่อ (Pending — Priority Order)

#### Priority 1 — Core Gameplay Loop (ยังทำงานไม่ครบ)

**1.1 Event Participation**
- [ ] Player กด "Join Event" → เขียน `event_participants` record
- [ ] ตรวจ `event_requirements` ก่อน join (stat/level gate)
- [ ] แสดง Event ที่ joined ใน character profile
- [ ] Flash event: auto-close เมื่อ `end_at` ผ่าน (queue job)

**1.2 Reward Distribution**
- [ ] Admin กด "Distribute Rewards" บน EventResource
- [ ] ระบบเขียน `reward_logs` → อัปเดต `inventories` + `character_stats.gold`
- [ ] กัน double-reward (ตรวจ reward_logs ก่อน)
- [ ] แจ้ง Notification ให้ Player เมื่อได้รับ Reward

**1.3 EXP & Level Up**
- [ ] เมื่อ Post ถูก Approve → ให้ EXP ตาม reward
- [ ] ตรวจ `exp >= exp_to_next` → Level up, reset exp, อัปเดต `exp_to_next`
- [ ] แสดง Level Up notification ใน sidebar

**1.4 Badge Auto-Award**
- [ ] Observer หรือ Job ตรวจ condition ทุกครั้งที่ Post ถูก approve
- [ ] condition_type: `posts` (นับ approved posts), `events` (นับ events joined)
- [ ] เขียน `character_badges` record + Notification

#### Priority 2 — AI Features

**2.1 Post Summarizer**
- [ ] Admin กด "Summarize" บน PostResource → เรียก Claude API
- [ ] เขียนผล → `posts.ai_summary`
- [ ] บันทึก cost → `ai_logs`

**2.2 World Chronicle Generator**
- [ ] Admin กด "Generate Chronicle" บน EventResource (หลัง event closed)
- [ ] Prompt: ส่ง approved post summaries ของ event ให้ Claude สร้าง Chronicle
- [ ] บันทึก → `world_chronicles.content`, `is_published = false`
- [ ] Admin review แล้ว publish

**2.3 Writing Assistant**
- [ ] ปุ่ม "Assist" ใน post form → เรียก Claude API ด้วย draft content
- [ ] Return suggestions inline (ไม่แทนที่ content ของ player)

#### Priority 3 — Social & Engagement

**3.1 Witness System (Post Reactions)**
- [ ] ปุ่ม react ใต้ post (Witness/Moved/Inspired/etc.)
- [ ] เขียน `post_reactions` — unique per (post_id, character_id)
- [ ] แสดง reaction count ใต้ post
- [ ] Notification ให้เจ้าของ post เมื่อมีคนกด

**3.2 Notification System — Full Integration**
- [ ] ระบบส่ง Notification อัตโนมัติจาก events:
  - Post approved → แจ้งผู้โพสต์
  - Reward received → แจ้งผู้เล่น
  - Event created → broadcast ให้ player ใน city นั้น
  - Badge earned → แจ้งผู้เล่น
- [ ] Discord Webhook — ส่งเมื่อ event เปิด/ปิด, chronicle publish

**3.3 Market**
- [ ] ทดสอบ buy/sell/cancel flow ครบ (edge cases: quantity 0, ราคาเกิน gold)
- [ ] Market listing expiry (อาจ auto-cancel หลัง 7 วัน)
- [ ] แสดงประวัติ transaction ของ character

#### Priority 4 — Polish & Performance

- [ ] Mobile responsive (ตอนนี้ design เน้น 1920px)
- [ ] Thread pagination (ตอนนี้ load all posts)
- [ ] Image upload จริง (ตอนนี้ใส่ URL เท่านั้น)
- [ ] World Map visual (แสดง 6 kingdoms เป็น map จริง)
- [ ] Online/Active tracking (session-based last_seen)
- [ ] Search (threads, characters, items)
- [ ] Character stat allocation UI (ใช้ stat points เมื่อ level up)

---

## Known Issues & Technical Debt

| Issue | สาเหตุ | Status |
|---|---|---|
| `cities` ไม่มี column `kingdom` | Migration ไม่ได้สร้าง | ✅ แก้แล้ว — ใช้ `city->name` แทนทุก view |
| `characters` ไม่มี column `role` | ไม่ได้ migrate | ✅ แก้แล้ว — ใช้ `auto_rank` แทน |
| Messages ไม่มีระบบจริง | ยังไม่ได้สร้าง | ⚠️ redirect → /notifications ชั่วคราว |
| Market ยังไม่ทดสอบ stress | เพิ่งสร้าง | 🔧 ต้องทดสอบ |
| Post content เป็น HTML (Quill) | `strip_tags()` ใน sidebar | ⚠️ ควร sanitize ก่อน save |
| avatar ใช้ URL เท่านั้น | ไม่มี file upload | 🔧 ต้องทำ Priority 4 |

---

## Data Model Snapshot

```
User (1) ── (1) Character ── (1) CharacterStat
                 │
                 ├── (many) Inventory ── Item
                 ├── (many) CharacterBadge ── Badge
                 ├── (many via event_participants) Event
                 └── (many) RewardLog ── Event + Item

City ── Village ── Thread ── Post ── PostReaction
City ── Event ── Reward
                └── EventRequirement
                └── EventParticipant ── Character

WorldChronicle ── Event
GameNotification (target_id → Character)
MarketListing (seller_id → Character, item_id → Item)
AILog (cost tracking per API call)
```

---

## Auto-Rank System (computed, not stored)

| Posts Approved | Rank |
|---|---|
| 0–4 | Stranger |
| 5–19 | Wanderer |
| 20–49 | Traveler |
| 50–99 | Veteran |
| 100+ | Legend |

---

## File Structure (Key Files)

```
app/
  Http/Controllers/
    AuthController.php          — register/login (atomic User+Character+Stat)
    ThreadController.php        — show thread + rail data (notices, myPosts)
    CharacterController.php     — show, edit, update
    MarketController.php        — index, create, store, cancel, buy
    NotificationController.php  — index, markRead, markAllRead
    RewardHistoryController.php — index
    WorldChronicleController.php — index, show
  Models/
    Character.php               — auto_rank appended, relationships
    MarketListing.php           — scopeActive()
    GameNotification.php        — $table='notifications', scopeForCharacter()
  Filament/Resources/           — Admin panel resources

resources/
  views/
    partials/
      character-module.blade.php  — collapsible right sidebar charModule
      navbar.blade.php            — top navigation with icons
    components/
      public/shell.blade.php      — 3-column layout wrapper
      avatar-frame.blade.php      — SVG Art Deco frame component
      frames/
        _fan-corner.blade.php     — guardian/legend style
        _step-corner.blade.php    — veteran/knight style
        _simple-corner.blade.php  — stranger/wanderer style
    thread.blade.php              — main thread page + rail panels
    character.blade.php           — character profile page
    inventory.blade.php
    notifications.blade.php
    market/index.blade.php
    market/create.blade.php
    rewards.blade.php
    chronicles/index.blade.php
    chronicles/show.blade.php

  css/vaelthorn-theme.css         — custom design system (dark fantasy theme)
```

---

## Design System Notes

- **Font:** Cinzel (display/headings), EB Garamond (body/chronicle), Cinzel Decorative (initials)
- **Color:** gold `#c8a84b`, bg dark `#0b0a08`, text muted `#c4b898`
- **Body zoom:** 0.9 (designed at 1920px)
- **Avatar frames:** SVG overlay, stroke thinned to 0.9/0.45/0.25 (from 1.8/0.9/0.5)
- **Post portrait:** 240×450px in thread author column
- **Sidebar charModule:** scroll > 80px → mini (44px avatar + name + rank), click mini → expand (pinned until scroll to top)

---

## คำถามสำหรับ AI วิเคราะห์

1. งานใน Priority 1 ควรเริ่มจากอะไรก่อนเพื่อให้ Core Loop ทำงานได้ end-to-end เร็วที่สุด?
2. Badge Auto-Award ควรใช้ Laravel Observer หรือ Queue Job — trade-off คืออะไร?
3. AI Features (Claude API) ควรออกแบบ rate limit และ cost control อย่างไรในระดับ ~80 posts/day?
4. Witness System กับ Notification จะ integrate กันอย่างไรโดยไม่ให้ flood notifications?
5. Post content เป็น Quill HTML — ควร sanitize ที่ Controller (save) หรือที่ View (display) หรือทั้งคู่?

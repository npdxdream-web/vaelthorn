# Vaelthorn

RPG community platform for online roleplay writers. Players create a character, write
posts in threads within a City (grouped under a Kingdom), earn EXP/Gold/items through
admin-run Events, and grow their character over time. Admins run everything through a
Filament admin panel — approving posts, creating Events, granting rewards.

**Live**: [vaelthorn.world](https://vaelthorn.world)
**Target scale**: ~20 active players/day, ~80 posts/day

## Stack

- **Backend**: Laravel 13, PHP 8.3+, MySQL
- **Admin panel**: Filament 3.3 at `/admin`
- **Frontend**: React 18 + TypeScript SPA at `/app` (`resources/frontend/vaelthorn-ui/`)
- **Build**: Vite 8 + Tailwind CSS 4

## Setup

```bash
composer setup   # installs deps, creates .env, generates key, migrates, builds assets
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
```

## Running locally

```bash
composer dev     # server + queue worker + log tailer (pail) + vite, all at once
```

Frontend-only dev server: `npm run dev` — see [`docs/frontend-setup.md`](docs/frontend-setup.md) for a
non-technical walkthrough.

## Testing

```bash
composer test                              # full suite
php artisan test tests/Feature/SomeTest.php  # single file
./vendor/bin/pint                          # fix PHP code style
```

## Documentation

- **[`CLAUDE.md`](CLAUDE.md)** — architecture, database schema, domain model, conventions. Start here.
- **[`docs/INDEX.md`](docs/INDEX.md)** — map of every other doc in [`docs/`](docs/) (changelog, roadmap, feature plans, QA audits) and which one answers what question.

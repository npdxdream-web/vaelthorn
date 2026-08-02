# Design Reference (not used by the app)

Everything in this folder is a Figma/AI design-tool export or a design mockup dump —
**no code in `app/`, `resources/frontend/vaelthorn-ui/`, or anywhere else in the app
references any of it.** Kept only in case the visual references are still useful;
safe to delete entirely without breaking anything.

- `figma-export/`, `onboarding-design/` — raw shadcn/ui component scaffolding from a
  Figma Make export. `figma-export/` alone has 3 near-duplicate copies nested inside it
  (top level, `design/`, `thread-page-reference/`).
- `exports/` — **not tracked by git** (see root `.gitignore`) — mockup zips and a
  screenshot dumped at the repo root before this cleanup. Deleting these is
  permanent (no git history to recover from), so they were moved here rather than
  deleted outright.

The real, actively-used frontend lives at `resources/frontend/vaelthorn-ui/` — see
[`/docs/frontend-setup.md`](../docs/frontend-setup.md).

# FOCMS Dashboard

Front-end dashboards for the **Future Officer Candidate Management System** — a longitudinal portfolio and life-tracking platform built around the 5-pillar taxonomy (Personal, Academics, Extra Curricular, Career, Higher Education).

This plugin is the **read layer** for FOCMS. The write side (Custom Post Types, ACF field groups, Notion migration) lives in separate mu-plugins. This plugin's job is to surface the data through two themed, authenticated dashboards.

## What it does

| Route | View | Audience |
|---|---|---|
| `/dashboard` | Family Dashboard | Parents / guardians / system operator |
| `/john` | Student Portal | The student (currently configured for John, becomes `/student` in commercial multi-tenant) |

Both routes:
- Require WordPress login (anonymous → WP login → return after login)
- Use mobile-first responsive CSS
- Apply `noindex,nofollow,noarchive` meta to prevent search engine crawling
- Read live data from FOCMS Custom Post Types via a central `FOCMS_Data_Provider` class

## Family Dashboard tabs

- **Pulse** (live in v0.2.0) — Quick Stats, Upcoming Deadlines, Training Pulse, Active Goals, Recent Activity
- **Risk** (placeholder, Phase 3.3) — Stale records, overdue items, deadline gap analysis
- **Metrics** (placeholder, Phase 3.4) — GPA/MAP/ACT trajectory card grid
- **Pathway** (placeholder, Phase 3.6) — Admissions probability across 4 pathways (Service Academy, ROTC, Merit, D1 Athletic)

## Architecture

- **Theme-independent** — uses high-specificity `.focms-*` CSS classes that won't collide with theme styles
- **Tenant-configurable** — all tenant values in `define()` constants at top of main plugin file (`FOCMS_TENANT_ID`, `FOCMS_FAMILY_SLUG`, `FOCMS_GRAD_DATE`, etc.)
- **Permalink-aware** — uses WordPress rewrite rules API; requires Post Name (or similar) permalink structure
- **Defensive read layer** — `FOCMS_Data_Provider` tolerates missing CPTs, missing ACF fields, missing data; widgets gracefully degrade

## Installation

### Production (via Git Updater)

This repo is set up for the [Git Updater](https://git-updater.com/) WordPress plugin. Once Git Updater is installed and configured:

1. Add this repo as a managed plugin via Git Updater's settings
2. WordPress will surface updates whenever a new release is tagged here
3. Click "Update" in WP admin — same flow as any other plugin update

### Manual install (fallback)

1. Download the latest release ZIP from [Releases](https://github.com/srjordan6/focms-dashboard/releases)
2. WordPress admin → **Plugins → Add New → Upload Plugin**
3. Upload the ZIP, install, activate
4. Visit `/dashboard` (must be logged in)

## Dependencies

- WordPress 6.0+
- PHP 7.4+
- Advanced Custom Fields (ACF) Pro recommended — plugin falls back to `get_post_meta` if absent
- FOCMS Custom Post Types registered (separate mu-plugin: `jrj-family-cpts.php`)

## Release process

Releases follow semantic versioning:
- **Patch** (0.2.x) — bug fixes, no schema changes
- **Minor** (0.x.0) — new widgets, new tabs, new data provider methods
- **Major** (x.0.0) — breaking changes to data provider API, template structure changes

To cut a release:
1. Update version in `focms-dashboard.php` header AND `FOCMS_VERSION` constant
2. Update `CHANGELOG.md`
3. Commit: `chore: release v0.x.0`
4. Tag: `git tag v0.x.0 && git push --tags`
5. Create GitHub Release with the new tag, attach the plugin ZIP as a release asset

## Commercial intent

This plugin is part of the FOCMS commercial product roadmap. The single-tenant version (this code) becomes the white-label embed mode for tenants who want their own URL. The multi-tenant SaaS version will replace the WordPress dependency with a Postgres + React stack — see the FOCMS strategic docs in Notion for the full commercial architecture.

## License

Proprietary. All rights reserved. Not licensed for redistribution. See [LICENSE](./LICENSE).

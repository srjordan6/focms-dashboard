# Changelog

All notable changes to FOCMS Dashboard are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.1] - 2026-06-03

### Added
- Git Updater plugin headers (`GitHub Plugin URI`, `Primary Branch`, `Release Asset`) — enables WordPress to detect updates from GitHub releases automatically
- `Plugin URI` now points to the GitHub repo instead of the marketing site
- `Author URI` header added
- This CHANGELOG file
- README.md with install instructions and release process
- Proprietary LICENSE file

### Changed
- License header changed from `Private` to `Proprietary` for clarity

### Migration notes
- No functional code changes between v0.2.0 and v0.2.1
- This release exists to establish the GitHub-managed release pipeline
- Existing installations updating from 0.2.0 → 0.2.1 will see no behavioral difference

## [0.2.0] - 2026-06-02

### Added
- **Family Dashboard Pulse tab** with 5 live widgets:
  - Quick Stats — GPA, latest MAP RIT (Math + Reading), latest swim PR, days to graduation
  - Upcoming Deadlines — next 90 days from `jrj_app_nom`, `jrj_std_test`, `jrj_competition`, `jrj_swim_meet`, `jrj_summer`, color-coded by urgency
  - Training Pulse — last 30 days summary with SVG sparkline (no JS lib dependency)
  - Active Goals — up to 6 active goals with progress bars
  - Recent Activity — last 10 records across all CPTs
- Tab-template architecture (`templates/tabs/*.php`) enabling per-tab files
- Placeholder tabs for Risk, Metrics, Pathway
- 6 new `FOCMS_Data_Provider` methods:
  - `records_by_date_range()` — defensive multi-CPT, multi-field-name date queries
  - `get_upcoming_deadlines()` — aggregates deadlines across 5 CPTs
  - `get_recent_training()` — training records in window
  - `get_training_volume_summary()` — aggregated stats + sparkline data
  - `get_active_goals()` — filtered by status field with defensive defaults
  - `get_latest_growth_test()` — most recent MAP record by subject
  - `days_until()` — countdown helper
  - `urgency_class()` — threshold-based classification
- `FOCMS_GRAD_DATE` and `FOCMS_GRAD_DATE_LABEL` config constants
- ~330 lines of new widget CSS (mobile-first, responsive)

### Changed
- `dashboard-family.php` reduced from 139-line monolithic template to 65-line wrapper that delegates to tab files

## [0.1.0] - 2026-06-02

### Added
- Initial plugin scaffold
- URL routing for `/dashboard` and `/john` via WordPress rewrite rules API
- `FOCMS_Router` class — claims routes, adds `noindex` meta, swaps templates
- `FOCMS_Auth` class — strict authentication, redirects anonymous users to WP login
- `FOCMS_Data_Provider` class — central read API for CPT queries (initial 8 methods)
- Placeholder family dashboard and student portal templates
- Mobile-first responsive base CSS
- Activation/deactivation hooks for clean rewrite rule lifecycle
- 13 tenant-configurable constants at top of main plugin file (slugs, titles, prefixes, paths)

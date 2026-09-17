# AMRAJ — Local Business Directory

## ⚠️ Before you deploy — do this first

1. **Rotate your ImageKit key.** The old private key was hardcoded in source
   control and must be treated as leaked. Generate a new key from your
   ImageKit dashboard.
2. Copy `.env.example` to `.env` and fill in real values (DB credentials +
   the new ImageKit keys). `.env` is git-ignored — never commit it.
3. Change the default admin password immediately after your first login
   (`admin@amraj.com` / `Admin@Amraj2026`).
4. Import `database/amraj.sql` into a fresh `amraj` database.

## What was fixed in this pass

**Security**
- Removed the hardcoded ImageKit private key and DB credentials from
  `backend/config.php` — both now load from `.env` (see `backend/env.php`).
- Added `.gitignore` (`.env`, logs, uploads) so secrets and runtime files
  never get committed again.
- Fixed a double-escaping bug in `backend/shop_handler.php` and
  `frontend/owner/edit-shop.php`: values were run through
  `mysqli_real_escape_string()` *and* bound via prepared statements, which
  corrupted any text with an apostrophe (saved literal backslashes into the
  database). Removed the redundant escaping since prepared statements
  already handle this safely.
- Fixed the default admin account: the password hash in `amraj.sql` did not
  actually match the documented password, so the seeded admin account could
  not log in on a fresh install. Regenerated the hash.
- Fixed a missing `require` in `sitemap.php` that caused a fatal error.

**Design consistency**
- Added `frontend/assets/css/style.css` — one shared design-system file
  (typography, spacing, card/shadow tokens, focus states) used across every
  page instead of copy-pasted inline `<style>` blocks with slightly
  different values per page.
- Rewrote the generic, AI-sounding copy across the site (nav, footer, hero,
  search page, categories, about, contact, terms, register, admin) into
  plain, professional wording.

**Real data, no placeholders**
- Removed the hardcoded fallback numbers (142 businesses / 24 categories)
  on the homepage. Stats are now pulled live from the database.
- Added a real visitor counter (`site_visits` table, one row per
  visitor/day, hashed IP — no raw IPs stored) shown alongside registered
  user and business counts on the homepage.

**PWA**
- `manifest.json`, `service-worker.js` (caches static assets, always goes
  to the network for login/admin/owner/user areas), `offline.html` fallback.
- New brand icon (`frontend/assets/logo-icon.svg`) rasterized to all
  required sizes under `frontend/assets/icons/`.

**SEO**
- `robots.txt` and a dynamic `sitemap.php` that lists all approved
  business listings plus static pages.

## Testing performed

Ran the app end-to-end on a local PHP 8.3 + MariaDB server:
- `php -l` on all 34 PHP files — no syntax errors.
- Every public page returns 200; every protected dashboard correctly
  redirects to login when unauthenticated.
- Full registration → login → shop submission → data-integrity flow
  tested (including apostrophes in text fields, to catch the escaping bug
  above).
- Admin login verified against the corrected password hash.

## Local setup

```bash
cp .env.example .env        # fill in DB + ImageKit values
mysql -u root -p amraj < database/amraj.sql
php -S localhost:8000 -t .  # or point Apache/Nginx at this folder
```

## Design pass — warm/editorial redesign (this build)

**What changed**
- Replaced the blue/indigo/orange gradient + glassmorphism look (the thing that
  read as "obviously AI-generated") with a warm terracotta/olive palette on
  a stone-gray neutral base, across every page: public site, admin, owner and
  user dashboards.
- Headings site-wide now render in Fraunces (serif) via one shared rule in
  `assets/css/style.css` — body text stays Inter. This is the single biggest
  lever for the "hand-designed" feel and required no per-page edits.
- Removed the blurred glow-blob decorative circles (index, login, register)
  and the gradient/glass "premium card" hover effects; replaced with flat
  warm surfaces and a simple lift + soft shadow on hover.
- Gradient brand text (the blue→orange "AMRAJ" wordmark) is now solid
  terracotta — no `bg-clip-text` gradients left anywhere.
- New mark-only logo (terracotta location-pin on ink, olive center dot),
  regenerated at every required favicon / home-screen icon size, plus a new
  matching social-share (OG) image.
- `manifest.json` and the `theme-color` meta tag now match the new ink brand
  color, and the service worker cache key was bumped so installed/PWA users
  actually pick up the new icons and styles instead of serving the old
  cached ones.

**What did not change**
- No backend/PHP logic, queries, or handlers were touched — this pass is
  purely visual (Tailwind utility classes, the shared CSS file, inline
  `<style>` blocks, and static image assets).
- Every `.php` file was run through `php -l` after editing — no syntax
  errors.

**Not covered in this pass** (couldn't verify without a live DB in this
environment — test these before/after deploying):
- End-to-end functional testing against a real MySQL/MariaDB instance.
- Visual QA in an actual browser (this was a code-level, not pixel, review).

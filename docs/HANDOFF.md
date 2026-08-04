# Handoff — Keybolts static pages

Written 2026-08-02. Read this first if you are picking the work up from
another agent or another session.

## What this project is

Keybolts (Công ty TNHH XNK Khóa Cửa Việt Long) — a Vietnamese door-lock
distributor. **Headless:** Drupal 11 is API-only, and a Nuxt 4 SSR app renders
every public page. Both are served from one origin, so
`https://vietlong.ddev.site/` shows the real site and `/api`, `/admin` stay with
Drupal.

## Read these, in this order

1. `docs/superpowers/specs/2026-08-02-keybolts-static-pages-design.md` — what we
   agreed to build and **why** each decision was made.
2. `docs/superpowers/plans/2026-08-02-keybolts-static-pages.md` — 15 tasks with
   the complete code for each. This is the work list.
3. `docs/drupal-environment-setup.md` — environment traps that will cost you an
   hour each if you skip it.
4. This file's *Progress* section below.

## Progress

The authoritative live ledger is `.superpowers/sdd/2026-08-02-keybolts-static-pages/progress.md`
— but **that directory is git-ignored**, so a fresh clone will not have it.
The snapshot below is the tracked copy. Reconcile it with `git log` on the
`feat/static-pages` branch, which is the real record.

| Task | State |
|---|---|
| 1. `branch` content type + seed 5 showrooms | complete (`6559b45..9d6aba7`) |
| 2. `GET /api/v1/branches` | complete (`9d6aba7..91502bd`) |
| 3. Homepage reads branches from API | complete (`91502bd..2f27d49`) |
| 4. `contact_submission` entity | implemented (`14b8050`); one review finding open — see below |
| 5–15 | not started |

**Task 4's open finding.** The entity's `admin_permission` is
`administer nodes`, which exposes customer PII (name, phone, message) to anyone
who can manage the product catalogue. It needs a dedicated permission in a new
`keybolts_core.permissions.yml` with `restrict access: true`. A fix was
dispatched but may not have landed — check `git log` for a permissions file
before redoing it.

**A reviewer finding that was wrong, so you don't redo it.** A review claimed
the `created` base field will not auto-populate. That is false: Drupal's
`CreatedItem` applies `REQUEST_TIME` through `applyDefaultValue()`. Verified by
creating an entity without setting `created` and reading back `1785680191`. Do
**not** add a `setDefaultValueCallback` or a `preSave()` override.

**Deferred minors** (decide before merging, neither blocks progress):
- `field_map_url` exists on `branch` but is never seeded; the design derives the
  Maps link from the address client-side. Task 9's `BranchGrid` only renders the
  "Chỉ đường" link when `mapUrl` is set, so it will not appear until either the
  seed fills it or the component derives it from the address.
- `BranchSerializer::toArray()` is public where `ProductSerializer` keeps
  analogous helpers private. Style only.

## Environment — the things that will bite you

**Run the site**

```bash
ddev start
cd frontend && npm run dev      # required, or / returns 502
```

Then open `https://vietlong.ddev.site/`.

**The dev server is on port 3100, bound to 0.0.0.0 — not Nuxt's default.**
Nuxt binds loopback only, which the ddev web container cannot reach, and port
3000 on the original machine was already published by an unrelated Docker
project. With both problems in play the nginx proxy silently served *that other
project's site* while still returning HTTP 200. If you change the port, change
the `upstream nuxt` block in `.ddev/nginx_full/nginx-site.conf` to match.

**Never let the string `#ddev-generated` appear in `.ddev/nginx_full/nginx-site.conf`,
not even inside a comment.** ddev greps for that literal anywhere in the file and
regenerates it, silently discarding the whole proxy setup. A comment saying "do
not restore the #ddev-generated marker" is enough to trigger it.

**nginx matches regex locations before plain prefixes.** Drupal's deny-dotfiles
rule and any `.js`/`.css` rule would otherwise claim Nuxt's assets and Vite's
`/node_modules/.vite/` dev URLs, so every Nuxt route in the config uses `^~`.

**Node cannot verify ddev's TLS certificate.** SSR fetches fail with
`UNABLE_TO_VERIFY_LEAF_SIGNATURE`. The `dev` and `generate` npm scripts already
point `NODE_EXTRA_CA_CERTS` at mkcert's CA. Do not "fix" this with
`NODE_TLS_REJECT_UNAUTHORIZED=0`.

**Tailwind 4 silently drops arbitrary values containing nested parentheses.**
`grid-cols-[repeat(auto-fill,minmax(230px,1fr))]` and
`shadow-[inset_0_0_0_1px_rgba(0,0,0,.16)]` generate no CSS at all — no warning.
`clamp(...)` and `rgba(...)` on their own are fine. Named `kb-*` classes in
`@layer components` in `frontend/app/assets/css/tokens.css` are the workaround.

**Tailwind's theme variables are tree-shaken.** `tokens.css` uses
`@theme static` deliberately — plain `@theme` emits only the variables it sees
used in class names, which drops every token read through plain `var()`.

**Base CSS must stay inside `@layer base`.** Unlayered CSS beats every layered
rule, so an unlayered `a { color }` silently overrides `text-white` and every
other colour utility on links site-wide.

**Adding new `.vue` files can leave the dev server's Tailwind scan stale.**
Restart `npm run dev` when a new utility does not apply.

**Nuxt de-duplicates component path segments.** `components/product/ProductCard.vue`
resolves as `<ProductCard>`, not `<ProductProductCard>`.

**Vite blocks the ddev hostname unless it is allow-listed.** The dev server is
reached through ddev's router, not `localhost`, so `vite.server.allowedHosts` in
`nuxt.config.ts` must list `.ddev.site`. Without it every route answers 403.

## reCAPTCHA v3

Three forms are gated: `/lien-he`, `/dai-ly`, and the homepage consultation
block. Keys are not in the repo.

Site key is public — `frontend/.env`:

```
NUXT_PUBLIC_RECAPTCHA_SITE_KEY=6Lxxxxxxxxxxxxxxxxxxxxx
```

Secret key belongs in `web/sites/default/settings.php`, which is **not**
committed:

```php
$settings['keybolts_recaptcha_secret'] = '6Lxxxxxxxxxxxxxxxxxxxxx';
$settings['keybolts_recaptcha_threshold'] = 0.5;
```

Behaviour with no keys configured: the script never loads, no token is sent,
the server skips verification and stores the lead with an empty score. Forms
keep working — this is how dev and staging run.

Behaviour with keys configured: a score below the threshold is rejected with
`422 {"errors":["recaptcha"]}` and nothing is stored. If Google is unreachable
the lead is **accepted** with an empty score and a warning is logged — losing a
real customer's enquiry is worse than storing one unscored lead.

Leads land in `/admin/keybolts/submissions` (entity `contact_submission`, not a
node — they are operational records, not published content).

## Commands

```bash
# Kernel tests (22 currently pass)
ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom --no-coverage"

# Frontend tests (9 currently pass)
cd frontend && npm test

# Content model / seed scripts (all idempotent)
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:script scripts/seed/seed_branches.php

# After any content-model change
ddev drush cex -y && git add config/sync

# Admin login link
ddev drush uli --uri=https://vietlong.ddev.site "/admin/content"
```

## How to work

The plan's tasks are meant to be executed one at a time, each ending in its own
commit, with the tests run before committing. Every task in the plan carries the
full code — transcribe it rather than improvising, because the Vietnamese copy
must match the prototypes byte-for-byte.

**Rules that produced the fewest defects so far:**

- `design/*.html` is the authority for all copy and layout. Re-read the relevant
  file immediately before building each page.
- **A token appears several times per design file — take the LAST occurrence.**
  Each prototype has readable `:root` blocks near the top and a second copy
  inside the escaped template JSON, which is what the page actually renders
  with. This has already caused two real defects: the font (Nunito Sans, not
  Roboto) and the branch address capitalisation (`Đại Lộ`, not `Đại lộ`).
- The prototypes disagree with each other where they were exported at different
  times. The **newer file wins**; check modification times. The nav is six items
  (Sản phẩm · Giới thiệu · Dự án · Tin tức · Đại lý · Liên hệ) — Homepage and
  Product Detail still show an older five-item nav and are stale.
- **Verify by content, never by status code.** A 200 proved nothing the day the
  proxy was serving an unrelated project's site.
- Never commit screenshots or binaries.

## What is out of scope for this plan

Tin tức, Article and Dự án pages need `article` and `project` content types and
are batch 2. Tidying the 28-field product edit form is batch 3. Sending email on
form submission was explicitly deferred — leads are stored and read in admin.

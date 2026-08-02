# Drupal environment setup

This note covers one manual step that every fresh checkout needs before Drupal's
configuration management commands (`drush cex`, `drush cim`) will work. It is not
automated because the file it touches is intentionally excluded from version control.

## Why this step exists

The project's configuration is tracked in `config/sync/` and kept in sync with the
site via Drupal's config management system. That system needs to be told where
`config/sync/` lives relative to the Drupal root, and that location is set in
`web/sites/default/settings.php`.

`web/sites/default/settings.php` is DDEV-generated and environment-specific — it's
listed in `.gitignore` (see `/web/sites/default/settings.php`) and is not, and should
not be, committed. That means the `config_sync_directory` setting has to be added by
hand on every fresh checkout; there is no tracked file that sets it for you.

## Steps for a fresh checkout

1. Start the environment as usual:

   ```bash
   ddev start
   ```

   DDEV will generate `web/sites/default/settings.php` (via its own
   `settings.ddev.php` include) if it doesn't already exist.

2. Open `web/sites/default/settings.php` and append the following line at the end of
   the file:

   ```php
   $settings['config_sync_directory'] = '../config/sync';
   ```

   This points config management at the `config/sync/` directory in the repository
   root (one level above the `web/` docroot, which is what `../` resolves to from
   `web/sites/default/`).

3. Import the tracked configuration into the site:

   ```bash
   ddev drush cim -y
   ```

   This applies everything committed in `config/sync/` — including the product
   content model (vocabularies, the `product` content type and its fields, the
   paragraph types used on the product detail page, and the Pathauto pattern) — to
   the freshly installed site.

Once these steps are done, `ddev drush cex -y` and `ddev drush cim -y` will work
normally for the rest of the session, the same as in any other environment where this
step has already been performed.

## Frontend: letting Node trust DDEV's certificate

Nuxt renders on the server, so its data fetching runs in Node rather than in the
browser. DDEV serves `https://vietlong.ddev.site` with a certificate signed by
**mkcert's** local CA. The browser trusts that CA, but Node keeps its own trust store
and does not, so an SSR fetch fails with:

```
UNABLE_TO_VERIFY_LEAF_SIGNATURE
```

The fix is to point Node at mkcert's root CA via `NODE_EXTRA_CA_CERTS`. The `dev` and
`generate` scripts in `frontend/package.json` do this automatically by shelling out to
`mkcert -CAROOT`, so `npm run dev` works with no extra setup as long as mkcert is
installed (DDEV installs it).

If you use a different local CA, or mkcert lives somewhere unusual, set the variable
yourself and the scripts will respect it:

```bash
export NODE_EXTRA_CA_CERTS="/path/to/rootCA.pem"
```

Do **not** work around this with `NODE_TLS_REJECT_UNAUTHORIZED=0` — that disables
certificate verification for every request the process makes.

## One domain: https://vietlong.ddev.site serves the Nuxt site

The site is headless, but both halves are served from a single origin so the
project URL shows the real site rather than Drupal's admin theme:

| Path | Served by |
|------|-----------|
| `/`, `/san-pham`, `/danh-muc/…`, `/thuong-hieu/…` | Nuxt |
| `/_nuxt/`, `/_fonts/`, Vite dev endpoints | Nuxt |
| `/api/v1/…`, `/jsonapi` | Drupal |
| `/admin`, `/user`, `/node`, `/taxonomy`, … | Drupal |
| `/sites/default/files/…` (product images) | Drupal |

This is done with a custom nginx config at `.ddev/nginx_full/nginx-site.conf`,
which proxies `/` to the Nuxt dev server.

### Running it

```bash
ddev start
cd frontend && npm run dev     # must be running, or / returns 502
```

Then open **https://vietlong.ddev.site/**.

### Three things that will bite you

1. **The dev server listens on port 3100, bound to `0.0.0.0`** — not Nuxt's
   default. Nuxt normally binds loopback only, which the ddev web container
   cannot reach, and port 3000 on this machine is already published by another
   Docker project. With both problems in play the proxy silently served *that
   project's* site instead, with a perfectly healthy `200`. If you change the
   port, change the `upstream nuxt` block in the nginx config to match.

2. **Never let the string `#ddev-generated` reappear in the nginx config** —
   not even inside a comment. ddev looks for that literal anywhere in the file
   and regenerates the file when it finds it, silently discarding the proxy
   setup.

3. **nginx matches regex locations before plain prefixes.** Drupal's
   deny-dotfiles rule and any `.js`/`.css` rule would otherwise claim Nuxt's
   assets and Vite's `/node_modules/.vite/` dev URLs, so every Nuxt route in
   the config uses `^~`, which outranks regex.

# Database snapshot

`vietlong.sql.gz` — full dump of the ddev database, 313 tables.

**This repository is private and must stay private.** The dump contains real
data: customer leads with phone numbers, the admin account's password hash,
and session records. Making the repo public would publish all of it
permanently — forks and caches survive a deletion.

## Restore

```bash
ddev import-db --file=db/vietlong.sql.gz
ddev drush cr
```

## Refresh

```bash
ddev export-db --gzip=false -f db/vietlong.sql && gzip -9 -f db/vietlong.sql
```

## Building without the dump

The dump is a convenience, not the source of truth. A clean site can be built
from the scripts, which is the reproducible path and carries no personal data:

```bash
ddev drush php:script scripts/setup/install_product_model.php
ddev drush php:script scripts/setup/install_product_displays.php
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:script scripts/setup/install_page_displays.php
ddev drush php:script scripts/seed/seed_products.php
ddev drush php:script scripts/seed/seed_branches.php
ddev drush php:script scripts/seed/seed_about.php
ddev drush php:script scripts/seed/seed_dealers.php
ddev drush php:script scripts/seed/seed_contact.php
ddev drush php:script scripts/seed/seed_policies.php
ddev drush php:script scripts/seed/seed_news.php
ddev drush php:script scripts/seed/seed_projects.php
```

Configuration lives in `config/sync` and is imported with `ddev drush cim -y`.

## Not in this repository

Secrets are gitignored and must be recreated on each machine — see
`docs/HANDOFF.md`:

- `web/sites/default/settings.php` — database credentials, hash salt,
  reCAPTCHA secret key, reverse-proxy settings
- `frontend/.env` — reCAPTCHA site key

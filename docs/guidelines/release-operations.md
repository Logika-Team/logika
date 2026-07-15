# Release operations

This runbook applies to the `wordpress` deployment branch. It delivers only
the checked-in Logika theme and plugins; production content, uploads,
`wp-config.php` and unrelated plugins stay owned by their environment.

## GitHub Environments

Create `staging` and `production` Environments. Configure `production` with
required reviewers before enabling the workflow. Add the following secrets to
each environment, with environment-specific values:

| Secret | Meaning |
| --- | --- |
| `SSH_HOST` | SSH hostname or IP address. |
| `SSH_USER` | Dedicated deploy account, not a WordPress administrator. |
| `SSH_PORT` | SSH port. |
| `SSH_PRIVATE_KEY` | Ed25519 private key for the deploy account. |
| `SSH_KNOWN_HOSTS` | Pinned host key output from `ssh-keyscan`, reviewed before storing. |
| `DEPLOY_ROOT` | Private release root containing `releases/`, `current` and `backups/`. |
| `DEPLOY_SITE_ROOT` | Existing WordPress root containing `wp-config.php` and `wp-content/uploads`. |
| `DEPLOY_BACKUP_ROOT` | Protected production backup root; required only in `production`. |
| `WP_CLI_BIN` | Optional remote WP-CLI command. On Plesk this can be `/opt/plesk/php/8.4/bin/php /usr/local/bin/wp`. |

Do not store WordPress passwords, database passwords, CRM credentials or
Cloudflare tokens in GitHub workflow files or release archives.

## Staging database sync

Staging may receive a local WordPress database snapshot, but production must
not. The GitHub runner cannot read a developer machine on push, so the sync is
two-step:

1. From the local WordPress checkout, run
   `scripts/release/export-local-staging-db.sh`.
2. If `DEPLOY_HOST`, `DEPLOY_USER` and `DEPLOY_ROOT` are set, the script uploads
   a private pending dump to `DEPLOY_ROOT/incoming/staging-db.sql.gz`.
3. The next `wordpress` branch staging deployment imports that pending dump,
   backs up the previous staging database under `DEPLOY_ROOT/db-backups/`,
   forces `home` and `siteurl` back to
   `https://staging.logika.resumemyhost.miy.link`, sets `blog_public=0`, flushes
   cache and moves the pending dump aside.

The export uses WP-CLI `search-replace --export`, so serialized values are
rewritten safely and the local database is not modified. The script refuses a
dump that still contains localhost, DDEV or the production URL.

This is a staging convenience only. Production database restore or migration
still requires explicit approval, a protected backup, a staging restore test and
a release-manifest migration flag.

## Server bootstrap

Run the following only after a verified server backup and during a scheduled
maintenance window:

1. Create a non-login deploy user whose writable paths are only `DEPLOY_ROOT`
   and the three managed component links under `DEPLOY_SITE_ROOT/wp-content`.
   The user must run the server `wp` command for this one WordPress site.
2. Create a 30-day protected backup location outside the public web root.
3. Run `scripts/release/preflight.sh` using staging values. It must report the
   WordPress version, active `logika-theme`, active `logika-core`, active
   `logika-leads`, and the REST route inventory.
4. Set `ALLOW_MANAGED_LINK_BOOTSTRAP=1` and run
   `scripts/release/bootstrap-managed-links.sh` on staging. It refuses to
   replace non-symlink directories. The first normal deploy then creates the
   initial `current` release atomically.
5. Repeat the same bootstrap on production only after the staging restore drill
   succeeds. Never run bootstrap as part of a GitHub Actions deploy job.

## Cache and smoke policy

Use the preflight REST inventory as the source of truth. Configure the host or
CDN not to cache `wp-admin`, `wp-login.php`, authenticated requests and the
public stateful routes under `/wp-json/logika/v1/`, including lead submission,
form-token and `phone-country`. `phone-country` must return `Cache-Control:
no-store`.

Staging must serve `X-Robots-Tag: noindex, nofollow` or an equivalent
non-indexable `robots.txt`. The staging workflow checks homepage, WordPress
REST and phone-country without submitting a lead. Production runs the same
read-only checks after its atomic switch.

## Backup, rollback and monitoring

The production workflow runs `scripts/release/backup.sh` before upload. It
stores a compressed database export plus `wp-config.php` and uploads, writes
checksums, and removes backup directories older than 30 days. Restore that
backup on staging before the first production deployment.

Rollback is a server-side atomic switch of `DEPLOY_ROOT/current` to the
preceding verified release. Database restore is not automatic and requires an
explicit migration approval. The release initiator reviews deployment output,
nginx/PHP/WordPress logs, 404s and the lead queue after deployment and again
within 24 hours.

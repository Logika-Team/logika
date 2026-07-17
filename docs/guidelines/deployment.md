# Production requirements

## Infrastructure release readiness

This section is the release-engineering boundary. It can be completed before
the application backlog is complete, but only with attached workflow, backup,
restore and smoke-check evidence. The release source is `wordpress`: a push
deploys the immutable artifact to staging, while production receives the same
artifact only through a manually approved GitHub Environment deployment.

- [ ] GitHub `staging` and `production` Environments contain SSH host, user,
  port, release root, WordPress root, deploy key and pinned `known_hosts`.
- [ ] Staging is `https://staging.logika.resumemyhost.miy.link`, has its own
  database and files, serves HTTPS and is non-indexable.
- [ ] Managed component paths are verified symlinks to
  `DEPLOY_ROOT/current/wordpress/...`; uploads, `wp-config.php` and unrelated
  server plugins are not release payload.
- [ ] The staging workflow has deployed a validated SHA and its smoke checks
  have passed.
- [ ] If a local WordPress database snapshot is needed on staging, it is
  exported with `scripts/release/export-local-staging-db.sh`, uploaded only to
  `DEPLOY_ROOT/incoming/staging-db.sql.gz`, imported only by the staging
  workflow, and followed by staging noindex/smoke evidence.
- [ ] The production Environment requires an approver and deploys only an
  artifact downloaded from a successful staging workflow run.
- [ ] A protected database plus managed-files backup is created before each
  production deploy, retained for 30 days, and has been restored on staging.
- [ ] The preceding release is still present and its rollback has been drilled
  on staging within 30 minutes.
- [ ] The release initiator has reviewed nginx/PHP/WordPress logs, 404s and
  the lead queue during the first 24 hours after production deployment.

## Application go-live readiness

The remaining product requirements below are intentionally not evidence of
infrastructure readiness. Leave them unchecked until the related CRM, import,
redirect, SEO, content and dynamic-page functionality has been implemented and
verified for a public launch.

The operational setup and evidence collection procedure is documented in
[`release-operations.md`](release-operations.md).

## 1. Environments

- [ ] Production environment is separate from local and staging.
- [ ] Staging environment exists and is used for final release checks.
- [ ] Staging is blocked from search indexing.
- [ ] Production runs only over HTTPS.
- [ ] Production domain, DNS and SSL certificate are configured before launch.
- [ ] Production PHP, MySQL and WordPress versions match the approved stack baseline.
- [ ] Production file permissions follow WordPress hosting best practices.

## 2. Access and accounts

- [ ] Every admin/editor has a personal WordPress account.
- [ ] Shared admin accounts are not used.
- [ ] Two-factor authentication is enabled for privileged users.
- [ ] Default usernames such as `admin`, `test`, `manager` are not used.
- [ ] Developer access is limited to required people only.
- [ ] Hosting, DNS, CRM and analytics access owners are documented.
- [ ] Temporary launch access is revoked after release.

## 3. Secrets and configuration

- [ ] Production secrets are not stored in Git.
- [ ] CRM credentials are stored only server-side.
- [ ] SMTP credentials are stored securely.
- [ ] Database credentials are stored securely.
- [ ] WordPress salts are unique for production.
- [ ] Production secrets differ from staging/local secrets.
- [ ] `.env.example` contains placeholders only.
- [ ] Frontend JS bundle does not contain private API keys.

## 4. Build and release artifact

- [ ] Production deploy uses a reproducible build process.
- [ ] Dependencies are installed from lockfiles.
- [ ] Built assets are generated before deployment.
- [ ] Manual edits in `build/`, vendor files or installed plugins are not part of the release process.
- [ ] Deployed theme and plugins match the reviewed source state.
- [ ] ACF Local JSON is included in the deployment artifact.

## 5. CI/CD and automated deployment

- [ ] CI/CD pipeline is configured before connecting real production hosting.
- [ ] Pipeline runs automatically on pull requests.
- [ ] Pipeline runs automatically on pushes to the main deployment branch.
- [ ] Autodeploy is configured for staging from the approved deployment branch.
- [ ] Production deployment is gated by successful CI checks.
- [ ] Production deployment requires explicit approval until the hosting and rollback process are proven stable.
- [ ] Deployment is blocked when any required test/check fails.
- [ ] Pipeline installs dependencies from lockfiles only.
- [ ] Pipeline builds frontend assets with the approved Node/npm stack.
- [ ] Pipeline validates PHP code when WordPress plugin/theme code is present.
- [ ] Pipeline runs unit tests when available.
- [ ] Pipeline runs integration tests when available.
- [ ] Pipeline runs browser smoke tests for critical pages when environment is available.
- [ ] Pipeline runs HTML validation or documents known accepted failures.
- [ ] Pipeline verifies that no production secrets are present in the repository or build artifact.
- [ ] Pipeline uploads or exposes build logs for debugging failed deployments.
- [ ] Failed deployments do not modify production state.
- [ ] Deployment status is visible to the team.

Required CI checks before any autodeploy:

- [ ] Frontend build passes.
- [ ] PHP syntax/static checks pass where applicable.
- [ ] Unit tests pass.
- [ ] Integration tests pass where available.
- [ ] Browser smoke tests pass on staging when available.
- [ ] Migration dry-run or migration safety check passes where available.
- [ ] No secrets are detected in source or generated artifact.

Autodeploy policy:

- [ ] Staging can deploy automatically after all required checks pass.
- [ ] Production deploy must not run from an unchecked commit.
- [ ] Production deploy should use the same reviewed artifact that passed CI.
- [ ] Production deploy should have rollback steps linked in the pipeline or release notes.

## 6. Database and migrations

- [ ] Staging database sync, when used, is staging-only; production has no
  automatic database import from local snapshots.
- [ ] Production database backup exists before deployment.
- [ ] Restore procedure has been tested on staging.
- [ ] Plugin migrations are idempotent.
- [ ] Migrations are tested on a production-like database copy.
- [ ] Schema version options are updated only by migration code.
- [ ] Destructive database changes require explicit approval and rollback plan.
- [ ] CRM retry queue is checked before and after restore/migration operations.

## 7. WordPress and plugins

- [ ] WordPress core version matches approved production baseline.
- [ ] Required plugins are installed and active.
- [ ] Unused plugins are removed, not only disabled.
- [ ] Unused themes are removed, except required fallback theme if hosting policy requires it.
- [ ] `logika-theme` is active.
- [ ] `logika-core` is active.
- [ ] `logika-leads` is active if lead handling is included in current release.
- [ ] ACF Pro is active and field groups are synchronized.
- [ ] SEO plugin is configured.

## 8. Caching and performance

- [ ] Page cache/CDN is configured for public pages.
- [ ] Lead submission endpoints are excluded from cache.
- [ ] Form token endpoint is excluded from cache.
- [ ] `GET /wp-json/logika/v1/phone-country` is excluded from cache and returns `Cache-Control: no-store`.
- [ ] Cloudflare Managed Transform `Add visitor location headers` is enabled so the origin receives `CF-IPCountry`.
- [ ] Admin endpoints are excluded from cache.
- [ ] CRM callback endpoints are excluded from cache.
- [ ] Static assets have cache headers.
- [ ] CSS/JS assets are versioned.
- [ ] Images are optimized for production.
- [ ] Critical pages pass agreed performance smoke checks.

## 9. Forms, leads and CRM

- [ ] Lead forms save requests locally before CRM send.
- [ ] CRM integration uses server-side requests only.
- [ ] CRM credentials are not exposed to browser.
- [ ] CRM timeout policy is configured.
- [ ] CRM retry policy is configured.
- [ ] Lead idempotency is enabled.
- [ ] Duplicate lead handling is enabled.
- [ ] Admin can view lead status.
- [ ] Admin can retry failed leads with correct permissions.
- [ ] CRM failure does not show technical details to users.
- [ ] Production test lead flow is verified according to agreed launch policy.

## 10. Security

- [ ] `WP_DEBUG_DISPLAY` is disabled in production.
- [ ] Debug logs are not publicly accessible.
- [ ] Custom public endpoints validate nonce/token where required.
- [ ] Custom admin endpoints validate capability and nonce.
- [ ] Form inputs are sanitized server-side.
- [ ] Template output is escaped.
- [ ] Upload file types are restricted.
- [ ] Rate limiting or anti-spam protection is enabled for public forms.
- [ ] Security headers are configured at hosting/CDN level where possible.

## 11. SEO and indexing

- [ ] Production robots/indexing settings are correct.
- [ ] Staging noindex settings are not copied to production.
- [ ] Sitemap is generated and accessible.
- [ ] `noindex` cities/entities are excluded from sitemap.
- [ ] Canonical URLs are correct on homepage, city pages and course pages.
- [ ] Redirect map from old Tilda URLs is applied.
- [ ] Priority old URLs are checked after deployment.
- [ ] JSON-LD output is valid on priority templates.
- [ ] SEO titles/descriptions render from configured fields/templates.

## 12. Monitoring and logs

- [ ] PHP error logging is enabled privately.
- [ ] Web server logs are available.
- [ ] CRM send attempts are logged.
- [ ] Lead events are auditable.
- [ ] 404 monitoring is available after launch.
- [ ] Critical form failures can be diagnosed by `request_id` or `lead_id`.
- [ ] First 24 hours after launch have active monitoring owner.

## 13. Rollback requirements

- [ ] Previous production release can be restored.
- [ ] Database backup is available before launch.
- [ ] Theme/plugin rollback steps are documented.
- [ ] DNS rollback or maintenance fallback is documented if needed.
- [ ] Migration rollback limitations are documented.
- [ ] CRM retry behavior after rollback is understood.

## 14. Launch checklist

- [ ] Deploy to staging.
- [ ] Run migrations on staging.
- [ ] Sync ACF field groups on staging.
- [ ] Verify homepage.
- [ ] Verify priority city pages.
- [ ] Verify priority course pages.
- [ ] Verify lead form success path.
- [ ] Verify lead form validation errors.
- [ ] Verify CRM failure handling.
- [ ] Verify sitemap and robots.
- [ ] Verify redirects.
- [ ] Take production backup.
- [ ] Deploy production release.
- [ ] Run production smoke checks.
- [ ] Monitor logs and lead queue after launch.

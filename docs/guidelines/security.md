# Security: Rules for Logika School

## 1. Security context

Logika School site does not include a student, parent or visitor cabinet. Public users do not register, log in, or manage personal data through frontend interfaces.

Main risk areas:

- WordPress admin area;
- trial lesson or consultation lead forms;
- local lead storage before CRM forwarding;
- webhook/API CRM integration;
- CRM, SMTP, analytics and infrastructure secrets;
- media, plugins, theme and custom code.

Core principle:

> Project security is built around protecting the admin area, personal data from leads, and reliable lead delivery into CRM.

## 2. Authentication and access

Public site:

- does not create user accounts for visitors;
- does not have a personal cabinet;
- must not expose private APIs without checks;
- must not store user sessions without explicit need.

WordPress admin:

- access only for team members and contractors who truly need it;
- each person must have an individual account, shared accounts are forbidden;
- enable two-factor authentication for administrators and editors;
- use minimal required roles: `administrator` only for technical owners, editors and SEO roles are limited;
- remove/deactivate accounts of people no longer working on the project;
- do not use login names such as `admin`, `test`, `manager`;
- limit login attempts and enable brute-force protection;
- where possible, restrict access to `/wp-admin` and `wp-login.php` by IP, VPN or hosting firewall layer.

Developers:

- should not work with production client accounts;
- should not store production credentials in local project files;
- should get access via secret manager or protected channel.

## 3. Secret storage

Secrets must not be committed to Git.

Secrets include:

- CRM API keys;
- webhook tokens;
- SMTP credentials;
- database credentials;
- WordPress salts;
- private keys;
- access tokens for analytics, maps, CDN and third-party services;
- production URLs with embedded credentials;
- backup credentials.

Rules:

- store secrets in environment variables, hosting secret manager, or protected `wp-config.php` outside version control;
- for local development use `.env.local` or equivalent and include it in `.gitignore`;
- keep only `.env.example` with placeholder values in repo;
- secrets must differ per local, staging and production environments;
- rotate secret immediately on leakage and review access logs;
- do not expose secrets in HTML, JS, server logs, error pages or debug dumps;
- never expose CRM keys to the frontend, even if endpoint access looks safe.

Rule example:

```text
Frontend form -> WordPress endpoint -> server-side CRM request
```

Forbidden:

```text
Frontend form -> direct CRM API request with public API key
```

## 4. Lead forms

Lead forms are the primary public entry point for user data.

Each form must:

- validate required fields in frontend for UX and on backend for security;
- verify `nonce` or another WordPress anti-CSRF token;
- have rate limiting by IP, user agent and/or fingerprint signals;
- include a honeypot or other lightweight anti-spam mechanism;
- accept only expected fields;
- ignore unknown fields when they are not in the lead contract;
- normalize phone, name, child age, city, course, UTM and source URL;
- escape output in admin views;
- not trust hidden fields as source-of-truth without server validation.

Personal data:

- collect only what is needed to process the lead;
- do not request unnecessary child data;
- do not store payment details, documents or medical data;
- show a privacy-policy link near form submit;
- store consent date, source URL and consent text version where required by law.

## 5. Local lead storage

Leads must never be lost even if CRM is temporarily unavailable.

Rules:

- save the lead locally in WordPress first;
- then send it to CRM server-side;
- store send status: `pending`, `sent`, `failed`, `retrying`;
- store technical CRM error details: HTTP code, error code, short message;
- do not store full response body if it contains secrets or unnecessary personal data;
- every lead must have stable `lead_id` or idempotency key;
- resending should not create duplicates;
- access to lead view in admin must be role-based or capability-based.

Lead logs should be useful for diagnostics but safe:

- allowed: `lead_id`, status, endpoint alias, HTTP code, timestamp;
- not allowed: CRM key, webhook signature secret, full payload with plain phone in public logs;
- mask personal data in debug logs.

## 6. API and CRM webhook protection

All requests to CRM must be server-side, never from browser.

Outgoing CRM request rules:

- use HTTPS only;
- store CRM endpoint and tokens as secrets;
- sign payloads when CRM supports HMAC/signature;
- pass idempotency key to protect against duplicates;
- set timeout and retry policy;
- do not use endless retries;
- validate CRM success with explicit contract, not only HTTP 200;
- separate production CRM from staging/test CRM;
- keep secure dry-run or sandbox mode for development.

Incoming callbacks (if CRM sends events back):

- accept callbacks only on dedicated endpoint;
- validate signature, shared secret or token;
- validate timestamp when supported to reduce replay attacks;
- validate event type and payload schema;
- respond uniformly and safely to unknown events;
- do not execute arbitrary actions without allowlist.

## 7. WordPress REST API and AJAX

Every custom endpoint must have a clear contract.

Public endpoints:

- must expose only needed actions for the public site;
- must validate request method, nonce/token, content type and payload;
- must have rate limiting;
- must not return private ACF fields, full phone/email, internal CRM IDs or debug details;
- must return consistent safe errors without exposing internal structure.

Admin endpoints:

- must check `current_user_can(...)`;
- must not rely only on hiding buttons in UI;
- must use nonce for state-changing actions;
- must log critical actions such as lead retry, export and CRM setting changes.

## 8. Plugins, theme and dependencies

Rules:

- install only required plugins;
- avoid plugins that duplicate `logika-core` features;
- update WordPress core, plugins and theme by schedule;
- review changelogs before major updates;
- remove unused themes/plugins instead of only disabling;
- do not edit vendor files or plugins directly;
- keep custom code in `logika-theme` and `logika-core` so changes are versioned;
- do not load third-party scripts without need and without understanding what data they collect.

For frontend dependencies:

- pin versions via lockfile;
- avoid heavy libraries for minor tasks;
- check dependencies for known vulnerabilities before release;
- do not store secrets inside JS bundle.

## 9. XSS, CSRF, and SQL injection protections

XSS:

- escape all data from ACF, CPT, forms and settings on output;
- use `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` according to context;
- do not output raw HTML from admin without allowlist;
- keep rich-text fields to allowed tags only.

CSRF:

- all state-changing actions must verify nonce;
- admin AJAX/REST actions must verify capability and nonce;
- public lead submission must include anti-automation tampering protection.

SQL injection:

- use `WP_Query`, `get_posts`, meta queries and WordPress API;
- for manual SQL always use `$wpdb->prepare`;
- do not build SQL by concatenating user input;
- validate `orderby`, `order`, taxonomy and meta keys against allowlist.

## 10. Files, media and uploads

If uploads are allowed in admin:

- allow only safe MIME types;
- do not allow SVG without sanitization;
- do not allow PHP, HTML, JS and executable file types;
- set file size limits;
- use WordPress media library for storage;
- verify alt texts and public filenames to avoid internal data leaks.

If forms ever get upload fields, this requires separate architecture:

- virus scanning;
- strict MIME/type restrictions;
- private storage strategy.

By default, public Logika forms should not accept files.

## 11. Privacy and data minimization

The project should collect only data required to process a lead.

Rules:

- name, phone, child age, city, course and comment are valid only if needed by manager;
- do not store extra hidden data;
- do not pass personal data to analytics in query strings or client events;
- do not include personal data in URL query params;
- keep lead retention period configured in WordPress;
- ensure deletion or anonymization of old leads;
- restrict lead exports by role and log each export action.

## 12. Errors and debug mode

Production:

- `WP_DEBUG_DISPLAY` should be off;
- errors must not show stack traces to users;
- debug logs must not be publicly accessible;
- user-facing messages should be neutral: "Could not submit the request. Try again later or contact us.";
- technical details should only go into restricted logs.

Local/staging:

- debug may be enabled, but secrets and personal data must still not leak into shared logs;
- staging should use test CRM or dry-run mode;
- staging should be non-indexable.

## 13. Security release checklist

Before deployment:

- run dependency vulnerability checks;
- run security lint/static checks;
- verify admin hardening is in place;
- verify no secrets in repo and deployment payload;
- run lead form negative tests (spam, duplicate, XSS payloads, rate limit);
- verify CRM errors are not exposed to users;
- verify lead data is not sent to third-party analytics without consent and minimization.

After deployment:

- rotate temporary credentials if any were exposed during rollout;
- validate webhook endpoints and callback signature verification;
- review logs for security anomalies in first 24h;
- run smoke test for protected admin actions;
- verify backups and restore plan still work.

## 14. Security non-goals (for now)

Not needed until dedicated architecture is approved:

- full WAF tuning in local development;
- advanced fraud scoring engine;
- complete SIEM integration;
- internal custom crypto module;
- storing CRM API keys in DB without encryption.

These items can be added in follow-up phases with explicit ownership and testing plan.

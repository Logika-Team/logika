# MCP: WordPress + ACF Pro

Date: 2026-07-11  
Project: Logika School  
Scope: local/staging machine-readable content workflows for WordPress, ACF Pro and MCP-compatible agents.

## 1. Purpose

MCP lets approved developer tools connect to WordPress through the WordPress MCP Adapter and use ACF abilities for structured content work.

For this project, MCP is allowed for:

- discovering ACF-enabled content structure;
- registering or updating ACF-managed CPT, taxonomy and field group definitions during implementation;
- testing machine-readable content workflows before production;
- validating that field groups, CPTs and taxonomies expose the expected AI-access metadata.

MCP is not a public site feature and must not be exposed to anonymous visitors.

## 2. Requirements

- WordPress 6.9 or later.
- ACF Pro 6.8.1 or later.
- MCP Adapter plugin installed and active.
- DDEV local environment for WordPress.
- WP-CLI available through DDEV.
- Dedicated local/staging WordPress user `logika_mcp`.

Install the MCP Adapter in the DDEV WordPress site:

```bash
ddev wp plugin install https://github.com/WordPress/mcp-adapter/releases/latest/download/mcp-adapter.zip --activate
```

Verify ACF Pro and MCP Adapter:

```bash
ddev wp plugin list --status=active
ddev wp mcp-adapter list
```

## 3. Project files

The project-level MCP client configuration is:

```text
.mcp.json
```

It starts the default WordPress MCP Adapter server through:

```text
scripts/wp-mcp.sh
```

The wrapper intentionally calls `ddev wp` instead of a host `wp` binary so the MCP client uses the same PHP, database and WordPress environment as the project.

ACF AI support is enabled by:

```text
wp-content/mu-plugins/logika-acf-ai.php
```

This file only enables ACF AI/Abilities integration. It does not store secrets and does not register project content by itself.

## 4. Setup

Create the dedicated local/staging MCP user after WordPress is running:

```bash
ddev wp user create logika_mcp dev@example.invalid --role=administrator --user_pass="$(openssl rand -base64 24)"
```

Do not use `admin`, `test` or shared production accounts for MCP.

Confirm WordPress can serve MCP over stdio:

```bash
./scripts/wp-mcp.sh mcp-adapter list
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}' \
  | ./scripts/wp-mcp.sh mcp-adapter serve --server=mcp-adapter-default-server --user=logika_mcp
```

Then open the project in an MCP-compatible client and use the checked-in `.mcp.json`.

## 5. ACF object rules

For ACF-managed post types, taxonomies and field groups that should be available through MCP:

- set `show_in_rest` to `true`;
- set `allow_ai_access` to `true`;
- add clear `ai_description` text where ACF supports it;
- keep structural definitions in ACF Local JSON under `wp-content/plugins/logika-core/acf-json/`;
- do not use MCP to create production-only, unreviewed field names.

## 6. Security rules

- MCP is enabled only for local and controlled staging workflows unless a separate production approval exists.
- The MCP user must be individual or environment-specific, never shared with normal editors.
- No application passwords, cookies, tokens or private URLs are committed to Git.
- Schema/content mutations through MCP must be reviewed the same way as normal code or ACF Local JSON changes.
- Public REST endpoints still require the protections defined in `docs/security.md`.

## 7. Current project status

This repository currently contains the frontend source, project documentation and vendored ACF Pro, but the DDEV WordPress skeleton is not configured yet.

The MCP config is therefore ready to use after Phase 2 creates the WordPress/DDEV environment and activates ACF Pro plus MCP Adapter.

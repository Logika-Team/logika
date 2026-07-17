# Project
The project is a multi-page landing site built for an educational school in Ukraine for children and teenagers for programming and English language training.  
The project does not include personal account functionality; it only supports application submission for enrollment. The site acts as a sales page so users ultimately submit an enrollment request.  
It is important to enable a high level of customization so all texts and images can be changed dynamically, and articles can be published to the Blog. This is accomplished with ACF Pro.  
One of the main technical capabilities is selecting a city via an interactive map or the navbar, after which regional news and regional promotions for that specific location are displayed.

### Rules:

1. Always read `/docs/guidelines` for stable project documentation and `/docs/changelog` for recent change notes.
2. Write the smallest possible number of lines to complete the task — fewer lines are better.
3. The entire site must be only in Ukrainian.
4. When you make a significant architectural change, add it to `/rules/structure.md`.
5. Describe business logic according to the single responsibility principle.
6. When planning implementation steps, break work into steps and add them to `/docs/guidelines/plan.md`.
7. Use `/.agents/skills` to verify what you are going to do.
8. Always do exactly what was requested and respond with short status updates without long text.
9. For API work, use `/docs/guidelines/api`.
10. For testing, use `/docs/guidelines/testing.md`.
11. When adding a new feature not listed in `/docs/guidelines/features.md`, add a feature description to `/docs/guidelines/features.md` to keep our backlog.
12. For database work, use `/docs/guidelines/database.md`.
13. Before starting work, always check the stack in `/docs/guidelines/tech-stack.md`.
14. For security, always refer to `/docs/guidelines/security.md`.
15. See `/docs/guidelines/project.md` for more detailed project goals.
16. For architecture work, use `/rules/architecture.md` and always update `architecture.md` when a new architectural innovation appears.
17. Always follow the rules in `rules/rules.md`.
18. Update project launch instructions in `/README.md`.
19. For deployment preparation or deployment readiness checks, see `/docs/guidelines/deployment.md`.
20. For guidance on using ACF Pro for content model configuration, see `/docs/guidelines/content-model.md`.
21. Follow REST principles.
22. Always check `/docs/guidelines/edge-cases` before starting.
23. For the latest documentation, always check `/docs/guidelines/links.md`.
24. For CI/CD setup, refer to `/docs/guidelines/deployment.md`.
25. All local WordPress development must use Docker and DDEV to ensure a consistent, reproducible environment across all developers and AI agents.
26. For any ACF Pro, ACF Local JSON, content model, field group, CPT, taxonomy, options page, or ACF runtime work, use the project MCP setup. Work from the WordPress/DDEV checkout at `/home/sbaikov/Desktop/Projects/logika/.worktrees/codex-implement-plan`, start/check DDEV first, and use both:
    - `logika-wordpress` via `.mcp.json` / `./scripts/wp-mcp.sh` for WordPress MCP Adapter runtime abilities.
    - our local ACF MCP server from `/home/sbaikov/Desktop/Projects/acf_mcp` for safe Local JSON audits, dry runs, backups, and sync checks.

27. After any meaningful project change, update documentation in Obsidian vault at `/home/sbaikov/Obsidian/obsidian-backup/Obsidian vault/Projects/logika-school` using local skills `obsidian-vault` and `obsidian-markdown` as a brief dated changelog:
    - Use files by month: `docs/changelog/YYYY-MM.md` (example: `docs/changelog/2026-07.md`).
    - Inside each file, keep concise date sections:
      - `## YYYY-MM-DD` (for example, `## 2026-07-14`)
      - Short bullet list of what changed that day, without long descriptions.
    - One file should contain all meaningful changes for that month (ACF, deployment, CPT, API, etc.), not separate files per technology.

ACF MCP commands:

```bash
cd /home/sbaikov/Desktop/Projects/logika/.worktrees/codex-implement-plan
ddev describe
./scripts/wp-mcp.sh core version
./scripts/wp-mcp.sh plugin get advanced-custom-fields-pro --format=json
./scripts/wp-mcp.sh plugin get mcp-adapter --format=json
./scripts/wp-mcp.sh mcp-adapter list
```

Run our Local JSON MCP server for this project:

```bash
cd /home/sbaikov/Desktop/Projects/logika/.worktrees/codex-implement-plan
ACF_MCP_ACF_JSON_DIR="$PWD/wordpress/wp-content/plugins/logika-core/acf-json" \
ACF_MCP_WP_CLI_BIN="./scripts/wp-mcp.sh" \
node /home/sbaikov/Desktop/Projects/acf_mcp/dist/server/stdio.js
```

When connected to our ACF MCP server, run the relevant checks before changing ACF JSON:

```text
acf_runtime_status
acf_audit_ai_access
acf_validate_runtime_compatibility
acf_audit_schema_mappings
acf_sync_json with { "dryRun": true }
```

When connected to `logika-wordpress`, use MCP Adapter tools to inspect runtime ACF abilities:

```text
mcp-adapter-discover-abilities
mcp-adapter-get-ability-info
mcp-adapter-execute-ability with ability_name "acf/field-groups"
```

Never edit ACF structures blindly. Prefer Local JSON + `dryRun` first, review the git diff, then use `acf_sync_json` / `./scripts/wp-mcp.sh acf json sync --dry-run` before any non-dry-run sync.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

## Local WordPress MCP

ACF machine-readable content workflows are prepared through `.mcp.json`, `scripts/wp-mcp.sh` and `wp-content/mu-plugins/logika-acf-ai.php`.

After the DDEV WordPress skeleton exists, install and activate the MCP Adapter:

```bash
ddev wp plugin install https://github.com/WordPress/mcp-adapter/releases/latest/download/mcp-adapter.zip --activate
ddev wp mcp-adapter list
```

Full setup and verification steps are in `docs/guidelines/mcp.md`.

## Skill usage for tool work

For tool-related tasks in this repo, use local skills from `.agents/skills` when they match the task domain.

- `cloudflare-development`, `nginx-configuration`, `docker-patterns`, `mysql`, `ssh` and `wp-wpcli-and-ops` were added in this worktree branch.
- For infrastructure/hosting, use `cloudflare-development` or `nginx-configuration`; for shell or server access, use `ssh`; for containerization and local env, use `docker-patterns`; for database work, use `mysql`; and for WordPress operational work, use `wp-wpcli-and-ops`.
- This should be the default before using related tools or editing workflows that rely on these domains.

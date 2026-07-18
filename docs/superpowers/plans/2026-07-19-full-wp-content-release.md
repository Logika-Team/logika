# Full WordPress Content Release Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deploy the complete checked-in `wordpress/wp-content` tree atomically to staging.

**Architecture:** Release archives contain all tracked runtime files under `wordpress/wp-content`, excluding mutable `uploads` and temporary WordPress upgrade directories. The active server `wp-content` becomes one symlink to the verified release; each release links `uploads` to a persistent server directory.

**Tech Stack:** Bash, tar, SSH, WordPress CLI, Node test runner.

## Global Constraints

- Build only from `.worktrees/wordpress` on branch `wordpress`.
- Preserve server uploads, database and `wp-config.php`.
- Staging only; do not touch production.

---

### Task 1: Package the complete runtime tree

**Files:**
- Modify: `scripts/release/build-artifact.sh`
- Modify: `tests/release-infrastructure.test.mjs`

- [ ] Test that an artifact contains `wordpress/wp-content` and excludes uploads and upgrade directories.
- [ ] Build the artifact directly from the runtime tree without overlaying static `build/` output.
- [ ] Run `node --test tests/release-infrastructure.test.mjs`.

### Task 2: Switch one managed WordPress content tree

**Files:**
- Modify: `scripts/release/deploy.sh`
- Modify: `tests/release-infrastructure.test.mjs`

- [ ] Test that deploy validates the complete `wp-content` archive and links persistent uploads before switching `current`.
- [ ] Replace the three component symlink checks with one `wp-content` symlink check.
- [ ] Run release tests and staging preflight.

### Task 3: Document and deploy staging

**Files:**
- Modify: `docs/guidelines/release-operations.md`
- Modify: `docs/plan.md`
- Modify: `rules/structure.md`

- [ ] Record the full-content release boundary and rollback method.
- [ ] Build, checksum-check, back up, bootstrap and deploy staging.
- [ ] Verify live homepage, `vacancies`, modal and active release checksum.

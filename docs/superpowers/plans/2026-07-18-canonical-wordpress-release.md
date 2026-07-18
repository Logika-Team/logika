# Canonical WordPress Release Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build and deploy staging only from the canonical `wordpress` worktree, with a verifiable manifest of every release file.

**Architecture:** A focused Bash check validates the source branch and reports dirty WordPress/static files in other local worktrees without changing them. The existing archive builder writes a SHA-256 file manifest; deployment verifies it before atomically switching `current`.

**Tech Stack:** Bash, Git worktrees, tar, sha256sum, Node.js built-in test runner, SSH.

## Global Constraints

- `.worktrees/wordpress` / branch `wordpress` is the only local staging source.
- Keep the existing theme, `logika-core` and `logika-leads` release scope.
- Never upload WordPress core, third-party plugins, `uploads`, `wp-config.php`, caches or local artifacts.
- Preserve the managed theme JavaScript; `build/js` is not deployed.
- The source-status command is read-only.

---

### Task 1: Enforce and report the canonical source

**Files:**
- Create: `scripts/release/release-source-status.sh`
- Modify: `scripts/release/build-artifact.sh`
- Modify: `tests/release-infrastructure.test.mjs`

**Interfaces:**
- Consumes: a source-root path and Git worktree metadata.
- Produces: exit `0` for the `wordpress` source with no outside edits; exit `1` with every outside dirty `source/` or `wordpress/wp-content` path listed.

- [ ] **Step 1: Write the failing source-contract test**

Add a Node assertion that requires `release-source-status.sh` to use `git branch --show-current`, `git worktree list --porcelain`, and `git status --porcelain -- source wordpress/wp-content`. Assert that `build-artifact.sh` invokes the script before `npm run backend`.

- [ ] **Step 2: Verify red**

```bash
node --test --test-name-pattern='canonical source' tests/release-infrastructure.test.mjs
```

Expected: FAIL because the script does not exist.

- [ ] **Step 3: Implement the minimal read-only check**

Create a Bash script that resolves `${1:-$PWD}`, requires branch `${RELEASE_SOURCE_BRANCH:-wordpress}`, lists Git worktrees, skips the selected root, and reports each dirty `source/` or `wordpress/wp-content` path. Invoke it from the artifact builder before the frontend build.

- [ ] **Step 4: Verify green and commit**

```bash
node --test --test-name-pattern='canonical source' tests/release-infrastructure.test.mjs
git add scripts/release/release-source-status.sh scripts/release/build-artifact.sh tests/release-infrastructure.test.mjs
git commit -m "fix: guard the canonical WordPress release source"
```

### Task 2: Verify every archived runtime file before switching staging

**Files:**
- Modify: `scripts/release/build-artifact.sh`
- Modify: `scripts/release/deploy.sh`
- Modify: `tests/release-infrastructure.test.mjs`

**Interfaces:**
- Consumes: staged `wordpress/` tree.
- Produces: `release-files.sha256` in the archive; deploy exits non-zero when a file differs from that manifest.

- [ ] **Step 1: Write failing manifest assertions**

Require `release-files.sha256` in the archive and require it to contain `wordpress/wp-content/themes/logika-theme/assets/js/main.js`. Require deploy to run `sha256sum -c release-files.sha256` before `current.next`.

- [ ] **Step 2: Verify red**

```bash
node --test --test-name-pattern='manifest' tests/release-infrastructure.test.mjs
```

Expected: FAIL because the archive lacks the manifest.

- [ ] **Step 3: Implement the manifest**

After the CSS/image overlay, add:

```bash
( cd "$staging_dir" && find wordpress -type f -print0 | sort -z | xargs -0 sha256sum ) > "$staging_dir/release-files.sha256"
```

Archive the file, allow it in deploy validation, and verify it after extraction with:

```bash
( cd "$release_dir" && sha256sum -c release-files.sha256 )
```

- [ ] **Step 4: Verify green and commit**

```bash
node --test --test-name-pattern='manifest' tests/release-infrastructure.test.mjs
git add scripts/release/build-artifact.sh scripts/release/deploy.sh tests/release-infrastructure.test.mjs
git commit -m "fix: verify WordPress release file manifest"
```

### Task 3: Gate the current handoff and deploy staging

**Files:**
- Modify: `docs/guidelines/release-operations.md`
- Modify: `docs/plan.md`
- Test: `tests/release-infrastructure.test.mjs`

**Interfaces:**
- Consumes: canonical worktree and source-status output.
- Produces: staging deployment only after untransferred outside edits have been handled explicitly.

- [ ] **Step 1: Run the status check**

```bash
scripts/release/release-source-status.sh .
```

Expected now: FAIL and list the dirty root checkout. Do not copy or delete any listed file automatically.

- [ ] **Step 2: Transfer reviewed changes only**

Compare every listed path with its WordPress renderer. Transfer only a reviewed runtime file to `.worktrees/wordpress`; `source/*.html` must have a corresponding WordPress template/source-page change. Re-run the status check and report every path that cannot be mapped safely.

- [ ] **Step 3: Document, verify and deploy**

Document the gate and manifest, run `node tests/release-infrastructure.test.mjs`, then run the existing backup, artifact, deploy, preflight and smoke scripts against staging. Verify the active server manifest and affected browser paths.

- [ ] **Step 4: Commit**

```bash
git add docs/guidelines/release-operations.md docs/plan.md
git commit -m "docs: document canonical WordPress release checks"
```

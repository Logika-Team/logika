# City Selector Data Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove non-city selector entries and make every remaining city belong to its correct oblast.

**Architecture:** WordPress `city` posts and their `region` terms remain the selector source. Seed and import scripts must not recreate invalid records; the REST contract test checks the public payload and removes its temporary fixture.

**Tech Stack:** WordPress, WP-CLI, PHP contract tests.

## Global Constraints

- Keep public content Ukrainian and make no frontend selector changes.
- Use the active DDEV worktree and reversible data changes where possible.

---

### Task 1: Protect the public city payload

**Files:**
- Modify: `tests/city-api.php`

- [x] Add a failing assertion that rejects `Академмістечко`, `Контрактова`, `Онлайн`, `Місто для карти`, `Інші міста`, and an incorrectly grouped `Новий Розділ`.
- [x] Run: `ddev exec php tests/city-api.php`; expected: failure before the data cleanup.
- [x] Delete test-only map posts and branches from a shutdown handler so the check cannot pollute the public API.

### Task 2: Correct selector source data

**Files:**
- Modify: `scripts/seed-cities.php`
- Modify: `scripts/import-tilda-branches.php`

- [x] Remove the obsolete online city seed and associate the Контрактова branch with Київ while retaining its branch label.
- [x] In DDEV, trash non-city records, assign every valid former «Інші міста» record to its oblast, and assign Новий Розділ to Львівська область.
- [x] Run: `ddev exec php tests/city-api.php`; expected: public contract passes.

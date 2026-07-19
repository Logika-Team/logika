# English Course Skills Arrow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the supplied gray dotted arrow behind the five skill cards on English course pages.

**Architecture:** Keep the existing PHP/ACF markup unchanged. Copy the supplied PNG into the theme assets and render it as a non-interactive pseudo-element of `.english-course-skills` in the already-enqueued `english-course.css`; hide it when the cards become vertical.

**Tech Stack:** WordPress classic theme, plain CSS, supplied PNG asset, DDEV/browser smoke check.

## Global Constraints

- Use the canonical `.worktrees/wordpress` checkout.
- Keep the public site Ukrainian-only.
- Do not add JavaScript, PHP, ACF fields, or dependencies.
- Preserve unrelated dirty worktree changes.
- Keep the arrow decorative with `pointer-events: none` and cards above it.

---

### Task 1: Add the arrow asset

**Files:**
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/english-course/dotted-arrow.png`

**Interfaces:**
- Consumes: `/home/sbaikov/Downloads/hand-drawn-dotted-arrow-shape-free-png-2695930054.png`
- Produces: A theme-local PNG available to `assets/css/english-course.css`.

- [ ] **Step 1: Copy the supplied asset**

Run from the worktree root:

```bash
mkdir -p wordpress/wp-content/themes/logika-theme/assets/img/english-course
cp /home/sbaikov/Downloads/hand-drawn-dotted-arrow-shape-free-png-2695930054.png wordpress/wp-content/themes/logika-theme/assets/img/english-course/dotted-arrow.png
```

Expected: the destination file exists and remains a 1920x1335 grayscale+alpha PNG.

- [ ] **Step 2: Verify the asset copy**

```bash
file wordpress/wp-content/themes/logika-theme/assets/img/english-course/dotted-arrow.png
identify -format 'width=%w height=%h channels=%[channels]\n' wordpress/wp-content/themes/logika-theme/assets/img/english-course/dotted-arrow.png
```

Expected: `1920 x 1335` and `graya` channels.

### Task 2: Connect the asset to the skills section

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/english-course.css`

**Interfaces:**
- Consumes: `.english-course-skills`, its existing `::after` decoration, and the asset from Task 1.
- Produces: A responsive gray arrow layer behind `.english-course-skills` heading and cards.

- [ ] **Step 1: Add the minimal CSS rule**

Insert immediately after the existing `.english-course-skills` rule:

```css
.english-course-skills:before{position:absolute;z-index:0;top:12%;left:0;width:100%;height:88%;content:"";background:url('../img/english-course/dotted-arrow.png') center/100% 100% no-repeat;opacity:.42;pointer-events:none}
```

Keep the existing `z-index:1` rules for `.english-course-skills .english-course-heading` and `.english-course-skills ul` so the cards remain in front.

- [ ] **Step 2: Hide the desktop-only decoration on mobile**

Add to the existing `@media(max-width:900px)` block:

```css
.english-course-skills:before{display:none}
```

Expected: the arrow is absent when the cards use the two-column/mobile layout and cannot create horizontal overflow.

- [ ] **Step 3: Run static checks**

```bash
git diff --check -- wordpress/wp-content/themes/logika-theme/assets/css/english-course.css
test -f wordpress/wp-content/themes/logika-theme/assets/img/english-course/dotted-arrow.png
rg -n "english-course-skills:before|dotted-arrow.png|pointer-events:none" wordpress/wp-content/themes/logika-theme/assets/css/english-course.css
```

Expected: no whitespace errors, the asset exists, and the three CSS markers are present.

### Task 3: Verify the real page

**Files:**
- No additional files.

**Interfaces:**
- Consumes: The modified theme asset/CSS path on the real English course route.
- Produces: Desktop and mobile smoke evidence with no broken layout.

- [ ] **Step 1: Check the enqueued stylesheet path**

```bash
rg -n "logika-english-course|english-course.css" wordpress/wp-content/themes/logika-theme/functions.php
```

Expected: the existing enqueue still points to `assets/css/english-course.css`.

- [ ] **Step 2: Verify the live route**

Use the configured DDEV route for an English course page and confirm:

```text
Desktop: the gray dotted arrow runs visually from the first skill card toward the fifth; cards/text stay above it.
Mobile: the arrow is hidden; cards stack without horizontal scrolling.
```

- [ ] **Step 3: Update the knowledge graph**

```bash
graphify update .
```

Expected: graphify completes and includes the changed theme CSS/asset paths.

- [ ] **Step 4: Review the final diff**

```bash
git status --short
git diff --stat
git diff -- wordpress/wp-content/themes/logika-theme/assets/css/english-course.css
```

Expected: only the planned asset/CSS files are added or modified, aside from pre-existing dirty files.

# Onboarding Section Decorations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add the supplied dotted SVG curls behind the homepage onboarding cards.

**Architecture:** Keep the SVG decorative and non-interactive. The homepage source markup gets one absolutely positioned image layer; the live theme stylesheet places it behind the onboarding content and hides overflow on narrow screens.

**Tech Stack:** WordPress classic theme, static homepage markup adapter, CSS, SVG.

## Global Constraints

- Change only the WordPress worktree runtime surface.
- Preserve all existing onboarding copy, links, card order and responsive behavior.
- Use the supplied SVG without new dependencies or JavaScript.
- Decorative imagery must have empty alt text and `aria-hidden="true"`.

### Task 1: Add the supplied decorative asset

**Files:**
- Create: `wordpress/wp-content/themes/logika-theme/assets/img/onbording/group-3986.svg`

**Steps:**

- [x] Copy `/home/sbaikov/Downloads/Group 3986.svg` to the theme asset path and rename it to the existing lowercase asset convention.
- [x] Verify the copied file is an SVG and its byte size matches the supplied file.

### Task 2: Add the background layer to the homepage onboarding section

**Files:**
- Modify: `wordpress/wp-content/themes/logika-theme/source-pages/index.php:615-686`
- Modify: `wordpress/wp-content/themes/logika-theme/assets/css/style.css` in the `.onboarding-section` rules.

**Steps:**

- [x] Insert this first child inside `.onboarding-section`, before `.container`:

```html
<div class="onboarding-section__bg" aria-hidden="true">
  <img src="img/onbording/group-3986.svg" alt="">
</div>
```

- [x] Add the minimum CSS needed to keep the layer behind the content:

```css
.onboarding-section{position:relative;overflow:hidden}
.onboarding-section__bg{position:absolute;z-index:0;top:clamp(30px,5vw,80px);left:50%;width:min(1349px,100%);height:auto;transform:translateX(-50%);pointer-events:none}
.onboarding-section__bg img{display:block;width:100%;height:auto}
.onboarding-section__wrapp{position:relative;z-index:1}
```

- [x] Keep the existing card/image selectors unchanged.

### Task 3: Verify the runtime result

**Files:**
- Test: `wordpress/wp-content/themes/logika-theme/source-pages/index.php`
- Test: `wordpress/wp-content/themes/logika-theme/assets/css/style.css`

**Steps:**

- [x] Run `ddev exec php -l wordpress/wp-content/themes/logika-theme/source-pages/index.php` and confirm no syntax errors.
- [x] Verify the asset reference resolves to `img/onbording/group-3986.svg` and the SVG has the expected `1349x1370` viewBox.
- [x] Open the homepage in the existing DDEV runtime and confirm the section contains the loaded decorative layer; browser screenshot capture was unavailable because the CDP capture timed out.

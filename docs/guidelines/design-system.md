# Design System: Logika School

Date: 2026-07-10  
Project: Logika School  
Visual language source: ready HTML/SCSS/JS markup in `source/`

## 1. Purpose of the design system

The Logika School design system describes visual rules, UI components, content patterns and constraints that must be preserved when moving the current markup into WordPress.

Goal:

> Keep the site visually consistent, CMS-manageable and robust to content variation: long headings, many cities, different course counts, empty data states and new landing pages.

The design system does not replace existing markup. It defines how to evolve it without UI collapse.

## 2. Brand character

Logika is a children-focused educational school for parents, children and teenagers.

Visual tone:

- friendly;
- confident;
- technological but not cold;
- colorful but not chaotic;
- clear for parents;
- engaging for children.

Balance:

- for parents: trust, structure, proof points, and a clear CTA;
- for children: energy, characters, illustrations, sense of play and progress;
- for SEO/GEO/AEO: clear headings, readable copy and predictable page structure.

## 3. Interface principles

### 3.1. Clarity before decoration

Each screen should quickly answer:

- what Logika offers;
- which age group it is for;
- where people can study;
- what the child gets;
- how to request a free lesson.

Decorative characters, stickers, cards and badges support meaning but should not replace it.

### 3.2. Component orientation

Any repeated block should become a component:

- hero;
- course card;
- city selector;
- branch card;
- FAQ accordion;
- lead form;
- review card;
- CTA section;
- trust bar;
- English level card.

When moving to WordPress, components should live as `template-parts`, ACF Blocks or CPT-driven partials, not as copied HTML on every page.

### 3.3. Data resilience

Components must handle:

- long Ukrainian and Russian headings;
- missing images;
- no local reviews;
- empty branch lists;
- different age ranges;
- varying CTA counts;
- different cities and URLs;
- mobile screens.

### 3.4. Conversion without coercion

Main CTA is request for a free lesson or consultation.

Rules:

- CTA must be visible;
- form should be short;
- text near form explains next step;
- user should not feel forced to decide immediately;
- privacy consent should always be visible near submit.

## 4. Design tokens

Tokens are already defined in `source/scss/general/_vars.scss`. During WordPress migration keep them as CSS custom properties.

### 4.1. Typography tokens

```css
--font-primary: "Montserrat", sans-serif;
--body-font-size: clamp(12px, 2.083vw, 16px);
```

Rules:

- primary font: Montserrat;
- body text: weight 500, line-height 140%;
- large headings: weight 700;
- do not add second primary font without separate decision;
- do not use negative letter spacing in new compact UI without overflow checks.

### 4.2. Layout tokens

```css
--content-width: 1440px;
--content-width-mode: 1920px;
--content-width-mini: 952px;
--container-offset: clamp(16px, 5.859vw, 60px);
--container-width: calc(var(--content-width) + (var(--container-offset) * 2));
--header-height: 125px;
```

Responsive header heights:

```text
desktop: 125px;
tablet: 110px;
mobile: 190px;
```

Rules:

- all primary content lives inside `.container`;
- full-width sections are okay, but inner content stays constrained by container;
- do not create new container widths without reason;
- use `max-width` for narrow text blocks instead of new layout grids.

### 4.3. Color tokens

#### Brand colors

| Token | Hex | Usage |
|---|---|---|
| `--violet-500` | `#29235C` | main headings, dark brand surfaces |
| `--violet-300` | `#2F2535` | body text, button text on yellow |
| `--violet-100` | `#602B7A` | forms, accent cards, secondary brand backgrounds |
| `--yellow` | `#FFD631` | primary CTA |
| `--green` | `#95C11F` | age badges, positive accents |
| `--light-blue` | `#DDF0FB` | tags, secondary text on violet, soft backgrounds |

#### Neutral colors

| Token | Hex | Usage |
|---|---|---|
| `--white` | `#FFFFFF` | cards, form inputs, text on dark background |
| `--black` | `#000000` | technical text only |
| `--grey-100` | `#D0D0D0` | dividers |
| `--grey-500` | `#858585` | secondary copy |
| `--cl-h` | `#0F0F0F` | dark text when needed |
| `--cl-k` | `#F8F8F8` | light background |

#### Semantic aliases

| Purpose | Preferred token |
|---|---|
| Primary CTA background | `--yellow` |
| Primary heading | `--violet-500` |
| Body text | `--violet-300` |
| Secondary text | `--grey-500` |
| Dark section background | `--violet-500` or `--violet-100` |
| Success/accent | `--green` |
| Soft tag background | `--light-blue` |
| Divider | `--grey-100` |

Rules:

- primary CTA remains yellow;
- avoid large areas with only one purple tone and no contrast;
- use green as accent, not full-page background;
- red `--bg-f: #FF2F00` only for errors or rare warning states;
- add new colors only if existing tokens do not cover the task.

## 5. Typography scale

Current scale from `source/scss/general/_typography.scss`.

| Style | Size | Line-height | Weight | Usage |
|---|---:|---:|---:|---|
| `h1`, `.h1` | `60px` | `115%` | `700` | main hero headline |
| `h2`, `.h2` | `clamp(32px, 7.292vw, 56px)` | `118%` | `700` | section headings |
| `h3`, `.h3` | `46px` | `118%` | `700` | major cards and course blocks |
| `h4`, `.h4` | `28px` | `128%` | `600` | hero/section subheadings |
| `h5`, `.h5` | `20px` | `140%` | `700` | labels, form title, accordion |
| Body | `var(--body-font-size)` | `140%` | `500` | main text |
| Small | `12px` | `140%` | `500` | privacy text, helper copy |

Rules:

- only one semantic `h1` per page;
- sections use `h2`;
- cards and list items use `h3`/`h5` by density;
- avoid hero-scale typography in small cards;
- if heading comes from CMS, test 2-3 lines on mobile.

## 6. Spacing and layout

Base spacing patterns from current markup:

| Pattern | Value | Usage |
|---|---:|---|
| Section vertical padding | `clamp(40px, 7.813vw, 80px)` | standard sections |
| Hero padding | `60px 0` | first screen |
| Section content gap | `60px` | between heading and list |
| Card internal padding | `30px` | cards and trust bars |
| Form padding | `40px 30px` | violet lead form |
| Field gap | `18px` | form inputs |
| CTA group gap | `16px` | adjacent buttons |
| Small chip gap | `10px` | tags and controls |

Rules:

- sections should breathe, but not become landing-style hero on every viewport;
- do not nest cards inside decorative cards unless needed;
- course lists can alternate layout order like in `services-section`;
- two-column sections become single-column on mobile.

## 7. Shape and elevation

Current radii:

| Radius | Usage |
|---:|---|
| `12px` | buttons |
| `20px` | proof/trust bars |
| `30px` | lead form container |
| `40px` | large media cards, section panels, English cards |
| `46px-65px` | pill inputs, tags, age badges |

Rules:

- large friendly surfaces can use 30-40px radius;
- buttons remain 12px unless pill style is explicitly requested;
- inputs use pill radius;
- badges/tags use pill radius;
- use shadow carefully, e.g. trust bar: `0 0 25px rgba(37, 37, 37, 0.06)`.

## 8. Components

### 8.1. Button

Base classes:

```scss
.btn
.btn--yellow
.btn--violet
.btn--green
```

Base style:

```css
width: `100%` by default;
padding: `16px`;
display: flex;
align-items/justify-content: center;
gap: `6px`;
border-radius: `12px`;
font-weight: `700`;
line-height: `140%`.
```

Variants:

| Variant | Background | Text | Usage |
|---|---|---|---|
| `btn--yellow` | `--yellow` | `--violet-300` | primary CTA |
| `btn--violet` | `--violet-100` | `--white` | secondary brand CTA |
| `btn--green` | `--green` | `--white` | success/positive action |

Rules:

- primary action in commercial blocks uses yellow;
- secondary action may be violet or text/link style;
- buttons with icons should use existing SVG sprite icons;
- button text should stay short and action-oriented;
- disabled/loading states must not shift layout.

Required states:

- default;
- hover;
- focus-visible;
- active;
- disabled;
- loading.

### 8.2. Lead form

Base class:

```scss
.main-form
.main-form__input
.main-form__btn
.main-form__text
```

Structure:

- title;
- input stack;
- primary CTA button;
- privacy text.

Current style:

- form gap: `30px`;
- input height: `52px`;
- input padding: `0 16px`;
- input radius: `46px`;
- input background: white;
- privacy text: `12px`, light-blue on violet.

Rules:

- frontend validation is UX-only;
- server validation is mandatory;
- errors should be near fields;
- CRM failures must not expose technical details;
- privacy link must stay visible;
- hidden city/course/source/UTM fields are allowed but must be validated server-side.

### 8.3. Course card / service item

Pattern:

- large media block with violet background;
- slightly rotated age badge;
- optional icon/character;
- content column with title, tags, excerpt, CTA group.

Rules:

- age badge is high-visibility green pill;
- tags use light-blue pill style;
- excerpt uses secondary grey text;
- primary CTA first, secondary CTA second;
- cards must support missing image and long text.

### 8.4. English level card

Pattern:

- white card;
- 40px radius;
- centered content;
- age badge in top-right;
- image area;
- title + descriptive text.

Usage:

- English levels A0-A2/B1/B2;
- age-based language cards;
- compact course progression cards.

Rules:

- keep card content centered;
- age badge must not overlap important illustration details;
- image size should stay stable in slider.

### 8.5. Accordion / FAQ

Base class:

```scss
.accordion
.accordion__item
.accordion__btn
.accordion__btn-icon
```

Pattern:

- vertical list;
- 30px gap;
- bottom divider;
- active item uses violet-100 heading color;
- icon rotates on open.

Rules:

- FAQ text must be editable from CMS;
- question uses button, not link;
- button must expose expanded/collapsed state for accessibility;
- FAQ schema can include only visible FAQ content.

### 8.6. Trust bar

Pattern from hero:

- white/light surface;
- 20px radius;
- subtle shadow;
- horizontal list of proof points;
- icon + bold text.

Usage:

- years on market;
- license;
- rating;
- number of schools;
- number of cities;
- graduates.

Rules:

- proof numbers must come from CMS/options if expected to be editable;
- icons should be consistent size;
- on mobile, proof items should wrap or become scrollable cards.

### 8.7. Tags and badges

Types:

- course tag: light-blue pill;
- age badge: green pill with white text;
- small age label: light-blue pill;
- status/admin tag: use semantic colors carefully.

Rules:

- tags describe metadata, not actions;
- tag text should stay short;
- if tags wrap, spacing stays consistent.

### 8.8. Slider controls

Pattern:

- white pill controls on dark sections;
- icon-only or icon+text;
- 10px-20px padding;
- currentColor SVG fill.

Rules:

- use bundled Swiper only;
- do not add another carousel library;
- controls must be keyboard accessible;
- mobile swipe must work without hiding essential content.

## 9. Page templates

### 9.1. Homepage

Expected structure:

1. Hero with main offer and lead form.
2. Trust bar.
3. Course sections.
4. English section.
5. Advantages/trust content.
6. City selector/map.
7. CTA.
8. FAQ.

Rules:

- first viewport should clearly state what Logika is;
- form can appear in hero;
- proof points should be near top;
- next section should be hinted below hero on common viewport sizes.

### 9.2. City page

Expected structure:

- localized H1;
- local intro;
- branches/map;
- available courses;
- reviews or fallback reviews;
- FAQ;
- SEO text;
- lead form with city context.

Rules:

- city content must have real URL `/cities/{slug}/`;
- selected city in cookie/localStorage must not override canonical city page;
- noindex/review cities should not appear as fully indexed pages.

### 9.3. Course page

Expected structure:

- course hero;
- age and format;
- program/outcomes;
- projects/examples;
- available cities or online option;
- FAQ;
- CTA form.

Rules:

- course cards and course pages share source data;
- age range and format should be prominent;
- CTA must preserve course context.

### 9.4. Camp page

Expected structure:

- camp name;
- season/dates;
- city/format;
- program;
- gallery;
- CTA.

Rules:

- dates and availability are managed data;
- expired camps must have explicit state.

## 10. Content design

### 10.1. Voice

Use clear, direct language:

- describe benefit to child;
- reduce uncertainty for parents;
- keep CTA concrete;
- avoid vague hype.

Good:

```text
Leave a request and we will call you to pick a convenient time
```

Weak:

```text
Unlock an unlimited world of the future right now
```

### 10.2. CTA text

Preferred CTA patterns:

- `Try for free`;
- `Book a free lesson`;
- `Choose course`;
- `Select a course`;
- `Learn more`.

Rules:

- one primary CTA per block;
- button text should fit on mobile;
- CTA should match destination and action.

### 10.3. Empty states

Each dynamic block needs fallback:

- no local branches;
- no local reviews;
- no FAQ;
- no course image;
- no camp dates;
- CRM temporarily unavailable.

Fallbacks should be honest and helpful, not fabricated.

## 11. Accessibility

Minimum rules:

- interactive elements must be semantic buttons/links;
- keyboard focus must be visible;
- accordions need `aria-expanded`;
- sliders need accessible controls;
- images need meaningful `alt` when they carry information;
- decorative images may use empty alt;
- form fields need labels or accessible names;
- error messages should be linked to fields;
- color cannot be the only error/status signal.

Contrast rules:

- white text on violet backgrounds preferred;
- light-blue text on violet is acceptable for secondary copy if checked at small sizes;
- yellow CTA with violet text is primary high-contrast combination;
- grey secondary text remains readable on light backgrounds.

## 12. Responsive behavior

Breakpoints are implemented through project mixins. Design rules:

- desktop can use two-column hero/course layouts;
- tablet reduces header height and tightens spacing;
- mobile stacks content vertically;
- hero form should not disappear on mobile;
- sliders must preserve card dimensions;
- large badges must not overflow viewport;
- text should wrap within buttons and tags without breaking layout.

Mobile priorities:

1. main offer;
2. CTA/form;
3. age/course fit;
4. trust proof;
5. location/city selection.

## 13. WordPress implementation rules

When moving design into WordPress:

- keep CSS class names from current HTML where possible;
- convert repeated HTML blocks into `template-parts`;
- use ACF fields for editable text/images/CTA;
- use CPT for repeated entities;
- do not hardcode business content in PHP templates;
- do not create new design variants per page;
- expose only safe content controls to editors;
- use fallback states for missing CMS data.

Component mapping:

| Design component | WordPress implementation |
|---|---|
| Header | `header.php` + WP menu + city selector |
| Footer | `footer.php` + ACF Options |
| Hero | ACF Block or page template partial |
| Course card | `template-parts/cards/course-card.php` |
| English level card | `template-parts/cards/english-level-card.php` |
| FAQ accordion | `template-parts/sections/faq.php` + `faq_item` CPT |
| Lead form | `template-parts/forms/lead-form.php` + `logika-leads` |
| Trust bar | ACF repeater/options |
| City selector | CPT `city` + JS component |

## 14. Do and do not

Do:

- reuse existing tokens and classes;
- keep design bright and friendly;
- make CTA states obvious;
- test long CMS content;
- keep forms short;
- use real data relationships;
- preserve visual rhythm from source HTML.

Do not:

- introduce an unrelated second palette;
- replace the whole UI with generic WordPress theme styles;
- hardcode final marketing text in PHP;
- use a page builder for systematic city/course pages;
- add new slider/modal libraries without need;
- hide important content under animation;
- make every section a decorative card;
- create one-off components when existing patterns already work.

## 15. Quality checklist

Before approving a new page or component:

- uses existing color tokens;
- uses Montserrat;
- heading hierarchy is correct;
- CTA is clear and visible;
- form has labels/errors/privacy text;
- mobile layout is readable;
- long text does not overflow;
- empty state exists;
- image alt policy is defined;
- component can be fed from ACF/CPT;
- no CRM/API logic is embedded in UI;
- visual style matches current Logika sections.

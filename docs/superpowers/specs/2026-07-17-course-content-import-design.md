# Course Content Import Design

## Goal

Move the real educational copy from `/home/sbaikov/Documents/tilda-export` into editable ACF fields for WordPress `course` posts, make `/it-courses/` show those courses as cards, and render every course through the existing shared single-course template.

## Scope

- Include standalone educational Tilda pages with course content and exclude hackathons, event pages, `Copy of ...` pages, and duplicate campaign pages.
- Use the current `course` CPT and `group_logika_course` Local JSON field group.
- Populate hero copy, age range, short card copy, learning outcomes, lesson process, and module-by-module `course_program` content from Tilda.
- Populate the `/it-courses/` age-category relationships so cards link to the generated `/courses/{slug}/` routes.
- Keep the existing source-page DOM and visual styles; no per-course PHP templates.
- Do not invent missing copy or migrate Tilda images in this slice; existing template fallbacks remain active.

## Source classification

Use the detailed educational pages for Visual Programming, Game Design, Web Sites, Graphic Design, Python Start, Python Mastery, Python Advanced, Graphic Design 2 year, and Python Expert. Pages whose canonical alias starts with `hackathon`, including short Python Junior/Middle/Senior and Scratch Junior pages, are event pages and remain excluded despite course-like titles.

## Data flow

```text
Tilda HTML export
  -> versioned course content fixture
  -> idempotent WP-CLI seed
  -> course CPT + ACF fields
  -> age-category relationship cards
  -> single-course.php / source-pages/it-course.php
```

The stable identity is the Tilda page ID stored in `course_external_id`; rerunning the seed updates the same course instead of creating duplicates. Existing records are matched by that external ID first and by slug second.

## Rendering contract

- Course cards use title, age, short description, fallback image, and `get_permalink()`.
- The shared course page uses the current ACF-driven source markup.
- `course_program` renders module title, rich-text description, and topic list in the existing accordion.
- Empty optional sections stay hidden through the current fallback logic.
- All output continues through the existing WordPress escaping helpers.

## Acceptance criteria

- Every included source course has one published `course` post with a stable slug and no duplicate on a second seed run.
- Every included course has visible title, age, hero/short description, and program modules where Tilda provides them.
- `/it-courses/` contains real course cards grouped by the existing age sections, and each card's details link opens the matching course page.
- At least one course from each age group renders its own copy and module list.
- Existing unrelated dirty worktree changes remain untouched.

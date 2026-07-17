# Course Student Projects Section Design

## Goal

Show the approved student-project carousel directly after the course process section on every published course page, including `/courses/programming-start/`.

## Scope

- Reuse the homepage portfolio visual contract: standard and featured cards, optional video link and trial CTA.
- Keep the content per course in the existing `group_logika_course` ACF Local JSON group.
- Render the same section through the fixed course source markup used by public course routes.
- Keep empty project rows and empty portfolio sections hidden.

## Data model

`course_projects` is a repeater with the existing generic title, description and image fields. It gains stable fields needed by the approved layout: card variant, student name and age, course label, topic, student image, video URL, CTA label and CTA URL. The current field names remain unchanged.

## Verification

- A focused PHP smoke test proves the Local JSON exposes every portfolio field and the rendered course contains both standard and featured cards.
- The course route is checked in local DDEV at desktop and mobile widths.

# Project: Logika School

Date: 2026-07-10  
Project: Logika School  
Source: existing HTML/CSS/JS source in `source/`

## Brief description

Logika School is the website of one of the largest coding and English schools for children and teenagers in Ukraine. The project must present training directions, courses, IT camps, cities and branches, explain the advantages of the school and guide users toward a lead form for a free trial lesson or consultation.

The current repository contains ready-made HTML/CSS/JS. The target state is a managed WordPress site: a custom theme `logika-theme` uses the existing markup as the UI base, while content, SEO/GEO/AEO fields, courses, cities, FAQ, reviews and CTAs are managed through WordPress, ACF Pro and the custom plugin `logika-core`.

## Project goals

- Transform the static Logika markup into a maintainable CMS-driven site without losing visual design.
- Enable editors to manage content without a developer: texts, images, courses, cities, branches, FAQ, reviews, CTAs and SEO fields.
- Support scaling across many cities, branches, courses and localized landing pages.
- Collect leads reliably: keep source of lead, selected course/city, UTM tags and status of delivery to external systems.
- Make SEO, GEO and AEO part of the data model instead of manual template edits.
- Keep the architecture simple and reliable: WordPress as CMS, ACF Pro as editable field layer, `logika-theme` as visual theme, `logika-core` as business logic layer.

## Audience

Primary user audience:

- parents of children aged 7–17 who choose coding, English language courses or IT camps;
- children and teens who need understandable learning options and a motivation-friendly experience;
- users from different Ukrainian cities who need to find the nearest school or choose online learning.

Internal audience:

- marketers and SEO specialists who manage landing pages, metadata, local blocks, FAQ and conversion CTAs;
- content editors who update pages without touching code;
- managers who receive and process enrollment requests;
- developers maintaining the theme, plugins, imports, forms and integrations.

## Context for AI

When working on this project, keep in mind:

- this is not just a set of static HTML pages, but a source of UI for a managed WordPress site;
- business content should not stay hardcoded in templates if it is expected to be editable by admins;
- repeatable entities must be modeled as data: cities, branches, courses, camps, reviews, FAQ, media and forms;
- architecture decisions must be simple, idempotent and easy to support over time;
- project priorities are reliability, simplicity, backward compatibility and performance.

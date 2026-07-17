# Features: Logika School

Date: 2026-07-10
Source: `project-scope.md`
Implementation stack: WordPress + ACF Pro + custom theme + `logika-core`

## 1. Project goal

Logika School site is a marketing site and lead generation system, not an internal learning platform.

Main objective:

> Migrate the site from Tilda to a manageable WordPress CMS where managers can edit cities, branches, courses, camps, reviews, FAQ, news/offers, SEO fields and forms without a developer.

The site is effectively a landing site with many city landing templates. City pages should not be created manually as 130+ separate pages. They must be generated through one template and admin data.

## 2. Feature: marketing site and lead capture

Description:

The site is for acquisition and lead collection. Lessons, calls, LMS, student portal and internal educational logic are out of scope for now.

Requirements:

- forms for lead capture must exist on site.
- leads must be sent to CRM.
- until CRM is finalized, leads should be stored in WordPress so they are not lost.
- forms must pass page context: city, course, URL, UTM, referrer.
- key pages should have clear CTA logic.
- phone fields should preselect the country code from safe hosting/CDN geo headers with an admin fallback.

Acceptance criteria:

- user can submit a lead from homepage, city page, course page and contacts page.
- lead is saved locally.
- lead includes city when city is selected.
- lead includes source page.
- lead is not lost if CRM returns error.

## 3. Feature: Tilda migration

Description:

Current site is on Tilda. Need to migrate content, URLs and SEO logic to WordPress.

Requirements:

- get Tilda access.
- collect current page list and old URLs.
- define which pages are migrated one-to-one, which are reworked, which are removed.
- create redirect map from old URLs to new WordPress URLs.
- migrate texts, images, SEO title/description, forms and important blocks.

Acceptance criteria:

- old URL map is prepared.
- old important URLs have corresponding new URLs.
- 301 redirects are configured before launch.
- no critical 404s on old important URLs.

## 4. Feature: dynamic city pages

Description:

City is a central entity. Must quickly create and edit city pages from admin.

URL:

```text
/cities/{city-slug}/
```

Examples:

```text
/cities/kyiv/
/cities/lviv/
/cities/odesa/
```

Requirements:

- city created as WordPress CPT `city`.
- city page generated through single `single-city.php`.
- manager can add city in admin.
- manager can edit title, slug, H1, intro, SEO title, description, SEO text, local blocks, indexing status.
- city can link branches, courses, camps, reviews, FAQ, news/offers.
- if no local data exists, template should use fallback content.

Acceptance criteria:

- manager can create new city without developer.
- new city gets URL `/cities/{slug}/`.
- city page renders data from ACF/CPT.
- repeated blocks are not hardcoded in HTML.
- no separate HTML/PHP page for each city is required.

## 5. Feature: city selection for localized content

![[Pasted image 20260624142520.png]]

Description:

User selects city, and site shows content specific to this city.

Core logic:

> When user selects a locality, the site should show local news, camps, branches, addresses, map, reviews, offers and other city blocks.

City selection must behave identically from any entry point:

- user selects from navbar;
- user selects via interactive map;
- user opens direct URL `/cities/{city-slug}/`;
- user returns later and city is restored from browser memory.

Main flow:

1. User opens site.
2. Navbar shows city selection option.
3. User selects city, for example `Kyiv`.
4. Site stores selected city.
5. Site shows Kyiv local content:
   - news;
   - offers;
   - camps;
   - courses;
   - branches;
   - addresses;
   - map;
   - reviews;
   - FAQ;
   - CTA and form with selected city.
6. If user navigates to other pages, selected city remains active.
7. If user opens a form, hidden fields include selected city.

Alternative flow: direct city URL

1. User opens `/cities/kyiv/`.
2. WordPress resolves city by URL.
3. City context becomes `Kyiv`.
4. Navbar shows selected city.
5. All local blocks use city `Kyiv`.

Alternative flow: city choice on generic page

1. User is on home or generic page.
2. User selects city from navbar.
3. Site either:
   - redirects to `/cities/{slug}/`;
   - or updates local blocks on current page.
4. For SEO, preferred to keep city content on dedicated URL `/cities/{slug}/`.

What depends on selected city

| Block | Behavior |
|---|---|
| Navbar | shows selected city |
| Hero/CTA | can adapt text by city |
| News/offers | local city materials first, then fallback |
| Camps | show camps available in selected city |
| Courses | show city-specific courses or fallback to global |
| Branches | show selected city addresses |
| Google Maps | show exact addresses/branches for selected city |
| Reviews | local reviews first, then global fallback |
| FAQ | local FAQ, then fallback |
| Lead form | sends `city_id`, `city_name`, `city_slug` |
| SEO | city SEO content lives on `/cities/{slug}/` |

City memory:

- selected city should be stored in browser.

Recommended approach:

- store `city_slug` in `localStorage` or cookie;
- also store `city_id` or resolve by slug;
- on open, verify saved city;
- if user is on city URL, URL has priority over stored city;
- update saved value on new city selection.

City context source priority:

1. city URL `/cities/{slug}/`.
2. explicit user choice in navbar/map.
3. saved city from cookie/localStorage.
4. default city or neutral no-city state.

Fallback logic:

If selected city has no local content:

- no local news -> show global news;
- no local camps -> show global/online camps or hide block;
- no reviews -> show global reviews;
- no branch -> show online scenario or contact CTA;
- no local FAQ -> show global FAQ;
- no local image -> use fallback image from ACF Options.

Acceptance criteria:

- city choice from navbar is preserved;
- city choice from map is preserved;
- direct URL `/cities/{slug}/` sets city context;
- local blocks update based on selected city;
- lead form receives city;
- no data break on missing local items;
- city SEO page available at dedicated URL;
- selected city does not break cache and canonical.

## 6. Feature: interactive country/region map

![[Pasted image 20260624142755.png]]

Description:

Site should have interactive map. Clicking a region shows localities in that region. After city selection, city context is set and local content is shown.

Main flow:

1. User sees interactive map.
2. User clicks region.
3. Site shows list of localities in that region.
4. User selects city.
5. Site saves selected city.
6. Site shows local content for that city.
7. Google Maps with exact addresses/branches appears below.

Map requirements:

- map must be interactive;
- region states:
  - default;
  - hover;
  - active/selected;
  - disabled/no cities.
- clicking a region should show available cities list.
- city list comes from WordPress, not hardcoded HTML.
- city links to region;
- city selection updates navbar.
- city selection updates map.
- city selection updates local blocks.

Google Map below map selection:

Must show:

- exact branch address;
- several addresses if multiple branches;
- marker per branch;
- city/region name;
- link to Google Maps.

If no branch in city:

- show online/contact CTA; or
- hide map; or
- show city-level map without exact branch if agreed.

Data model:

Minimal fields:

#### Region

- region name;
- slug;
- map code/path id;
- active flag;
- sort order.

#### City

- name;
- slug;
- region;
- coordinates;
- selected city label;
- city page URL;
- status: active/inactive/review/noindex/index.

#### Branch

- city relation;
- address;
- lat/lng;
- phone;
- schedule;
- Google Maps URL;
- active flag.

Acceptance criteria:

- clicking region opens city list.
- list contains only cities in selected region.
- clicking city sets city context.
- navbar shows selected city.
- map shows selected city addresses.
- local news/offers/camps/branches adapt to city.
- map works on desktop and mobile.
- disabled regions are not shown as clickable.

## 7. Feature: local news and offers

Description:

News/offers can be global or linked to a specific city.

Requirements:

- content editor can add news/offers from admin.
- news/offer can be linked to city.
- if city selected, city content is shown first.
- if no local content, global content is shown.
- media-centre search finds published articles by their native WordPress searchable content and keeps the selected-city filter.
- while the visitor types, the search field shows a compact dropdown of matching article links.
- content should have URL and SEO fields.

Acceptance criteria:

- news can be linked to city.
- city page shows local news.
- generic pages show local news after city selection.
- no duplicates in fallback.

## 8. Feature: local camps

Description:

Camps should respect selected city.

Requirements:

- camp created as CPT `camp`.
- camp can be linked to one or many cities.
- city page shows camps for selected city.
- on city selection, generic pages can show camps for that city.
- if no local camps, show fallback.

Acceptance criteria:

- manager can add camp from admin.
- manager can choose cities where camp is available.
- after city selection camp block updates.
- camp lead form passes selected city.

## 9. Feature: branches and addresses

Description:

Branches are a separate entity linked to city.

Requirements:

- manager can add/edit branches.
- branch has city, address, phone, coordinates, schedule.
- city page shows only branches for current city.
- Google Maps shows exact branch addresses.

Acceptance criteria:

- branch can be created from admin.
- branch can be assigned to city.
- selected city branches display on city page.
- Google Maps shows branch markers.

## 10. Feature: courses

Description:

Courses must be editable from admin and reused across pages.

Requirements:

- course created as CPT `course`.
- course has age, format, direction, description, program, CTA, FAQ, SEO fields.
- course can be available in all cities or selected cities only.
- city page can show courses available for city.

Acceptance criteria:

- manager can create/edit course.
- course can be linked to city.
- course page has dedicated URL.
- course cards on city page are pulled from CPT.

## 11. Feature: reviews

Description:

Reviews must be editable and linked to city/course.

Requirements:

- review created as CPT `review`.
- review can be linked to city and/or course.
- city page shows local reviews first.
- if no local reviews, global reviews appear.

Acceptance criteria:

- manager can add review.
- review can be assigned to city.
- local reviews appear after city selection.
- fallback does not render empty block.

## 12. Feature: FAQ

Description:

FAQ should be editable and used for SEO/AEO.

Requirements:

- FAQ can be created as CPT `faq_item`.
- FAQ may be global or tied to city/course.
- FAQ should render on city page, course page and global FAQ page.
- FAQ contributes to `FAQPage` schema if questions are actually visible.

Acceptance criteria:

- manager can add FAQ.
- FAQ can be tied to city or course.
- page shows relevant questions.
- schema includes only visible FAQ.

## 13. Feature: SEO/GEO/AEO management from admin

Description:

Manager/SEO specialist should change SEO/GEO/AEO content from admin without developer.

Requirements:

- all SEO fields editable via ACF Pro.
- city requires indexing status.
- cities should have SEO title, description, H1, intro, SEO text, FAQ, local blocks.
- courses require SEO title, description, H1, FAQ, Course schema inputs.
- articles need answer-first summary, author/expert, related content.
- managers can create a complete article from one fixed editor template: cover, author, body, sidebar promotion, selected courses, related articles, CTA and inline FAQ.
- article table of contents is generated from visible H2/H3 headings; only published related content may appear publicly.
- schema should be built from visible content.

Acceptance criteria:

- SEO title editable in admin.
- description editable in admin.
- H1 editable in admin.
- FAQ editable in admin.
- index status of city affects sitemap/noindex.
- content changes do not require deployment.

## 14. Feature: manager admin experience

Description:

Admin should be simple for content managers.

What manager should be able to do:

- add/edit cities.
- add/edit branches.
- add/edit courses.
- add/edit reviews.
- add/edit FAQ.
- add/edit news/offers.
- add/edit camps.
- edit SEO fields.
- edit global site settings.

Requirements:

- fields should have clear labels.
- complex fields should include helper text.
- required fields should be validated.
- content manager should not have access to critical theme/plugin settings.

Acceptance criteria:

- manager can create city without developer.
- manager can assign branch to city.
- manager can edit city SEO text.
- manager can add local news.
- manager can add camp and tie it to city.

## 15. Feature: localization

Description:

Site starts in Ukrainian only.

Requirements:

- site interface and content in Ukrainian.
- URL/slugs pre-aligned.
- if multilingual support is added later, this is a separate phase.

Acceptance criteria:
- fields and templates should not be overcomplicated for multilinguality prematurely (Ukranian only).

## 16. Feature: hosting, staging and access

Description:

Hosting and staging are not finalized yet, but controlled process needed for development.

Requirements:

- set up local environment.
- prepare staging.
- get access to Tilda, domain, hosting, CRM, analytics.
- run production only after staging verification.

Acceptance criteria:

- staging exists before production launch.
- backup exists before cutover.
- forms, map, city selector and redirects are verified pre-production.

## 17. MVP feature priority

### Must have

1. WordPress + ACF Pro + custom theme.
2. city import/creation.
3. city pages `/cities/{slug}/`.
4. city selection from navbar.
5. interactive country/region map and city selection.
6. Google Maps with addresses.
7. local news/offers by selected city.
8. local camps by selected city.
9. branches and addresses by selected city.
10. lead forms with city context.
11. SEO fields and indexing status.
12. manager admin for content.

### Should have

1. Fallback content for cities without local data.
2. local reviews.
3. FAQ tied to city/course.
4. sitemap/noindex logic.
5. Tilda redirect map.

### Could have after MVP

1. automatic booking into time slots.
2. student portal.
3. automatic Zoom/Google Meet setup.
4. complex CRM lead-routing logic.
5. multilingual support.

## Marketing page ACF

- [x] Add page-specific ACF groups for About, IT Courses, English Courses, FAQ and Media Center.
- [x] Seed the first editable course and FAQ content without duplicating existing entities.

## 18. Open questions

- which CRM is used?
- is there ready city/branch table?
- which cities are priority for early indexing?
- should all cities be `index`, or some in `review/noindex`?
- final hosting/staging?
- which Tilda pages are migrated one-to-one?
- which pages will be reworked?
- which fields are mandatory for city publication?
- what to show if city has no branch?
- what to show if city has no local camps?
- should city selection on homepage navigate immediately to `/cities/{slug}/` or update blocks without navigation?

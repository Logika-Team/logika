# Map admin data and online form

## Goal

Make the school map use the published WordPress city and branch records, keep
the map unselected until the visitor chooses a region, and show the existing
hero lead form when the visitor switches to online learning.

## Scope

- Keep the existing map markup, styles and SVG asset.
- Load cities from `GET /wp-json/logika/v1/cities` and group them by the
  returned region.
- Add a read-only city-branch endpoint. It returns only published, active
  branches belonging to the requested published city, with title, address,
  coordinates and Google Maps URL.
- Do not select a region after the SVG loads. The selector shows a controlled
  prompt until a region is selected.
- Highlight only the selected region. Hover remains a transient affordance.
- Replace hardcoded Dnipro cities and school addresses with REST data.
- Move the existing hero form node into the map's online slot on the online
  mode click; return it to its original position on offline mode click.
- When a city is chosen, populate the existing city details with its active
  branches and map coordinates. Show the configured no-branch fallback when
  the city has none.

## Data flow

```text
WordPress city CPT + region taxonomy
  -> GET /logika/v1/cities
  -> map region buttons
  -> city selection
  -> GET /logika/v1/cities/{id}/branches
  -> branch list and map iframe
```

The online mode does not create a new lead form. It moves the already rendered
hero form, so the established `data-logika-lead-form` validation and lead REST
submission continue unchanged.

## Failure and empty states

- If cities fail to load, show the current safe map error state.
- A region without cities is not selectable.
- A city without active branches shows the configured WordPress fallback text
  and its city coordinates when available.
- A city without usable coordinates does not render a misleading map frame.

## Verification

- Add a PHP contract test for the city-branch response and its active/published
  filtering.
- Add a map script contract test for REST city loading, neutral initial state
  and reuse of the hero form node.
- Run the focused PHP tests, project build and a browser smoke test against
  DDEV for region selection, online/offline form movement and city details.

## Non-goals

- No changes to city or branch ACF field definitions.
- No new form, form handler or JavaScript library.
- No unrelated changes in the current dirty worktree.

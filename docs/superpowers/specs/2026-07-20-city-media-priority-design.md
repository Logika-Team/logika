# City Media Priority Design

## Goal

Show the newest published article tagged for the selected city first, then other city articles, then common articles by date across the Media Center, `/blog/`, and homepage media blocks.

## Ordering

1. With no selected city, show published common articles by newest date. The configured featured Media Center article may remain first.
2. With a selected city, show published articles tagged for that city by newest date, followed by published common articles by newest date.
3. City articles for another city remain hidden. Common articles fill every limited section when there are not enough city articles.
4. `/blog/` requests all matching articles in that same order; its date filter may then sort the already-visible set by selected date direction.

## Architecture

`Logika\Core\MediaApi` is the source of truth for the public Media Center and `/blog/` order. Its featured-post promotion applies only without a selected city. Homepage media selection follows the same city-first ordering instead of retaining an editor-selected order when a city is active.

## Scope and checks

- Preserve current city visibility rules, category filters, search, and the seven-card Media Center limit.
- Preserve the editor-selected featured post when no city is selected.
- Add regression coverage for city-first API ordering, common fallback, all-article ordering, and homepage city output.

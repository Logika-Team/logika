# Homepage course chip links

## Scope

The existing `home_programming_courses` ACF repeater owns each chip label and URL. No new fields are needed.

## Links

- Python Expert → `/courses/python-expert/`
- Python Advanced → `/courses/python-advanced/`
- Основи фронтенд розробки → `/courses/frontend/`
- Комп'ютерна грамотність для дорослих → `/courses/computer-literacy-14/`

## Rendering

Render every chip with its ACF URL, escaped as a URL. Keep the existing fallback only when an editor leaves a URL empty.

## Verification

Test the four rendered links and their editable ACF row values.

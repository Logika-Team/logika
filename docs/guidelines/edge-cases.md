# Edge Cases: Logika School

Date: 2026-07-10
Project: Logika School
Stack: WordPress + ACF Pro + `logika-theme` + `logika-core` + `logika-leads`

## 1. Purpose

This document defines edge cases that the project must handle during implementation, QA, staging and production launch.

It is not a feature backlog. It is a risk checklist for the existing project scope:

- WordPress marketing site;
- city landing pages;
- city selector and interactive map;
- courses and camps;
- branches and addresses;
- FAQ, reviews, news/offers;
- lead forms;
- local lead storage;
- CRM integration;
- SEO/GEO/AEO;
- deployment and production operations.

Rule:

> Edge cases are considered handled only when the expected behavior is implemented or explicitly documented as deferred with owner and risk.

## 2. Global content and language edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Public UI text appears in Russian or English | Site violates project language requirement | Public site content must be Ukrainian only |
| Admin labels are mixed-language | Editor confusion | Admin labels should be consistent and understandable to content managers |
| CMS content is empty but template assumes text exists | Broken layout or empty headings | Render fallback, hide optional block, or show controlled empty state |
| Very long Ukrainian heading | Mobile overflow | Text wraps safely without overlap |
| City/course name contains apostrophe, dash or special Ukrainian letters | Broken URLs, bad escaping | Slug normalized safely; output escaped by context |
| Editor inserts rich text with unsupported HTML | XSS or broken layout | Sanitize with allowlist; never render raw unsafe HTML |
| Editor removes required image | Broken card layout | Use fallback image or render image-free state |
| Same content block is edited in multiple places | Inconsistent content | Shared/repeated content should live in CPT/options, not duplicated manually |

## 3. WordPress and CMS edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| ACF Pro disabled | Fatal errors | Theme/plugin fails gracefully in admin-facing warning where possible |
| ACF field group not synced | Missing content fields | Admin can sync Local JSON; templates use safe field access |
| Field name changed without migration | Data disappears | Field names must not be renamed without migration |
| Editor deletes related entity | Broken relationship output | Template checks relation existence and skips invalid item |
| Related entity is draft/private | Private content leaks | Public queries include only publish/active items |
| Manager creates duplicate city slug | Broken route/canonical | WordPress uniqueness plus import dedupe prevents duplicate canonical pages |
| CPT rewrite rules not flushed after activation | 404 on CPT pages | Flush rewrite rules only on plugin activation/deactivation |
| Theme changed but `logika-core` remains | Business data still available | CPT/ACF/business logic remains in plugin, not theme |
| Manager lacks permission to edit needed content | Content blocked | Roles/capabilities defined for manager workflow |
| Manager has access to plugin/theme settings | Accidental breakage | Restrict critical technical settings |

## 4. City model and city page edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| City is published without branches | Empty address/map block | Show online/contact CTA or configured fallback |
| City has branches but all inactive | Misleading local page | Treat as no active branches and show fallback |
| City has no local courses | Empty course section | Show global/online courses or configured fallback |
| City has no local camps | Empty camp section | Hide block or show configured fallback |
| City has no local reviews | Empty trust block | Show global approved reviews fallback |
| City has no local FAQ | Missing SEO/AEO content | Show global FAQ fallback if relevant |
| City has `review` status | Premature indexing | Page should be noindex/excluded from sitemap until approved |
| City has `noindex` status | SEO leakage | Add noindex and exclude from sitemap |
| City has invalid coordinates | Broken map | Do not render marker; log/report data issue |
| City URL uses stale slug | 404 or duplicate page | Redirect old slug if slug changes after publication |
| City selected in cookie conflicts with `/cities/{slug}/` URL | Wrong local content/canonical | URL city has priority over stored city |
| City page opened with no saved city | Missing UI state | Current URL city sets city context |
| City entity deleted but saved in browser | Broken selector state | Clear saved city and return neutral/default state |
| City has local content but is inactive | Private content exposure | Do not expose inactive city in public lists/selectors |

## 5. City selector and interactive map edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Region has no active cities | Dead click target | Region appears disabled/non-clickable |
| Region click returns empty city list | Confusing UI | Show controlled empty state |
| City list request fails | Broken selector | Show safe retry/error state without blocking page |
| User selects city from navbar on homepage | Ambiguous navigation | Follow agreed behavior: redirect to city page or update blocks |
| User selects city from map | Context not synced | Navbar, form hidden fields and local blocks update |
| User changes city after filling form | Wrong lead context | Form context updates before submit |
| Cached page contains old embedded city state | Wrong user content | City state should be resolved client-side or through non-cacheable endpoint |
| LocalStorage unavailable | City selection cannot persist | Site still works for current session/page URL |
| Multiple tabs with different selected cities | Confusing user state | URL city remains source of truth; stored city is best-effort |
| Map JS fails to load | Critical content inaccessible | City selection remains available through navbar/list fallback |

## 6. Branch and Google Maps edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Branch has address but no coordinates | Marker missing | Render address list; omit marker |
| Branch has coordinates but no address | Bad UX | Do not render public branch until address exists |
| Branch coordinates are invalid | Broken map viewport | Skip marker and log/report issue |
| Google Maps API fails | Empty map area | Show address list and external Google Maps links |
| Multiple branches in one city | Marker overlap | Render all active branches and readable list |
| Branch phone differs from global phone | Wrong contact info | Use branch phone when present, global fallback otherwise |
| Branch schedule missing | Empty schedule | Hide schedule row instead of showing blank labels |
| Duplicate branch address in import | Duplicate markers | Dedupe by `external_id` or `city_slug + address_hash` |

## 7. Course edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Course has no image | Broken card | Use fallback image or image-free card state |
| Course has no age range | Misleading card | Hide age badge or show configured neutral state |
| Course has age_min greater than age_max | Invalid data | Prevent save or mark validation error |
| Course has no related city | Ambiguous availability | Treat as global/online only if business rule allows |
| Course linked to inactive/noindex city | Bad local listing | Exclude inactive city relation from public local blocks |
| Course FAQ empty | Empty accordion | Hide FAQ section or show relevant global FAQ |
| Course page has empty schema fields | Invalid structured data | Omit empty schema properties |
| Same course imported twice | Duplicate course cards | Dedupe by `external_id` or `course_slug + locale` |
| User submits course form from city page | Lost context | Lead includes both course and city where available |

## 8. Camp edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Camp has expired dates | Users submit outdated offer | Show explicit expired state or hide CTA |
| Camp has no dates yet | Misleading availability | Show neutral "dates to be confirmed" copy only if approved |
| Camp is inactive | Public stale content | Exclude from public lists |
| Camp has no related city | Wrong local listing | Show only as global/online if configured |
| Camp has no gallery | Broken media block | Hide gallery block |
| Camp linked to inactive city | Wrong city page content | Do not show on inactive city context |
| Duplicate camp import | Duplicate seasonal offer | Dedupe by `external_id` or `slug + start_date + city_ids` |
| Camp form submitted from selected city | Lost context | Lead includes camp and city context |

## 9. Reviews edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Review is not approved | Trust content leak | Do not show publicly |
| Review has no author name | Low-quality output | Hide author label or require author before approval |
| Review has no photo | Broken card | Use text-only layout |
| Review linked to deleted city/course | Broken relation | Skip invalid relation |
| No local reviews | Empty block | Use approved global fallback reviews |
| Same review imported twice | Duplicate carousel item | Dedupe by `review_external_id` or source hash |
| Review text is very long | Slider/card overflow | Clamp or allow controlled wrapping |

## 10. FAQ and schema edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| FAQ answer is empty | Bad UX/schema | Do not render item |
| FAQ question duplicates another visible question | Repetitive content | Deduplicate visible output by normalized question |
| FAQ linked to inactive city/course | Wrong page content | Exclude from public output |
| FAQ visible in UI but missing from schema | SEO inconsistency | Include visible active FAQ in schema |
| FAQ in schema but hidden from UI | Structured data violation | Do not include hidden FAQ in schema |
| Accordion JS fails | Hidden content inaccessible | FAQ content remains accessible in HTML |
| FAQ order not set | Unstable page output | Use explicit sort or fallback deterministic ordering |

## 11. Posts, news and offers edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| City has local news and global fallback | Duplicate content | Show local first and avoid duplicates |
| Post linked to inactive city | Private/local mismatch | Do not show as local public content |
| Offer expired but still published | Misleading promotion | Hide or mark expired according to editorial rule |
| Post has no featured image | Broken card | Use fallback image or text-only card |
| Standard post used for promotion | Ambiguous label | Use `post_is_offer` field when promotion behavior is needed |
| Answer-first summary missing | AEO weaker but valid | Page still renders; schema only uses available data |

## 12. Lead form validation edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Phone is missing | Bad lead | Return field-level validation error |
| Phone has invalid format | Bad CRM data | Normalize or reject server-side |
| Phone mask passes frontend but invalid server-side | Bad CRM data | Server validation wins |
| Name too short or empty | Low-quality lead | Return field-level validation error |
| Child age outside allowed range | Wrong course routing | Reject or omit based on form rules |
| Consent not accepted | Legal risk | Reject submit |
| Unknown `form_id` | Unsafe payload | Reject with `unknown_form` |
| Unknown city/course slug | Wrong context | Reject context or save lead without invalid relation according to API contract |
| Hidden fields tampered | Data integrity risk | Server validates all IDs/slugs |
| UTM fields too long | Storage/log issues | Truncate or reject by max length |
| Source URL from another domain | Spoofed source | Reject or sanitize according to API rules |
| User double-clicks submit | Duplicate lead | Idempotency prevents duplicate local/CRM lead |
| Browser offline during submit | Lead not sent | Show safe error and allow retry |
| JS disabled | Form unavailable | Provide graceful fallback if selected form layer supports it |

## 13. Lead storage and deduplication edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| CRM unavailable after local save | Lead loss | Keep lead locally as `failed` or `retrying` |
| DB insert succeeds, CRM send fails | Partial failure | Store CRM error and allow retry |
| DB insert fails before CRM send | Lost lead | Do not call CRM; show safe failure |
| Same idempotency key repeated | Duplicate lead | Return duplicate/safe response without new CRM send |
| Same phone submits different course | False duplicate | Do not over-dedupe if business context differs |
| Same phone submits same form repeatedly | Spam/duplicate | Rate limit and dedupe according to policy |
| Retry after accepted CRM status | Duplicate CRM record | Do not send again |
| Retry after timeout | Unclear CRM state | Use idempotency key and attempt log |
| Lead contains PII in logs | Privacy/security risk | Mask phone/name in logs |
| Lead export requested by unauthorized user | Data leak | Require capability and audit event |
| Old leads exceed retention period | Privacy risk | Anonymize/delete according to retention policy |

## 14. CRM and webhook edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| CRM returns HTTP 200 with error body | False success | Validate explicit CRM contract |
| CRM returns 500 | Lost lead | Mark failed/retrying and keep local lead |
| CRM times out | Unknown state | Store timeout attempt and retry with idempotency key |
| CRM rejects payload | Permanent failure | Mark rejected and expose safe admin reason |
| CRM token expired | All sends fail | Log safe error; do not expose token |
| CRM sandbox used in production by mistake | Lost production leads | Environment config must be verified before launch |
| Production CRM used on staging | Polluted CRM | Staging uses sandbox/dry-run |
| CRM callback missing signature | Spoof risk | Reject callback |
| CRM callback replayed | Duplicate status changes | Validate timestamp/signature when supported |
| Unknown CRM callback event | Unexpected mutation | Log and safely ignore |
| CRM callback for unknown lead | Data mismatch | Log and reject/ignore safely |

## 15. REST API and AJAX edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Wrong HTTP method | Unsafe endpoint behavior | Return `invalid_method` or 405-style response |
| Invalid JSON body | Server error | Return `invalid_json` |
| Missing `Content-Type` | Ambiguous parsing | Reject or handle explicitly |
| Missing nonce/form token | CSRF/spam | Return `missing_token` |
| Expired token due to cached page | Broken forms | Refresh via form token endpoint |
| Rate limit exceeded | Spam | Return 429 safe response |
| Admin endpoint called by public user | Data leak | Return permission denied |
| Endpoint returns stack trace | Security leak | Return safe envelope only |
| Endpoint returns secrets | Critical leak | Never include tokens, auth headers or raw CRM secrets |
| Cached POST response | Wrong lead response | Exclude mutating endpoints from all caches |

## 16. Security edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| XSS payload in form name/comment | XSS in admin | Sanitize input and escape admin output |
| XSS payload in ACF WYSIWYG | XSS public site | Use `wp_kses_post` or approved allowlist |
| SQL-like input in filters | SQL injection | Use WordPress APIs or `$wpdb->prepare` |
| CSRF against admin retry action | Unauthorized CRM resend | Capability + nonce required |
| Public upload field added accidentally | File upload risk | Not allowed without separate architecture |
| SVG uploaded unsanitized | XSS risk | Disallow or sanitize SVG |
| Debug enabled in production | Sensitive leak | `WP_DEBUG_DISPLAY` disabled |
| Secret committed to repo | Credential leak | Rotate secret and remove from Git history as incident |
| Public endpoint exposes private ACF fields | Data leak | Response allowlist only |

## 17. SEO, redirects and indexing edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Old Tilda URL has no redirect | Lost traffic/404 | Add 301 redirect to closest new URL |
| Redirect chain | SEO/performance loss | Redirect directly to canonical URL |
| Redirect loop | Page inaccessible | Validate redirect map before launch |
| City `noindex` included in sitemap | SEO inconsistency | Exclude from sitemap |
| Staging indexable | Duplicate/unsafe indexing | Staging noindex/blocked |
| Production accidentally noindex | SEO loss | Verify robots/meta before launch |
| Canonical points to wrong city | Duplicate/wrong SEO | Canonical uses current city URL |
| Cookie-selected city changes canonical | SEO inconsistency | Cookie never overrides canonical URL |
| Empty schema properties | Invalid JSON-LD | Omit empty properties |
| Schema references hidden FAQ/review | Structured data violation | Schema only from visible content |
| SEO title missing | Weak snippet | Generate fallback from template |
| Manual SEO override empty string | Bad metadata | Treat empty override as missing |

## 18. Caching and performance edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Page cache stores personalized city state | Wrong city shown | City-specific canonical pages only; JS state for generic pages |
| CDN caches lead endpoint | Broken/duplicated forms | Exclude lead endpoints from cache |
| CDN caches token endpoint too long | Expired form tokens | Exclude or short TTL token endpoint |
| Cache not invalidated after city edit | Stale content | Clear relevant page/object cache |
| Large city list loaded on every page | Performance issue | Cache public city list and invalidate on city changes |
| Too many meta queries for city page | Slow page | Use explicit relations and indexes where needed |
| Large images from CMS | Slow page | Use WordPress image sizes and optimization |
| Slider initializes before content exists | JS errors | Guard initialization and handle empty lists |
| AOS/animations hide content if JS fails | Inaccessible content | Content visible without animation JS |

## 19. Frontend and layout edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Mobile header height differs from desktop | Overlap | Respect design-system header heights |
| Button text too long in Ukrainian | Overflow | Wrap safely or use shorter CMS copy |
| Card count is 1 instead of expected grid | Awkward layout | Layout supports 1, 2, 3+ items |
| Slider has fewer items than slides per view | Empty slides | Disable loop or adjust slider config |
| Image missing alt text | Accessibility/SEO issue | Decorative empty alt or meaningful alt where needed |
| Accordion opened by keyboard | Accessibility | Button semantics and `aria-expanded` |
| Focus state hidden | Accessibility | Visible focus for interactive elements |
| Form error only indicated by color | Accessibility | Text error message linked to field |
| Map or slider traps keyboard focus | Accessibility | Keyboard navigation remains possible |

## 20. Import and migration edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Import run interrupted | Partial data | Re-run safely by idempotent keys |
| CSV contains duplicate rows | Duplicate entities | Dedupe and log skipped/updated |
| Required import field missing | Bad entity | Skip row and report error |
| City slug changes in source | Duplicate city | Match by `external_id` first |
| Branch address format changes | Duplicate branch | Use normalized address hash |
| Import creates relation to missing city | Broken relation | Skip relation and report |
| Migration runs twice | DB errors/data loss | Migrations are idempotent |
| Migration partially fails | Unknown schema | Stop, log, keep schema version unchanged |
| Destructive migration without backup | Data loss | Not allowed |
| ACF JSON differs from DB | Field mismatch | Sync deliberately and review changes |

## 21. Deployment and production edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| Production deployed without backup | No rollback | Block launch |
| Restore process untested | False safety | Test restore on staging before launch |
| Wrong environment secrets | Broken CRM/site | Verify per-environment config |
| Staging CRM config copied to production | Leads not delivered | Verify production CRM before launch |
| Production debug display on | Sensitive leak | Disable before launch |
| Plugin inactive after deploy | Fatal/missing features | Smoke check required plugins |
| ACF not activated | Missing fields | Smoke check admin and public key pages |
| Permalinks not flushed | 404 on CPT pages | Flush on activation/deploy procedure |
| Rollback after CRM sends | Duplicate risk | Check retry/idempotency before resend |
| Launch monitoring absent | Slow incident response | Assign first-24h owner |

## 22. Browser and compatibility edge cases

| Edge case | Risk | Expected behavior |
|---|---|---|
| IE11 support still required | JS/CSS break | Confirm support before removing compatibility |
| Firefox ESR rendering differs | Layout issue | Respect current browserslist until changed |
| Safari mobile input behavior | Form UX issue | Test phone/input states on mobile |
| Browser blocks third-party scripts | Analytics/maps issue | Core content and forms still work |
| User has ad blocker | Tracking missing | Lead flow must not depend on analytics scripts |
| User has reduced motion enabled | Accessibility | Avoid mandatory animation for content visibility |

## 23. Testing coverage checklist

Minimum scenarios that should be represented in tests or manual QA:

- [ ] city with full local data;
- [ ] city without branches;
- [ ] city without reviews;
- [ ] city without FAQ;
- [ ] noindex city;
- [ ] course with missing image;
- [ ] course with FAQ and schema;
- [ ] camp expired state;
- [ ] duplicate city import;
- [ ] duplicate branch import;
- [ ] duplicate lead submit;
- [ ] CRM success;
- [ ] CRM timeout;
- [ ] CRM rejection;
- [ ] invalid phone form error;
- [ ] missing consent form error;
- [ ] city selector from navbar;
- [ ] city selector from map;
- [ ] direct city URL context;
- [ ] cached page with refreshed form token;
- [ ] sitemap excludes noindex content;
- [ ] redirect from old Tilda URL;
- [ ] mobile layout for long Ukrainian text.

## 24. Edge case handling rule for implementation

When implementing a feature:

1. Check this document for relevant edge cases.
2. Add missing edge cases before implementation if new risk appears.
3. Implement the simplest behavior that preserves data integrity.
4. Add tests for high-risk behavior.
5. Document deferred edge cases explicitly.

Do not hide edge cases inside comments or assumptions. If a scenario can affect leads, SEO, privacy, or content integrity, it belongs in this document.

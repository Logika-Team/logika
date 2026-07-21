## Канонічний WordPress release

- `.worktrees/wordpress` є єдиним канонічним джерелом staging release.
- Один release містить увесь `wordpress/wp-content`; staging перемикає одну символьну лінку `wp-content` на перевірену версію.
- `wp-content/uploads` є постійним серверним каталогом поза release і не потрапляє до архіву.
- Release містить увесь версіонований `wp-content`; WordPress core, uploads і
  серверна конфігурація лишаються на середовищі.
- Зміни з інших checkout переносяться до канонічного worktree окремо та не
  копіюються неявно під час deploy.

## Dynamic article pages

- `logika-core/acf-json/group_logika_post.json` owns the versioned ACF schema for standard post editorial fields and `group_logika_global.json` owns shared media-centre settings.
- `logika-core` owns the non-public `article_author` CPT, its Local JSON photo field and the `POST /logika/v1/articles/{id}/view` counter; view totals are technical post meta, not editor-managed ACF data.
- `logika-theme/src/ArticlePage.php` owns rendering of the fixed `/media-center/{slug}/` article layout; it reads WordPress post fields and ACF data but never defines business fields.
- Related courses and posts are stored as relationships and rendered from published source entities. Article FAQ is intentionally local to its post.

## Shared ACF sections

- `logika-core/acf-json` is the only field-schema source; every Image/Gallery returns an attachment ID and shows a `medium` preview.
- `logika-core/src/ImageOverrides.php` enhances every ACF Image field (any group, repeater row or options page) with a replace/reset panel. Default resolution: homepage `_override` fields fall back to a sibling base field (`LEGACY_FIELDS`); `field_review_photo` uses its own captured `review_original_photo` meta; every other field uses `_logika_default_image` post meta, snapshotted on that field's first admin save via `GET /wp-json/logika/v1/image-defaults`. If no default is known, the reset button renders disabled rather than clearing the field.
- `logika-theme/src/Entities.php` is the public visibility gate for reusable Course, FAQ and Review entities. Draft courses, inactive FAQ and unapproved reviews must not be queried around it.
- Reusable sections live in `logika-theme/template-parts/sections/` and receive explicit `$args`; they do not infer data from the current page.
- Header/footer navigation comes from the three registered WordPress menu locations. Brand media, contacts, social links, privacy URL, partners and certificates belong to Global Options.
- Fixed marketing pages always render their original `source-pages/*` DOM through `Logika_Theme_Fixed_Page`; ACF changes leaf content and repeated items inside those existing classes. There is no alternate structured-mode renderer.
- Legal pages share `group_logika_legal` and `templates/page-legal.php`; document section order is the ACF Repeater order and licence image order is the Gallery order.
- Course and Camp single templates keep the original source-page DOM and inject the queried CPT ACF payload into it; dynamic renderers must clone the source card shells instead of inventing alternate markup.
- Camp archive content belongs to the `camp_archive` ACF Options post ID. Camp cards still belong to the `camp` CPT and are filtered by `publish` plus `camp_is_active`.
- `logika-core/src/AdminUi.php` owns the native quick-create link for all ACF Course selectors.
- `logika-theme/src/CityPage.php` overlays City Home fields on formatted Home values for the current city request; `front-page.php` selects this renderer from `logika_city`.
- `logika-theme/src/GenericPage.php` dispatches the controlled builder layouts used only by `templates/page-builder.php`.
- `logika-theme/templates/page-blog.php` shares labels with the Media Center ACF group instead of defining a second options payload.

## Dynamic article pages

- `logika-core/acf-json/group_logika_post.json` owns the versioned ACF schema for standard post editorial fields and `group_logika_global.json` owns shared media-centre settings.
- `logika-core` owns the non-public `article_author` CPT, its Local JSON photo field and the `POST /logika/v1/articles/{id}/view` counter; view totals are technical post meta, not editor-managed ACF data.
- `logika-theme/src/ArticlePage.php` owns rendering of the fixed `/media-center/{slug}/` article layout; it reads WordPress post fields and ACF data but never defines business fields.
- Related courses and posts are stored as relationships and rendered from published source entities. Article FAQ is intentionally local to its post.

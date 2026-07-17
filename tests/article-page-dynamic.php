<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$article = get_page_by_path( 'dynamic-article-test', OBJECT, 'post' );
$article_id = $article ? (int) $article->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-article-test', 'post_title' => 'Динамічна стаття', 'post_status' => 'publish' ) );
wp_update_post( array( 'ID' => $article_id, 'post_title' => 'Динамічна стаття', 'post_excerpt' => 'Короткий опис пов’язаної статті.', 'post_content' => '<p>Вступ.</p><h2>Один розділ</h2><p>Текст.</p><h3>Один розділ</h3>' ) );

if ( ! post_type_exists( 'article_author' ) ) {
	fwrite( STDERR, "Article author post type is missing.\n" );
	exit( 1 );
}

$author = get_page_by_path( 'dynamic-article-author', OBJECT, 'article_author' );
$author_id = $author ? (int) $author->ID : (int) wp_insert_post( array( 'post_type' => 'article_author', 'post_name' => 'dynamic-article-author', 'post_title' => 'Тестова авторка', 'post_status' => 'publish' ) );
$photo_id = (int) ( get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids' ) )[0] ?? 0 );

$related = get_page_by_path( 'dynamic-related-test', OBJECT, 'post' );
$related_id = $related ? (int) $related->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-related-test', 'post_title' => 'Опублікована пов’язана стаття', 'post_status' => 'publish' ) );
$draft = get_page_by_path( 'dynamic-draft-test', OBJECT, 'post' );
$draft_id = $draft ? (int) $draft->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-draft-test', 'post_title' => 'Чернетка не для виводу', 'post_status' => 'draft' ) );

register_shutdown_function(
	static function () use ( $article_id, $related_id, $draft_id, $author_id ): void {
		foreach ( array( $article_id, $related_id, $draft_id, $author_id ) as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}
);

update_field( 'post_answer_first_summary', '<script>bad()</script>Безпечний вступ', $article_id );
update_field( 'article_author', $author_id, $article_id );
update_field( 'article_author_photo', $photo_id, $author_id );
update_field( 'article_related_posts', array( $related_id, $draft_id ), $article_id );
update_field( 'article_sidebar_enabled', 0, $article_id );
update_field( 'article_cta_enabled', 1, $article_id );
update_field( 'article_cta_title', 'Підберемо курс', $article_id );
update_field( 'article_cta_button_label', 'Надіслати заявку', $article_id );
update_field( 'article_faq_enabled', 1, $article_id );
update_field( 'article_faq_items', array( array( 'question' => 'Чи безпечна відповідь?', 'answer' => '<p><strong>Так.</strong><script>bad()</script></p>' ) ), $article_id );

$socials = static function ( $value, $post_id, array $field ) {
	return 'global_social_links' === $field['name'] ? array( array( 'label' => 'Instagram', 'url' => 'https://instagram.com/logika' ), array( 'label' => 'Telegram', 'url' => 'https://t.me/logika' ), array( 'label' => 'YouTube', 'url' => 'https://youtube.com/@logika' ) ) : $value;
};
add_filter( 'acf/pre_load_value', $socials, 10, 3 );

$output = Logika_Theme_Article_Page::render( $article_id );
remove_filter( 'acf/pre_load_value', $socials, 10 );
$default_social_output = Logika_Theme_Article_Page::render( $article_id );
$errors = array();

foreach ( array( 'Динамічна стаття', 'Тестова авторка', 'article-section__author-photo', 'https://instagram.com/logika', 'https://t.me/logika', 'https://youtube.com/@logika', 'Instagram-filled', 'Facebook-filed', 'Youtube-filled', 'Telegram-filled', 'data-article-view-count', '&lt;script&gt;bad()', 'Опублікована пов’язана стаття', 'data-logika-lead-form', 'Надіслати заявку', 'cta-section__top-bg', 'faq-section__left-bg', 'Чи безпечна відповідь?', '<strong>Так.</strong>' ) as $expected ) {
	if ( ! str_contains( $output, $expected ) ) {
		$errors[] = "Missing article output: {$expected}";
	}
}

foreach ( array( 'https://www.instagram.com/logika_it_school/', 'https://www.facebook.com/logika.it.school/', 'https://www.youtube.com/channel/UCFIBb_OZ1TPuhcjZUhVbifg', 'https://t.me/share/url?' ) as $expected ) {
	if ( ! str_contains( $default_social_output, $expected ) ) {
		$errors[] = "Missing default social link: {$expected}";
	}
}

if ( str_contains( $default_social_output, 'Threads-filled' ) ) {
	$errors[] = 'Threads icon must not be rendered.';
}

preg_match_all( '#<h[23][^>]* id="([^"]+)"#', $output, $heading_ids );
if ( 2 !== count( $heading_ids[1] ) || $heading_ids[1][0] === $heading_ids[1][1] ) {
	$errors[] = 'Article headings do not have unique table-of-contents anchors.';
}

foreach ( array( 'Чернетка не для виводу', '<script>bad()</script>' ) as $unexpected ) {
	if ( str_contains( $output, $unexpected ) ) {
		$errors[] = "Unsafe or private article output: {$unexpected}";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Dynamic article output uses safe WordPress and ACF data.\n";

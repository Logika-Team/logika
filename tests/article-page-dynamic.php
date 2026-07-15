<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$article = get_page_by_path( 'dynamic-article-test', OBJECT, 'post' );
$article_id = $article ? (int) $article->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-article-test', 'post_title' => 'Динамічна стаття', 'post_status' => 'publish' ) );
wp_update_post( array( 'ID' => $article_id, 'post_title' => 'Динамічна стаття', 'post_excerpt' => 'Короткий опис пов’язаної статті.', 'post_content' => '<p>Вступ.</p><h2>Один розділ</h2><p>Текст.</p><h3>Один розділ</h3>' ) );

$related = get_page_by_path( 'dynamic-related-test', OBJECT, 'post' );
$related_id = $related ? (int) $related->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-related-test', 'post_title' => 'Опублікована пов’язана стаття', 'post_status' => 'publish' ) );
$draft = get_page_by_path( 'dynamic-draft-test', OBJECT, 'post' );
$draft_id = $draft ? (int) $draft->ID : (int) wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'dynamic-draft-test', 'post_title' => 'Чернетка не для виводу', 'post_status' => 'draft' ) );

update_field( 'post_answer_first_summary', '<script>bad()</script>Безпечний вступ', $article_id );
update_field( 'article_related_posts', array( $related_id, $draft_id ), $article_id );
update_field( 'article_sidebar_enabled', 0, $article_id );
update_field( 'article_cta_enabled', 1, $article_id );
update_field( 'article_cta_title', 'Підберемо курс', $article_id );
update_field( 'article_cta_button_label', 'Надіслати заявку', $article_id );
update_field( 'article_faq_enabled', 1, $article_id );
update_field( 'article_faq_items', array( array( 'question' => 'Чи безпечна відповідь?', 'answer' => '<p><strong>Так.</strong><script>bad()</script></p>' ) ), $article_id );

$output = Logika_Theme_Article_Page::render( $article_id );
$errors = array();

foreach ( array( 'Динамічна стаття', '&lt;script&gt;bad()', 'Опублікована пов’язана стаття', 'data-logika-lead-form', 'Надіслати заявку', 'cta-section__top-bg', 'faq-section__left-bg', 'Чи безпечна відповідь?', '<strong>Так.</strong>' ) as $expected ) {
	if ( ! str_contains( $output, $expected ) ) {
		$errors[] = "Missing article output: {$expected}";
	}
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

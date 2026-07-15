<?php

require_once ABSPATH . 'wp-admin/includes/image.php';

$slots = array(
	array( 'Дитина в hero', 'boy-character.svg' ),
	array( 'Персонаж біля форми', 'logika-character.svg' ),
	array( 'Іконка: календар', 'banner-bar/icon-calendar-check.svg' ),
	array( 'Іконка: сертифікат', 'banner-bar/icon-document-certificate.svg' ),
	array( 'Іконка: рейтинг', 'banner-bar/icon-rating-star.svg' ),
	array( 'Іконка: школи', 'banner-bar/icon-outline_school.svg' ),
	array( 'Іконка: міста', 'banner-bar/icon-map-location.svg' ),
	array( 'Іконка: випускники', 'banner-bar/icon-tabler-school.svg' ),
	array( 'Картка послуг', 'services/service1.png' ),
	array( 'Ілюстрація послуг', 'services/service1.svg' ),
	array( 'Рівень англійської A0', 'english-courses/A0.svg' ),
	array( 'Рівень англійської A1', 'english-courses/A1.svg' ),
	array( 'Рівень англійської A2', 'english-courses/A2.svg' ),
	array( 'Рівень англійської B1', 'english-courses/B1.svg' ),
	array( 'Рівень англійської B2', 'english-courses/B2.svg' ),
	array( 'Трансформація до', 'transformation/before.png' ),
	array( 'Трансформація після', 'transformation/after.png' ),
	array( 'Онбординг 1', 'onbording/onbording1.svg' ),
	array( 'Онбординг 2', 'onbording/onbording2.svg' ),
	array( 'Онбординг 3', 'onbording/onbording3.svg' ),
	array( 'Сертифікат', 'certificates/certificate.png' ),
	array( 'Партнер Think', 'Partners/think.png' ),
	array( 'Партнер 1+1', 'Partners/1+1.png' ),
	array( 'Партнер Free', 'Partners/Free.png' ),
	array( 'Партнер Club', 'Partners/club.png' ),
	array( 'Партнер Ed', 'Partners/ed.png' ),
	array( 'Партнер Basis', 'Partners/basis.png' ),
	array( 'Партнер Fond', 'Partners/fond.png' ),
	array( 'Партнер Mriya', 'Partners/mriya.png' ),
);

$page_id = (int) get_option( 'page_on_front' );
if ( $page_id <= 0 ) {
	fwrite( STDERR, "Homepage is not configured.\n" );
	exit( 1 );
}

$asset_dir = trailingslashit( get_template_directory() ) . 'assets/img';
$upload = wp_upload_dir();
$ids = array();

$copy_image = static function ( string $source, string $target ): void {
	$extension = strtolower( pathinfo( $source, PATHINFO_EXTENSION ) );

	wp_mkdir_p( dirname( $target ) );

	if ( 'svg' === $extension ) {
		$command = sprintf( 'convert -background none -resize 800x800 %s %s 2>&1', escapeshellarg( $source ), escapeshellarg( $target ) );
		exec( $command, $output, $code );
		if ( 0 !== $code || ! is_readable( $target ) ) {
			throw new RuntimeException( "Cannot create preview for {$source}: " . implode( ' ', $output ) );
		}

		return;
	}

	if ( ! copy( $source, $target ) ) {
		throw new RuntimeException( "Cannot copy asset: {$source}" );
	}
};

foreach ( $slots as $index => $slot ) {
	$position = $index + 1;
	$title = sprintf( 'Головна %02d - %s', $position, $slot[0] );
	$source = trailingslashit( $asset_dir ) . $slot[1];

	if ( ! is_readable( $source ) ) {
		fwrite( STDERR, "Missing asset: {$source}\n" );
		exit( 1 );
	}

	$existing = get_posts(
		array(
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'post_status'      => 'inherit',
			'post_type'        => 'attachment',
			'posts_per_page'   => 1,
			'suppress_filters' => true,
			'title'            => $title,
		)
	);

	if ( $existing ) {
		$id     = (int) $existing[0];
		$target = (string) get_attached_file( $id );

		if ( ! is_readable( $target ) ) {
			try {
				$copy_image( $source, $target );
			} catch ( RuntimeException $exception ) {
				fwrite( STDERR, $exception->getMessage() . "\n" );
				exit( 1 );
			}

			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $target ) );
		}

		$ids[] = $id;
		continue;
	}

	$target = trailingslashit( $upload['path'] ) . sanitize_file_name( sprintf( 'logika-home-%02d-%s.png', $position, pathinfo( $slot[1], PATHINFO_FILENAME ) ) );

	try {
		$copy_image( $source, $target );
	} catch ( RuntimeException $exception ) {
		fwrite( STDERR, $exception->getMessage() . "\n" );
		exit( 1 );
	}

	$id = wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_mime_type' => 'image/png',
			'post_status'    => 'inherit',
		),
		$target,
		$page_id
	);

	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}

	wp_update_attachment_metadata( (int) $id, wp_generate_attachment_metadata( (int) $id, $target ) );
	$ids[] = (int) $id;
}

update_field( 'field_home_image_gallery', $ids, $page_id );

echo 'Homepage gallery images: ' . implode( ',', $ids ) . PHP_EOL;

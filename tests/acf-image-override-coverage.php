<?php

declare(strict_types=1);

$root   = dirname( __DIR__ );
$errors = array();

$script = (string) file_get_contents( $root . '/wordpress/wp-content/plugins/logika-core/assets/js/image-overrides.js' );
foreach ( array(
	"field.get('type') !== 'image'",
	'logika-image-override-reset',
	".toggleClass('disabled', !resolveDefault(field))",
) as $needle ) {
	if ( ! str_contains( $script, $needle ) ) {
		$errors[] = "Image override script no longer contains \"{$needle}\".";
	}
}

$dir     = $root . '/wordpress/wp-content/plugins/logika-core/acf-json';
$count   = 0;
foreach ( glob( $dir . '/*.json' ) as $file ) {
	$group = json_decode( (string) file_get_contents( $file ), true, 512, JSON_THROW_ON_ERROR );
	$visit = static function ( array $fields ) use ( &$visit, &$count ): void {
		foreach ( $fields as $field ) {
			if ( 'image' === ( $field['type'] ?? '' ) ) {
				++$count;
			}
			$visit( (array) ( $field['sub_fields'] ?? array() ) );
		}
	};
	$visit( (array) ( $group['fields'] ?? array() ) );
}

if ( $count < 100 ) {
	$errors[] = "Expected at least 100 ACF image fields across acf-json groups, found {$count}.";
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Image override controls target every ACF image field ({$count} found), not a whitelist.\n";

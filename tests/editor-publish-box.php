<?php

declare(strict_types=1);

require dirname( __DIR__ ) . '/wordpress/wp-load.php';

use Logika\Core\AdminUi;

$errors = array();

// Button links pointing at an in-page anchor or an internal path must save without a
// "Value must be a valid URL" error, while genuine junk stays rejected.
$field = acf_get_field( 'field_course_project_cta_url' );
if ( ! is_array( $field ) || 'url' !== ( $field['type'] ?? '' ) ) {
	$errors[] = 'Expected field_course_project_cta_url to exist as a url field.';
}

foreach ( array( '#lead-form', '/camps/', 'https://logika.ua/', 'tel:+380000000000', '' ) as $value ) {
	$valid = acf_validate_value( $value, $field, 'acf[field_course_project_cta_url]' );
	if ( true !== $valid ) {
		$errors[] = sprintf( 'Expected "%s" to pass url validation, got: %s', $value, is_string( $valid ) ? $valid : 'false' );
	}
}

if ( true === acf_validate_value( 'лише текст', $field, 'acf[field_course_project_cta_url]' ) ) {
	$errors[] = 'Expected a non-URL, non-anchor value to still be rejected.';
}

// The input must not be type="url", or the browser blocks the form before ACF is reached.
ob_start();
acf_render_field( acf_prepare_field( $field ) );
$html = (string) ob_get_clean();
if ( false !== strpos( $html, 'type="url"' ) ) {
	$errors[] = 'Expected the link input to render as a plain text input, got: ' . $html;
}
if ( false === strpos( $html, 'type="text"' ) ) {
	$errors[] = 'Expected a text input to be rendered, got: ' . $html;
}

// The Publish box must come first in the side column, whatever order the editor dragged
// the boxes into previously.
$order = AdminUi::publishBoxFirst(
	array(
		'side' => 'acf-group_logika_course,submitdiv,postimagediv',
		'normal' => 'slugdiv',
	)
);
if ( 'submitdiv,acf-group_logika_course,postimagediv' !== $order['side'] ) {
	$errors[] = 'Expected submitdiv to be moved to the front of the side column, got: ' . $order['side'];
}
if ( 'slugdiv' !== $order['normal'] ) {
	$errors[] = 'Expected the other contexts to stay untouched.';
}

// Field groups must not render above the side column, or Publish ends up below the whole form.
foreach ( glob( dirname( __DIR__ ) . '/wordpress/wp-content/plugins/logika-core/acf-json/*.json' ) as $path ) {
	$group = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $group ) || 'group_logika_post' === ( $group['key'] ?? '' ) ) {
		continue; // Core posts keep the editor, where after-title placement still makes sense.
	}
	if ( 'acf_after_title' === ( $group['position'] ?? '' ) ) {
		$errors[] = 'Field group renders before the Publish box: ' . basename( $path );
	}
}

if ( $errors ) {
	echo "FAIL\n" . implode( "\n", $errors ) . "\n";
	exit( 1 );
}

echo "OK: editor publish box\n";

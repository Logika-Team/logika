<?php

declare(strict_types=1);

$data   = wp_parse_args( $args ?? array(), array( 'items' => null, 'context' => 0 ) );
$markup = Logika_Theme_Source_Markup::renderReviewsSection( is_array( $data['items'] ) ? $data['items'] : null, $data['context'] );
if ( '' === $markup ) {
	return;
}
echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

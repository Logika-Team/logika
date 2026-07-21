<?php

declare(strict_types=1);

namespace Logika\Core;

use DOMAttr;
use DOMDocument;
use DOMElement;

/**
 * WordPress blocks image/svg+xml uploads by default. We allow them for users who may
 * already upload files, but only after stripping everything that makes an SVG executable
 * (scripts, event handlers, external references), so an editor account cannot turn the
 * media library into a stored-XSS vector.
 */
final class SvgUploads {
	private const MIME = 'image/svg+xml';

	/** Elements that can execute code or pull in remote content. */
	private const FORBIDDEN_ELEMENTS = array( 'script', 'foreignobject', 'iframe', 'embed', 'object', 'handler', 'audio', 'video', 'set', 'animate' );

	/** Attributes that can execute code (`on*` handlers are matched separately). */
	private const FORBIDDEN_ATTRIBUTES = array( 'xlink:script', 'formaction', 'seed', 'ping' );

	/** URL-bearing attributes: only same-document (`#id`) and data:image refs survive. */
	private const URL_ATTRIBUTES = array( 'href', 'xlink:href', 'src', 'action', 'formaction', 'from', 'to', 'values', 'attributename', 'begin' );

	public static function register(): void {
		add_filter( 'upload_mimes', array( self::class, 'allowMime' ) );
		add_filter( 'wp_check_filetype_and_ext', array( self::class, 'checkFiletype' ), 10, 4 );
		add_filter( 'wp_handle_upload_prefilter', array( self::class, 'sanitizeUpload' ) );
		add_filter( 'wp_generate_attachment_metadata', array( self::class, 'addDimensions' ), 10, 2 );
		add_action( 'admin_head', array( self::class, 'renderAdminStyles' ) );
	}

	/**
	 * @param array<string, string> $mimes
	 * @return array<string, string>
	 */
	public static function allowMime( array $mimes ): array {
		if ( ! current_user_can( 'upload_files' ) ) {
			return $mimes;
		}

		$mimes['svg'] = self::MIME;
		$mimes['svgz'] = self::MIME;

		return $mimes;
	}

	/**
	 * wp_check_filetype_and_ext() runs finfo against the file and blanks out ext/type when the
	 * detected mime disagrees with the extension. SVG is plain XML, so finfo commonly reports
	 * `image/svg`, `text/xml`, `text/html` or `text/plain` — restore the extension in that case.
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function checkFiletype( array $data, string $file, string $filename, $mimes ): array {
		$ext = strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
			return $data;
		}

		$data['ext'] = $ext;
		$data['type'] = self::MIME;

		return $data;
	}

	/**
	 * @param array<string, mixed> $file
	 * @return array<string, mixed>
	 */
	public static function sanitizeUpload( array $file ): array {
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		if ( 'svg' !== strtolower( (string) pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			// .svgz is gzip-compressed and cannot be sanitized in place; reject it rather than
			// letting an unchecked payload through.
			if ( 'svgz' === strtolower( (string) pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
				$file['error'] = __( 'Формат .svgz не підтримується — завантажте звичайний .svg.', 'logika-core' );
			}
			return $file;
		}

		$tmp = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		if ( '' === $tmp || ! is_readable( $tmp ) ) {
			return $file;
		}

		$markup = (string) file_get_contents( $tmp );
		$clean = self::sanitizeMarkup( $markup );
		if ( null === $clean ) {
			$file['error'] = __( 'Не вдалося обробити цей SVG — файл пошкоджений або містить небезпечний вміст.', 'logika-core' );
			return $file;
		}

		file_put_contents( $tmp, $clean );
		$file['size'] = strlen( $clean );

		return $file;
	}

	/**
	 * Returns the sanitized SVG markup, or null when the file is not parseable as an SVG.
	 */
	public static function sanitizeMarkup( string $markup ): ?string {
		if ( '' === trim( $markup ) ) {
			return null;
		}

		// Strip the doctype outright: it is the entry point for XXE / billion-laughs payloads.
		// The internal subset (`[ … ]`) is matched first, because the entity declarations inside
		// it contain `>` characters of their own.
		$markup = preg_replace( '/<!DOCTYPE[^>\[]*\[.*?\]\s*>/is', '', $markup ) ?? $markup;
		$markup = preg_replace( '/<!DOCTYPE[^>]*>/is', '', $markup ) ?? $markup;

		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument();
		$document->preserveWhiteSpace = false;
		$loaded = $document->loadXML( $markup, LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $document->documentElement instanceof DOMElement ) {
			return null;
		}
		if ( 'svg' !== strtolower( $document->documentElement->localName ) ) {
			return null;
		}

		foreach ( iterator_to_array( $document->childNodes ) as $node ) {
			if ( XML_PI_NODE === $node->nodeType || XML_DOCUMENT_TYPE_NODE === $node->nodeType ) {
				$document->removeChild( $node );
			}
		}

		self::scrubNode( $document->documentElement );

		$output = $document->saveXML( $document->documentElement );

		return is_string( $output ) ? $output : null;
	}

	private static function scrubNode( DOMElement $element ): void {
		foreach ( iterator_to_array( $element->childNodes ) as $child ) {
			if ( $child instanceof DOMElement ) {
				if ( in_array( strtolower( $child->localName ), self::FORBIDDEN_ELEMENTS, true ) ) {
					$element->removeChild( $child );
					continue;
				}
				self::scrubNode( $child );
				continue;
			}

			if ( XML_COMMENT_NODE === $child->nodeType || XML_PI_NODE === $child->nodeType ) {
				$element->removeChild( $child );
			}
		}

		foreach ( iterator_to_array( $element->attributes ) as $attribute ) {
			if ( $attribute instanceof DOMAttr && ! self::isAttributeAllowed( $attribute ) ) {
				$element->removeAttributeNode( $attribute );
			}
		}
	}

	private static function isAttributeAllowed( DOMAttr $attribute ): bool {
		$name = strtolower( $attribute->nodeName );
		$value = $attribute->value;

		if ( str_starts_with( $name, 'on' ) || in_array( $name, self::FORBIDDEN_ATTRIBUTES, true ) ) {
			return false;
		}

		// `style` can carry url(javascript:…) / behaviour expressions.
		if ( 'style' === $name && preg_match( '/(javascript|expression|url\s*\(\s*[\'"]?\s*(?!#|data:image\/))/i', $value ) ) {
			return false;
		}

		if ( in_array( $name, self::URL_ATTRIBUTES, true ) || str_ends_with( $name, ':href' ) ) {
			$trimmed = ltrim( $value );
			if ( '' === $trimmed || str_starts_with( $trimmed, '#' ) ) {
				return true;
			}
			return (bool) preg_match( '#^data:image/(png|jpe?g|gif|webp);base64,#i', $trimmed );
		}

		return true;
	}

	/**
	 * SVGs have no intrinsic pixel size, so WordPress stores empty metadata and the media
	 * library renders a broken thumbnail. Fill width/height from the root attributes or viewBox.
	 *
	 * @param array<string, mixed> $metadata
	 * @return array<string, mixed>
	 */
	public static function addDimensions( array $metadata, int $attachment_id ): array {
		if ( self::MIME !== get_post_mime_type( $attachment_id ) ) {
			return $metadata;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return $metadata;
		}

		$size = self::readDimensions( (string) file_get_contents( $file ) );
		if ( ! $size ) {
			return $metadata;
		}

		$upload_dir = wp_get_upload_dir();
		$metadata['width'] = $size[0];
		$metadata['height'] = $size[1];
		$metadata['file'] = ltrim( str_replace( $upload_dir['basedir'], '', $file ), '/' );
		$metadata['sizes'] = array();

		return $metadata;
	}

	/**
	 * @return array{0: int, 1: int}|null
	 */
	public static function readDimensions( string $markup ): ?array {
		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument();
		$loaded = $document->loadXML( $markup, LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $document->documentElement instanceof DOMElement ) {
			return null;
		}

		$root = $document->documentElement;
		$width = (float) $root->getAttribute( 'width' );
		$height = (float) $root->getAttribute( 'height' );

		if ( $width <= 0 || $height <= 0 ) {
			$box = preg_split( '/[\s,]+/', trim( $root->getAttribute( 'viewBox' ) ) );
			if ( ! is_array( $box ) || 4 !== count( $box ) ) {
				return null;
			}
			$width = (float) $box[2];
			$height = (float) $box[3];
		}

		if ( $width <= 0 || $height <= 0 ) {
			return null;
		}

		return array( (int) round( $width ), (int) round( $height ) );
	}

	/**
	 * Without an explicit size the media grid renders SVG thumbnails at their intrinsic size,
	 * which overflows the tile.
	 */
	public static function renderAdminStyles(): void {
		echo '<style>.media-icon img[src$=".svg"],img[src$=".svg"].attachment-post-thumbnail,.attachment .thumbnail img[src$=".svg"]{width:100%;height:auto;}</style>';
	}
}

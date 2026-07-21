<?php

declare(strict_types=1);

namespace Logika\Core;

use WP_CLI;

final class WebpUploads {
	private const META_KEY = '_logika_webp';
	private const MIME_TYPES = array( 'image/png', 'image/jpeg' );
	private const MAX_SOURCE_BYTES = 8 * 1024 * 1024;
	private const UNSUPPORTED_NOTICE_OPTION = 'logika_webp_unsupported';

	public static function register(): void {
		add_filter( 'wp_generate_attachment_metadata', array( self::class, 'generateVariants' ), 20, 2 );
		add_action( 'delete_attachment', array( self::class, 'deleteVariants' ) );
		add_action( 'admin_notices', array( self::class, 'renderUnsupportedNotice' ) );
		add_filter( 'wp_get_attachment_image_src', array( self::class, 'preferWebpImageSrc' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( self::class, 'preferWebpImageAttributes' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'logika webp backfill', array( self::class, 'cliBackfill' ) );
		}
	}

	/**
	 * @param array<string, mixed> $metadata
	 * @return array<string, mixed>
	 */
	public static function generateVariants( array $metadata, int $attachment_id ): array {
		self::convertPathsFor( $attachment_id, self::pathsFromMetadata( $attachment_id, $metadata ), false );
		return $metadata;
	}

	/**
	 * Converts an already-uploaded attachment's original + registered sizes to webp.
	 * Used by the `wp logika webp backfill` CLI command to catch up existing uploads.
	 * Returns the number of webp files newly created.
	 */
	public static function backfillAttachment( int $attachment_id, bool $force = false ): int {
		$metadata = wp_get_attachment_metadata( $attachment_id );
		$paths = self::pathsFromMetadata( $attachment_id, is_array( $metadata ) ? $metadata : array() );
		return self::convertPathsFor( $attachment_id, $paths, $force );
	}

	/**
	 * @param array<string, mixed> $assoc_args
	 */
	public static function cliBackfill( array $args, array $assoc_args ): void {
		$batch = isset( $assoc_args['batch'] ) ? max( 1, (int) $assoc_args['batch'] ) : 50;
		$force = isset( $assoc_args['force'] );
		$dry_run = isset( $assoc_args['dry-run'] );

		$paged = 1;
		$scanned = 0;
		$created = 0;

		do {
			$ids = get_posts(
				array(
					'post_type' => 'attachment',
					'post_status' => 'inherit',
					'post_mime_type' => self::MIME_TYPES,
					'fields' => 'ids',
					'posts_per_page' => $batch,
					'paged' => $paged,
					'orderby' => 'ID',
					'order' => 'ASC',
				)
			);

			foreach ( $ids as $id ) {
				$scanned++;
				if ( ! $dry_run ) {
					$created += self::backfillAttachment( (int) $id, $force );
				}
			}

			if ( $ids ) {
				WP_CLI::log( sprintf( 'Processed %d attachment(s)…', $scanned ) );
			}
			$paged++;
		} while ( count( $ids ) === $batch );

		WP_CLI::success(
			sprintf(
				'%s: scanned %d attachment(s), created %d webp file(s).',
				$dry_run ? 'Dry run' : 'Done',
				$scanned,
				$created
			)
		);
	}

	/**
	 * @param array<string, mixed> $metadata
	 * @return array<int, string>
	 */
	private static function pathsFromMetadata( int $attachment_id, array $metadata ): array {
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) || ! in_array( get_post_mime_type( $attachment_id ), self::MIME_TYPES, true ) ) {
			return array();
		}

		$paths = array( $file );
		$dir = dirname( $file );
		foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size ) {
			if ( isset( $size['file'] ) ) {
				$paths[] = $dir . '/' . $size['file'];
			}
		}

		return array_values( array_unique( $paths ) );
	}

	/**
	 * @param array<int, string> $paths
	 */
	private static function convertPathsFor( int $attachment_id, array $paths, bool $force ): int {
		if ( ! $paths ) {
			return 0;
		}

		$existing = array_values( (array) get_post_meta( $attachment_id, self::META_KEY, true ) );
		$created = array();

		foreach ( $paths as $path ) {
			$webp_path = self::webpPathFor( $path );
			if ( ! $force && file_exists( $webp_path ) ) {
				$created[] = $webp_path;
				continue;
			}

			$result = self::convertFile( $path );
			if ( $result ) {
				$created[] = $result;
			}
		}

		$created = array_values( array_unique( $created ) );
		if ( $created !== $existing ) {
			update_post_meta( $attachment_id, self::META_KEY, $created );
		}

		return count( array_diff( $created, $existing ) );
	}

	public static function deleteVariants( int $attachment_id ): void {
		$paths = get_post_meta( $attachment_id, self::META_KEY, true );
		foreach ( (array) $paths as $path ) {
			if ( is_string( $path ) && file_exists( $path ) ) {
				unlink( $path );
			}
		}
	}

	/**
	 * Builds a <picture> element with a webp source when one exists for the given attachment,
	 * falling back to a plain <img> otherwise.
	 *
	 * @param array<string, string> $attrs Extra attributes merged onto the <img> tag.
	 */
	public static function picture( int $attachment_id, string $size = 'full', array $attrs = array() ): string {
		// Bypass preferWebpImageSrc() here: the <img> fallback must stay in the original
		// format, otherwise there is nothing left for the <source type="image/webp"> to fall back from.
		remove_filter( 'wp_get_attachment_image_src', array( self::class, 'preferWebpImageSrc' ) );
		$image = wp_get_attachment_image_src( $attachment_id, $size );
		add_filter( 'wp_get_attachment_image_src', array( self::class, 'preferWebpImageSrc' ) );
		if ( ! $image ) {
			return '';
		}

		list( $src, $width, $height ) = $image;
		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		$attrs = array_merge(
			array(
				'width' => (string) $width,
				'height' => (string) $height,
				'alt' => is_string( $alt ) ? $alt : '',
			),
			$attrs
		);
		$attr_string = self::attrsToString( $attrs );
		$img = sprintf( '<img src="%s"%s>', esc_url( $src ), $attr_string );

		$webp_src = self::webpUrlFor( $src );
		if ( $webp_src === $src ) {
			return $img;
		}

		return sprintf( '<picture><source type="image/webp" srcset="%s">%s</picture>', esc_url( $webp_src ), $img );
	}

	/**
	 * Makes wp_get_attachment_image_url() / wp_get_attachment_image_src() return the webp
	 * variant on the front end whenever one exists, without touching every call site.
	 *
	 * @param array<int, mixed>|false $image
	 * @return array<int, mixed>|false
	 */
	public static function preferWebpImageSrc( $image ) {
		if ( is_admin() || ! is_array( $image ) || ! isset( $image[0] ) || ! is_string( $image[0] ) ) {
			return $image;
		}

		$image[0] = self::webpUrlFor( $image[0] );
		return $image;
	}

	/**
	 * Same as preferWebpImageSrc(), but for the full <img> tag built by wp_get_attachment_image():
	 * rewrites both `src` and every URL inside `srcset`.
	 *
	 * @param array<string, mixed> $attr
	 * @return array<string, mixed>
	 */
	public static function preferWebpImageAttributes( array $attr ): array {
		if ( is_admin() ) {
			return $attr;
		}

		if ( isset( $attr['src'] ) && is_string( $attr['src'] ) ) {
			$attr['src'] = self::webpUrlFor( $attr['src'] );
		}

		if ( isset( $attr['srcset'] ) && is_string( $attr['srcset'] ) ) {
			$attr['srcset'] = implode(
				', ',
				array_map(
					static function ( string $entry ): string {
						$entry = trim( $entry );
						$space = strrpos( $entry, ' ' );
						if ( false === $space ) {
							return self::webpUrlFor( $entry );
						}
						return self::webpUrlFor( substr( $entry, 0, $space ) ) . substr( $entry, $space );
					},
					explode( ',', $attr['srcset'] )
				)
			);
		}

		return $attr;
	}

	/**
	 * Returns the webp URL for an uploads-relative image URL when a converted sibling exists on disk,
	 * otherwise returns the original URL unchanged.
	 */
	public static function webpUrlFor( string $url ): string {
		$webp_path = self::urlToWebpPath( $url );
		if ( ! $webp_path || ! file_exists( $webp_path ) ) {
			return $url;
		}

		return substr( $url, 0, -( strlen( pathinfo( $url, PATHINFO_EXTENSION ) ) ) ) . 'webp';
	}

	private static function urlToWebpPath( string $url ): ?string {
		$upload_dir = wp_get_upload_dir();
		if ( ! str_starts_with( $url, $upload_dir['baseurl'] ) ) {
			return null;
		}

		$path = $upload_dir['basedir'] . substr( $url, strlen( $upload_dir['baseurl'] ) );
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'png', 'jpg', 'jpeg' ), true ) ) {
			return null;
		}

		return self::webpPathFor( $path );
	}

	private static function webpPathFor( string $path ): string {
		return preg_replace( '/\.(png|jpe?g)$/i', '.webp', $path );
	}

	private static function convertFile( string $path ): ?string {
		if ( filesize( $path ) > self::MAX_SOURCE_BYTES ) {
			return null;
		}

		$editor = wp_get_image_editor( $path );
		if ( is_wp_error( $editor ) ) {
			return null;
		}
		if ( ! $editor->supports_mime_type( 'image/webp' ) ) {
			update_option( self::UNSUPPORTED_NOTICE_OPTION, true );
			return null;
		}

		$has_alpha = self::hasAlpha( $path );
		if ( method_exists( $editor, 'set_quality' ) ) {
			$editor->set_quality( $has_alpha ? 90 : 82 );
		}

		$destination = self::webpPathFor( $path );
		$result = $editor->save( $destination, 'image/webp' );
		if ( is_wp_error( $result ) || ! isset( $result['path'] ) ) {
			return null;
		}

		if ( filesize( $result['path'] ) >= filesize( $path ) ) {
			unlink( $result['path'] );
			return null;
		}

		return $result['path'];
	}

	/**
	 * Reads the PNG color-type byte directly (offset 25 of the file: 8-byte signature +
	 * 4-byte IHDR length + 4-byte "IHDR" + 4-byte width + 4-byte height + 1-byte bit depth).
	 * Color types 4 (grayscale+alpha) and 6 (truecolor+alpha) carry an alpha channel.
	 */
	private static function hasAlpha( string $path ): bool {
		if ( 'image/png' !== wp_check_filetype( $path )['type'] ) {
			return false;
		}

		$handle = fopen( $path, 'rb' );
		if ( ! $handle ) {
			return false;
		}
		fseek( $handle, 25 );
		$color_type = fread( $handle, 1 );
		fclose( $handle );

		return '' !== $color_type && in_array( ord( $color_type ), array( 4, 6 ), true );
	}

	/**
	 * @param array<string, string> $attrs
	 */
	private static function attrsToString( array $attrs ): string {
		$pairs = array();
		foreach ( $attrs as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}
			$pairs[] = sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return implode( '', $pairs );
	}

	public static function renderUnsupportedNotice(): void {
		if ( ! get_option( self::UNSUPPORTED_NOTICE_OPTION ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Logika Core: сервер не підтримує конвертацію зображень у WebP (немає підтримки у GD/Imagick). Завантажені PNG/JPEG залишаються без WebP-версії.', 'logika-core' ) .
			'</p></div>';
	}
}

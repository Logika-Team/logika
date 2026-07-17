<?php
/**
 * Plugin Name: Logika Core
 * Description: Контентна модель сайту Logika.
 * Version: 0.1.0
 * Requires PHP: 8.3
 * Text Domain: logika-core
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'LOGIKA_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOGIKA_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once LOGIKA_CORE_PATH . 'src/AcfJson.php';
require_once LOGIKA_CORE_PATH . 'src/ContentTypes.php';
require_once LOGIKA_CORE_PATH . 'src/OptionsPage.php';
require_once LOGIKA_CORE_PATH . 'src/CitySlug.php';
require_once LOGIKA_CORE_PATH . 'src/CityApi.php';
require_once LOGIKA_CORE_PATH . 'src/MediaApi.php';
require_once LOGIKA_CORE_PATH . 'src/ArticleViews.php';
require_once LOGIKA_CORE_PATH . 'src/HomepageImageOverrides.php';

Logika\Core\AcfJson::register();
add_action( 'init', array( Logika\Core\ContentTypes::class, 'register' ) );
add_action( 'acf/init', array( Logika\Core\OptionsPage::class, 'register' ) );
add_action( 'rest_api_init', array( Logika\Core\CityApi::class, 'register' ) );
add_action( 'rest_api_init', array( Logika\Core\MediaApi::class, 'register' ) );
add_action( 'rest_api_init', array( Logika\Core\ArticleViews::class, 'register' ) );
Logika\Core\HomepageImageOverrides::register();

register_activation_hook(
	__FILE__,
	static function (): void {
		Logika\Core\ContentTypes::register();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);

<?php

declare(strict_types=1);

require dirname(__DIR__) . '/wordpress/wp-load.php';

$theme_path = get_template_directory();
$errors     = array();

if ( ! function_exists( 'logika_theme_render_source_page' ) ) {
	$errors[] = 'Source-page renderer is not registered.';
} else {
	ob_start();
	logika_theme_render_source_page( 'index' );
	$homepage = ob_get_clean();

	if ( ! str_contains( $homepage, 'banner-section' ) ) {
		$errors[] = 'Homepage does not render the source banner.';
	}

	$hero_title = get_field( 'home_hero_title', get_option( 'page_on_front' ) );

	if ( ! $hero_title || ! str_contains( $homepage, esc_html( $hero_title ) ) ) {
		$errors[] = 'Homepage hero does not use the ACF title.';
	}

	if ( ! str_contains( $homepage, 'data-logika-lead-form' ) || ! str_contains( $homepage, 'name="consent_accepted"' ) ) {
		$errors[] = 'Homepage does not render a protected lead form.';
	}

	if ( ! str_contains( $homepage, 'class="main-form__status"' ) ) {
		$errors[] = 'Homepage lead form has no non-visual submission status.';
	}

	foreach ( array( 'media-section__cards-layout', 'media-section__news', 'media-section__promos', 'media-section__blog-list', 'media-section__background' ) as $marker ) {
		if ( ! str_contains( $homepage, $marker ) ) {
			$errors[] = "Homepage media center is missing {$marker}.";
		}
	}

	if ( ! str_contains( $homepage, 'name="child_age"' ) || ! str_contains( $homepage, 'main-form__select' ) || ! str_contains( $homepage, 'data-logika-age-select' ) || ! str_contains( $homepage, 'main-form__age-dropdown' ) ) {
		$errors[] = 'Homepage lead form does not render the child age dropdown contract.';
	}

	if ( str_contains( $homepage, 'logika-child-age-static' ) || str_contains( $homepage, '</ul></div></button><ul class="main-form__age-dropdown"' ) || 1 > substr_count( $homepage, 'data-logika-age-select' ) ) {
		$errors[] = 'Homepage lead form keeps stale child age dropdown markup and breaks layout.';
	}

if ( ! str_contains( $homepage, 'data-logika-phone-input' ) || ! str_contains( $homepage, 'name="tel"' ) ) {
		$errors[] = 'Homepage lead form does not render the intl phone input contract.';
	}

	/*
	 * Contract copied from main/build/index.html. These are structural markers,
	 * not content assertions: ACF may change values but must not replace the
	 * supplied layout with a shorter template.
	 */
	$homepage_contract = array(
		'<p class="main-form__text">' => 1,
		'<picture>'                       => 4,
		'english-section__subtitle'       => 1,
		'english-section__controls'       => 1,
		'media-section__cards'            => 1,
		'media-section__card'             => 6,
		'swiper-slide'                     => 17,
	);

	foreach ( $homepage_contract as $marker => $minimum ) {
		if ( substr_count( $homepage, $marker ) < $minimum ) {
			$errors[] = "Homepage no longer matches the main HTML contract: {$marker}.";
		}
	}
}
if ( ! str_contains( $homepage, 'data-logika-phone-error' ) ) {
	$errors[] = 'Lead form does not render an inline phone validation message.';
}

foreach ( array( 'index', 'about', 'article', 'camp', 'camps', 'en-courses', 'faq', 'it-course', 'it-courses', 'media-center' ) as $page ) {
	if ( ! is_readable( "{$theme_path}/source-pages/{$page}.php" ) ) {
		$errors[] = "Source page {$page} was not transferred.";
	}
}

if ( ! is_readable( "{$theme_path}/assets/css/style.css" ) || ! is_readable( "{$theme_path}/assets/js/main.js" ) ) {
	$errors[] = 'Theme assets were not transferred.';
}

$theme_css = is_readable( "{$theme_path}/assets/css/style.css" ) ? file_get_contents( "{$theme_path}/assets/css/style.css" ) : '';
if ( str_contains( $theme_css, '.media-section__box>div{height:400px;background-color:red}' ) ) {
	$errors[] = 'Homepage media center still contains the red debug layout rule.';
}
if ( ! str_contains( $theme_css, 'main-form__honeypot' ) || ! str_contains( $theme_css, 'position:absolute' ) ) {
	$errors[] = 'Lead form honeypot is visible in layout.';
}

if ( ! is_readable( "{$theme_path}/assets/js/city-selector.js" ) ) {
	$errors[] = 'City selector script is missing.';
}

foreach ( array( 'intlTelInput.min.js', 'utils.js' ) as $vendor_js ) {
	if ( ! is_readable( "{$theme_path}/assets/js/vendor/intl-tel-input/{$vendor_js}" ) ) {
		$errors[] = "Intl phone vendor script {$vendor_js} is missing.";
	}
}

if ( ! is_readable( "{$theme_path}/assets/js/vendor/intl-tel-input/i18n-uk.js" ) ) {
	$errors[] = 'Intl phone Ukrainian country translations are missing.';
}

foreach ( array( 'intlTelInput.min.css', 'flags.png', 'flags@2x.png', 'globe.png', 'globe@2x.png' ) as $vendor_asset ) {
	if ( ! is_readable( "{$theme_path}/assets/css/vendor/intl-tel-input/{$vendor_asset}" ) ) {
		$errors[] = "Intl phone vendor asset {$vendor_asset} is missing.";
	}
}

$functions_php = file_get_contents( "{$theme_path}/functions.php" );
foreach ( array( 'logika-intl-tel-input', 'logika-intl-tel-input-i18n-uk', 'phoneCountryDefault', 'phoneUtilsUrl', 'phoneCountryEndpoint', 'logika/v1/phone-country' ) as $asset_contract ) {
	if ( ! str_contains( $functions_php, $asset_contract ) ) {
		$errors[] = "Theme assets do not expose {$asset_contract}.";
	}
}

if ( ! str_contains( $functions_php, 'logika-home-media-center' ) || ! is_readable( "{$theme_path}/assets/css/blocks/sections/media-section.css" ) ) {
	$errors[] = 'Homepage media center stylesheet is missing.';
}

if ( ! str_contains( $functions_php, "get_query_var( 'logika_city' )" ) ) {
	$errors[] = 'Homepage media center stylesheet is missing on city homepages.';
}

$leads_js = is_readable( "{$theme_path}/assets/js/leads.js" ) ? file_get_contents( "{$theme_path}/assets/js/leads.js" ) : '';
if ( ! str_contains( $leads_js, 'main-form__submit-error' ) ) {
	$errors[] = 'Lead form does not render a dedicated submission error alert.';
}
if ( ! str_contains( $leads_js, "[type=\"submit\"], .main-form__btn" ) ) {
	$errors[] = 'Submission alert does not support source-markup submit buttons.';
}
if ( str_contains( $leads_js, 'crypto.randomUUID()' ) || ! str_contains( $leads_js, 'logikaLeadToast' ) ) {
	$errors[] = 'Lead form must use a compatible request ID and global toast notification.';
}
foreach ( array( 'initialCountry: \'auto\'', 'geoIpLookup', 'phoneCountryEndpoint', 'preferredPhoneCountries', "['ua', 'sk', 'pl', 'cz', 'de', 'ro', 'md', 'gb', 'ca']", 'germany deutschland', 'romania', 'moldova', 'great britain', 'canada', 'setupPhoneCountrySearch', 'filterPhoneCountries', 'Пошук країни...', 'showSelectedDialCode: true', 'window.logikaIntlTelInputUk', 'iti--has-value', 'iti--phone-dropdown-open' ) as $phone_ui_contract ) {
	if ( ! str_contains( $leads_js, $phone_ui_contract ) ) {
		$errors[] = "Phone country code UI does not expose {$phone_ui_contract}.";
	}
}

$phone_i18n = is_readable( "{$theme_path}/assets/js/vendor/intl-tel-input/i18n-uk.js" ) ? file_get_contents( "{$theme_path}/assets/js/vendor/intl-tel-input/i18n-uk.js" ) : '';
foreach ( array( 'Данія', 'Джибуті', 'Домініканська Республіка', 'Еквадор', 'searchPlaceholder: "Пошук країни..."' ) as $translation_contract ) {
	if ( ! str_contains( $phone_i18n, $translation_contract ) ) {
		$errors[] = "Phone country translation is missing {$translation_contract}.";
	}
}

foreach ( array( '.iti--phone-dropdown-open', 'border-radius:26px 26px 0 0', 'border-top:0', 'border-bottom:4px solid var(--grey-500)', '.iti--inline-dropdown .iti__dropdown-content', 'max-height:360px', '.iti__dropdown-content:not(.iti__hide)', 'display:flex', 'overflow-x:hidden', 'border-radius:0 0 12px 12px', 'iti__country[hidden]', 'iti__divider[hidden]', 'display:none', 'iti__country.iti__highlight', 'background-color:var(--violet-300)', '.iti__country.iti__active', 'background-color:var(--violet-100)', '.iti__search-input{display:block', 'max-height:306px', 'overflow-y:auto' ) as $phone_css_contract ) {
	if ( ! str_contains( $theme_css, $phone_css_contract ) ) {
		$errors[] = "Phone country code CSS does not expose {$phone_css_contract}.";
	}
}

foreach ( array( 'header__city-list', 'overflow-x:hidden', 'header__city-option', 'text-overflow:ellipsis' ) as $city_dropdown_contract ) {
	if ( ! str_contains( $theme_css, $city_dropdown_contract ) ) {
		$errors[] = "City dropdown can expose horizontal scrolling: {$city_dropdown_contract}.";
	}
}

if ( ! preg_match( '/html\{[^}]*overflow-x:hidden/', $theme_css ) ) {
	$errors[] = 'Page root can expose horizontal scrolling.';
}

foreach ( array( 'main-form__age-dropdown', 'border-radius:0 0 12px 12px', 'main-form__age-option:hover', 'background-color:var(--violet-300)' ) as $age_css_contract ) {
	if ( ! str_contains( $theme_css, $age_css_contract ) ) {
		$errors[] = "Child age dropdown CSS does not expose {$age_css_contract}.";
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo "Source HTML pages are available in the theme.\n";

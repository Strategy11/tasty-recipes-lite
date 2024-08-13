<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Tasty_Recipes
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', __DIR__ . '/../vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load any necessary plugins.
 */
function _manually_load_plugin() {
	// Plugin being tested.
	require dirname( __DIR__ ) . '/tasty-recipes.php';
	// Important plugin we want to test alongside.
	if ( version_compare( str_replace( '-src', '', $GLOBALS['wp_version'] ), '4.9', '>=' ) ) {
		require dirname( __DIR__ ) . '/vendor/wordpress-seo/wp-seo.php';
		_wpseo_activate();
		$option                              = get_option( 'wpseo_titles' );
		$option['company_or_person']         = 'person';
		$option['company_or_person_user_id'] = 1;
		update_option( 'wpseo_titles', $option );
	}

	// Pretend like we have a valid license.
	update_option( Tasty_Recipes::LICENSE_KEY_OPTION, 'abc123' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Set the site url to pinchofyum.com.
 */
function _set_siteurl() {
	return 'http://pinchofyum.com';
}
tests_add_filter( 'pre_option_home', '_set_siteurl' );
tests_add_filter( 'pre_option_siteurl', '_set_siteurl' );

/**
 * Filter API requests to Nutrifox.
 *
 * @param mixed  $pre Existing filter value.
 * @param array  $r   Request arguments.
 * @param string $url Request URL.
 *
 * @return mixed
 */
function _nutrifox_api_request( $pre, $r, $url ) {
	if ( 'nutrifox.com' !== parse_url( $url, PHP_URL_HOST ) ) {
		return $pre;
	}
	if ( preg_match( '#api/tasty-recipes/conversion-enrichment#', $url ) ) {
		$file_path = __DIR__ . '/data/nutrifox-conversion-enrichment.json';
		if ( file_exists( $file_path ) ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => file_get_contents( $file_path ),
			);
		}
	}
	if ( preg_match( '#api/recipes/([\d]+)#', $url, $matches ) ) {
		$file_path = __DIR__ . '/data/nutrifox-' . $matches[1] . '.json';
		if ( file_exists( $file_path ) ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => file_get_contents( $file_path ),
			);
		}
	}
	return array(
		'response' => array(
			'code' => 404,
		),
		'body'     => '',
	);
}
tests_add_filter( 'pre_http_request', '_nutrifox_api_request', 10, 3 );

/**
 * Filter API requests to WP Tasty.
 *
 * @param mixed  $pre Existing filter value.
 * @param array  $r   Request arguments.
 * @param string $url Request URL.
 *
 * @return mixed
 */
function _wptasty_api_request( $pre, $r, $url ) {
	if ( 'www.wptasty.com' !== parse_url( $url, PHP_URL_HOST ) ) {
		return $pre;
	}
	if ( ! empty( $r['body'] )
		&& ! empty( $r['body']['edd_action'] )
		&& 'check_license' === $r['body']['edd_action'] ) {
		return array(
			'response' => array(
				'code' => 200,
			),
			'body'     => file_get_contents( __DIR__ . '/data/wptasty-check-license.json' ),
		);
	}

	if ( false !== stripos( $url, '/tasty-recipes-api/youtube-metadata' ) ) {
		$query = parse_url( $url, PHP_URL_QUERY );
		parse_str( $query, $args );
		if ( ! empty( $args['id'] ) ) {
			$file_path = __DIR__ . '/data/wptasty-youtube-' . $args['id'] . '.json';
			if ( file_exists( $file_path ) ) {
				return array(
					'response' => array(
						'code' => 200,
					),
					'body'     => file_get_contents( $file_path ),
				);
			}
		}
	}

	return array(
		'response' => array(
			'code' => 404,
		),
		'body'     => '',
	);
}
tests_add_filter( 'pre_http_request', '_wptasty_api_request', 10, 3 );

/**
 * Filter oEmbed API requests.
 *
 * @param mixed  $pre Existing filter value.
 * @param array  $r   Request arguments.
 * @param string $url Request URL.
 *
 * @return mixed
 */
function _oembed_api_request( $pre, $r, $url ) {
	$host = parse_url( $url, PHP_URL_HOST );
	if ( ! in_array( $host, array( 'vimeo.com', 'www.youtube.com', 'embed.mediavine.com' ), true ) ) {
		return $pre;
	}
	$query = parse_url( $url, PHP_URL_QUERY );
	parse_str( $query, $args );
	if ( 'vimeo.com' === $host && preg_match( '#([\d]+)#', $args['url'], $matches ) ) {
		$file_path = __DIR__ . '/data/vimeo-' . $matches[1] . '.json';
		if ( file_exists( $file_path ) ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => file_get_contents( $file_path ),
			);
		}
	}
	if ( 'www.youtube.com' === $host && preg_match( '#v=([^&]+)#', $args['url'], $matches ) ) {
		$file_path = __DIR__ . '/data/youtube-' . $matches[1] . '.json';
		if ( file_exists( $file_path ) ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => file_get_contents( $file_path ),
			);
		}
	}
	if ( 'embed.mediavine.com' === $host && preg_match( '#videos/([^/]+)/#', $args['url'], $matches ) ) {
		$file_path = __DIR__ . '/data/mediavine-' . $matches[1] . '.json';
		if ( file_exists( $file_path ) ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => file_get_contents( $file_path ),
			);
		}
	}
	return array(
		'response' => array(
			'code' => 404,
		),
		'body'     => '',
	);
}
tests_add_filter( 'pre_http_request', '_oembed_api_request', 10, 3 );

/**
 * Unset 'spam' when creating users on multisite because it causes errors in the test suite.
 *
 * @param array $args Original creation arguments.
 *
 * @return array
 */
function _wp_tasty_pre_insert_user_data( $args ) {
	unset( $args['spam'] );
	return $args;
}
tests_add_filter( 'wp_pre_insert_user_data', '_wp_tasty_pre_insert_user_data' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

echo PHP_EOL;
echo 'WP version: ' . $GLOBALS['wp_version'];
echo PHP_EOL . PHP_EOL;

require __DIR__ . '/class-tasty-recipes-testcase.php';
require __DIR__ . '/class-tasty-recipes-converter-testcase.php';

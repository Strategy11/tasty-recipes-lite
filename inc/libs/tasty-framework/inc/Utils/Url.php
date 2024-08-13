<?php
/**
 * Utility class deals with urls.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Utils;

/**
 * Utility class deals with urls.
 */
class Url {

	/**
	 * Return url with passed utm query strings added.
	 *
	 * @param string $url        Url to have utm query strings.
	 * @param array  $utm_params UTM query strings.
	 *
	 * @return string
	 */
	public static function add_utm_params( $url, $utm_params = array() ) {
		$default_utm_params = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => 'dashboard',
			'utm_campaign' => 'plugin',
			'utm_content'  => 'menu',
		);

		$utm_params = wp_parse_args( $utm_params, $default_utm_params );

		if ( false === strpos( $url, 'wptasty.com' ) ) {
			$url = 'https://www.wptasty.com/' . $url;
		}

		return add_query_arg( $utm_params, $url );
	}

	/**
	 * Get main admin menu link.
	 *
	 * @return string
	 */
	public static function get_main_admin_url() {
		return menu_page_url( 'tasty', false );
	}

	/**
	 * Check if current page is one of our pages.
	 *
	 * @return bool
	 */
	public static function is_wpt_page() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		return (bool) apply_filters( 'tasty_framework_admin_page_ours', $screen && ( 'toplevel_page_tasty' === $screen->base || stristr( $screen->base, 'wp-tasty_page_' ) ) );
	}

	/**
	 * Redirect to a URL even if the headers are already sent.
	 *
	 * @param string $url Url to redirect to.
	 *
	 * @return void
	 */
	public static function redirect( $url ) {
		if ( headers_sent() ) {
			echo '<script type="text/javascript">window.location="' . esc_url_raw( $url ) . '"</script>';
			return;
		}

		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Check if the current page is the dashboard/licensing page.
	 *
	 * @return bool
	 */
	public static function is_dashboard_page() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		return 'toplevel_page_tasty' === get_current_screen()->id;
	}
}

<?php
/**
 * Handles the integration with Mediavine Grow.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes\Utils;

/**
 * Integration with Mediavine Grow.
 */
class Mediavine {
	use Singleton;

	/**
	 * Flag to check if the JS file has been loaded.
	 *
	 * @var bool
	 */
	private static $js_loaded = false;

	/**
	 * Load the JS if the base JS is loaded.
	 *
	 * @since 3.13
	 *
	 * @return void
	 */
	public static function load_scripts() {
		if ( self::$js_loaded ) {
			return;
		}

		self::$js_loaded = true;
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_js' ), 1 );
	}

	/**
	 * Print the JS.
	 *
	 * @since 3.13
	 *
	 * @return void
	 */
	public static function print_js() {
		$js = Utils::get_contents( 'assets/js/mediavine-grow.js', 'path' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo PHP_EOL . '<script data-tasty-grow="">' . Utils::minify_js( $js ) . '</script>' . PHP_EOL;
	}
}

<?php
/**
 * Tasty Recipes class.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Admin\Plugins;

use Tasty\Framework\Abstracts\PluginInstaller;
use Tasty\Framework\Traits\Singleton;

/**
 * Tasty Recipes class.
 */
class Recipes extends PluginInstaller {

	use Singleton;

	/**
	 * Get current plugin file (dir/file.php).
	 *
	 * @return string
	 */
	protected function get_plugin_file() {
		return 'tasty-recipes/tasty-recipes.php';
	}

	/**
	 * Get current plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return 'Tasty Recipes';
	}

	/**
	 * Get current plugin license key option key.
	 *
	 * @return string
	 */
	protected function get_license_key_option_name() {
		return 'tasty_recipes_license_key';
	}

	/**
	 * Get current plugin license check transient name.
	 *
	 * @return string
	 */
	protected function get_license_check_cache_key() {
		return 'tasty-recipes-license-check';
	}

	/**
	 * Get current plugin details url.
	 *
	 * @return string
	 */
	public function get_product_details_page_url() {
		return 'https://www.wptasty.com/tasty-recipes';
	}

	/**
	 * Get current plugin pricing url.
	 *
	 * @return string
	 */
	public function get_product_pricing_page_url() {
		return 'https://www.wptasty.com/pricing-recipes';
	}

	/**
	 * Can add the plugin item to admin menu?
	 *
	 * @return bool
	 */
	public function has_framework() {
		return ! defined( 'TASTY_RECIPES_PLUGIN_VERSION' )
			||
			(
				defined( 'TASTY_RECIPES_PLUGIN_VERSION' )
				&&
				version_compare( TASTY_RECIPES_PLUGIN_VERSION, '3.9.1', '>' )
			);
	}
}

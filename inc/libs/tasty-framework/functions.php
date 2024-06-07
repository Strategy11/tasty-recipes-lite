<?php
/**
 * Helper functions.
 *
 * @package Tasty/Framework
 */

use Tasty\Framework\Utils\Template;

if ( ! function_exists( 'tasty_get_filesystem' ) ) {
	/**
	 * Get filesystem instance.
	 *
	 * @return WP_Filesystem_Direct Filesystem instance
	 */
	function tasty_get_filesystem() {
		static $filesystem = null;

		if ( is_null( $filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$filesystem = new WP_Filesystem_Direct( new StdClass() );
		}

		return $filesystem;
	}
}

if ( ! function_exists( 'tasty_get_admin_template' ) ) {
	/**
	 * Get admin template instance.
	 *
	 * @return Template Filesystem instance
	 */
	function tasty_get_admin_template() {
		return Template::instance()->init( __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Admin', tasty_get_filesystem() );
	}
}

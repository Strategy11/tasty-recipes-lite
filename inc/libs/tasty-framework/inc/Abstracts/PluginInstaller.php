<?php
/**
 * PluginInstaller Abstract class.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Abstracts;

use WP_Ajax_Upgrader_Skin;
use Plugin_Upgrader;
use Tasty\Framework\Admin\License\APIClient;
use WP_Error;
use WP_Filesystem_Direct;

/**
 * PluginInstaller Abstract class.
 */
abstract class PluginInstaller {
	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = '';

	/**
	 * Filesystem instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * License APIClient instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Get current plugin file (dir/file.php).
	 *
	 * @return string
	 */
	abstract protected function get_plugin_file();

	/**
	 * Get current plugin name.
	 *
	 * @return string
	 */
	abstract public function get_plugin_name();

	/**
	 * Get current plugin license key option key.
	 *
	 * @return string
	 */
	abstract protected function get_license_key_option_name();

	/**
	 * Get current plugin license check transient name.
	 *
	 * @return string
	 */
	abstract protected function get_license_check_cache_key();

	/**
	 * Get current plugin details url.
	 *
	 * @return string
	 */
	abstract public function get_product_details_page_url();

	/**
	 * Get current plugin pricing url.
	 *
	 * @return string
	 */
	abstract public function get_product_pricing_page_url();

	/**
	 * Can add the plugin item to admin menu?
	 *
	 * @return bool
	 */
	abstract public function has_framework();

	/**
	 * Initialize needed.
	 *
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 * @param APIClient            $api_client License API Client instance.
	 *
	 * @return $this
	 */
	public function init( $filesystem, $api_client ) {
		$this->filesystem = $filesystem;
		$this->api_client = $api_client;

		return $this;
	}

	/**
	 * Get current plugin slug from plugin file.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		if ( ! empty( $this->plugin_slug ) ) {
			return $this->plugin_slug;
		}
		list( $slug )      = explode( '/', $this->get_plugin_file() );
		$this->plugin_slug = $slug;
		return $slug;
	}

	/**
	 * Install and activate the plugin.
	 *
	 * @param string $plugin_download_link Plugin download url.
	 *
	 * @return void
	 */
	public function maybe_install_and_activate( $plugin_download_link = '' ) {
		if ( $this->is_installed() && $this->is_active() ) {
			return;
		}

		$installed = $this->is_installed();

		if ( ! $installed ) {
			$installed = $this->install_plugin( $plugin_download_link );
		}

		if ( ! $installed ) {
			return;
		}

		$this->activate_plugin();
	}

	/**
	 * Check if current plugin is installed.
	 *
	 * @return bool
	 */
	public function is_installed() {
		return $this->filesystem->is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->get_plugin_slug() );
	}

	/**
	 * Check if current plugin is activated.
	 *
	 * @return bool
	 */
	public function is_active() {
		return is_plugin_active( $this->get_plugin_file() );
	}

	/**
	 * Install or download plugin using zip download url.
	 *
	 * @param string $plugin_download_link Plugin download url.
	 *
	 * @return bool
	 */
	protected function install_plugin( $plugin_download_link ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $plugin_download_link );

		if ( is_wp_error( $result ) || is_null( $result ) || is_wp_error( $skin->result ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Activate current plugin.
	 *
	 * @return void
	 */
	public function activate_plugin() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		activate_plugin( $this->get_plugin_file(), '', false, true );
	}

	/**
	 * Deactivate current plugin.
	 *
	 * @return void
	 */
	public function deactivate_plugin() {
		if ( ! current_user_can( 'deactivate_plugins' ) ) {
			return;
		}

		deactivate_plugins(
			array(
				$this->get_plugin_file(),
			)
		);
	}

	/**
	 * Save license key into options table.
	 *
	 * @param string $license_key License key to be saved.
	 *
	 * @return void
	 */
	public function save_license_key( $license_key ) {
		update_option( $this->get_license_key_option_name(), $license_key, false );
	}

	/**
	 * Get license key from options table.
	 *
	 * @return string
	 */
	public function get_license_key() {
		return get_option( $this->get_license_key_option_name(), '' );
	}

	/**
	 * Remove and deactivate license key.
	 *
	 * @return void
	 */
	public function remove_license() {
		delete_option( $this->get_license_key_option_name() );
		delete_transient( $this->get_license_check_cache_key() );
		$this->api_client->deactivate_plugin_license( $this->get_license_key(), $this->get_plugin_name() );
	}

	/**
	 * Check if license key is saved into database.
	 *
	 * @return bool
	 */
	public function is_licensed() {
		return ! empty( $this->get_license_key() );
	}

	/**
	 * Get check license details.
	 *
	 * @return object|WP_Error
	 */
	public function check_license() {
		$cached = get_transient( $this->get_license_check_cache_key() );
		if ( false !== $cached ) {
			return is_wp_error( $cached ) ? $cached : (object) $cached;
		}

		$license_key = $this->get_license_key();
		if ( empty( $license_key ) ) {
			return new WP_Error( 500, __( 'That\'s not a valid license.', 'tasty' ) );
		}

		$check_license = $this->api_client->check_plugin_license_raw( $license_key, $this->get_plugin_name() );
		// Save an error for 1 minute to avoid API DDOS.
		$save_for = is_wp_error( $check_license ) ? MINUTE_IN_SECONDS : 24 * HOUR_IN_SECONDS;
		set_transient( $this->get_license_check_cache_key(), $check_license, $save_for );
		return is_wp_error( $check_license ) ? $check_license : (object) $check_license;
	}

	/**
	 * Check if license check request returns an error.
	 *
	 * @return bool
	 */
	public function is_error() {
		return $this->is_licensed() && is_wp_error( $this->check_license() );
	}

	/**
	 * Check if license is expired for current plugin.
	 *
	 * @return bool
	 */
	public function is_expired() {
		$license = $this->check_license();
		if ( empty( $license ) || is_wp_error( $license ) ) {
			return true;
		}

		return 'expired' === $license->license;
	}

	/**
	 * Check if current license is valid.
	 *
	 * @return bool
	 */
	public function is_valid_license() {
		$license = $this->check_license();
		if ( empty( $license ) || is_wp_error( $license ) ) {
			return false;
		}

		return 'valid' === $license->license;
	}

	/**
	 * Check if this plugin can be upgraded or not.
	 *
	 * @return bool
	 */
	public function can_upgrade() {
		$license = $this->check_license();
		if ( empty( $license ) || is_wp_error( $license ) ) {
			return false;
		}

		return ! in_array( (int) $license->price_id, array( 2, 3 ), true );
	}

	/**
	 * Activate license for current plugin.
	 *
	 * @param string $license_key License key to be activated.
	 *
	 * @return array|WP_Error
	 */
	public function activate_license( $license_key ) {
		return $this->api_client->activate_plugin_license( $license_key, $this->get_plugin_name() );
	}

	/**
	 * Get plugin status (active, installed, empty for not installed).
	 *
	 * @return string
	 */
	public function get_plugin_status() {
		if ( $this->is_active() ) {
			return 'active';
		}

		if ( $this->is_installed() ) {
			return 'installed';
		}

		return 'not-installed';
	}

	/**
	 * Get plugin download url.
	 *
	 * @return string
	 */
	public function get_plugin_download_url() {
		$license_key = $this->get_license_key();
		if ( empty( $license_key ) ) {
			return '';
		}

		$plugins = $this->api_client->get_key_plugins( $license_key );
		if ( is_wp_error( $plugins ) ) {
			return '';
		}

		foreach ( $plugins as $plugin ) {
			if ( $this->get_plugin_name() !== $plugin['name'] ) {
				continue;
			}
			return isset( $plugin['package'] ) ? $plugin['package'] : '';
		}

		return '';
	}

	/**
	 * Get required capability.
	 *
	 * @return string
	 */
	public function get_required_capability() {
		return 'manage_options';
	}
}

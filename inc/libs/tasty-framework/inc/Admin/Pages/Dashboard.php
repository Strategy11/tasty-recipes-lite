<?php
/**
 * Dashboard page handler class.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Admin\Pages;

use Tasty\Framework\Abstracts\PluginInstaller;
use Tasty\Framework\Admin\Plugins\Factory;
use Tasty\Framework\Traits\Singleton;
use Tasty\Framework\Utils\Template;
use Tasty\Framework\Utils\Url;

/**
 * Dashboard page handler class.
 */
class Dashboard {

	use Singleton;

	/**
	 * Template engine instance.
	 *
	 * @var Template
	 */
	private $template;

	/**
	 * Initialize instance.
	 *
	 * @param Template $template Template engine instance.
	 *
	 * @return $this
	 */
	public function init( $template ) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Show page contents.
	 *
	 * @return void
	 */
	public function show() {
		$plugins           = array();
		$available_plugins = Factory::create_all_plugins();
		$show_add_license  = false;

		foreach ( $available_plugins as $plugin ) {
			if ( ! $plugin ) {
				continue;
			}

			$plugins[] = $this->get_plugin_details( $plugin );

			if ( ! $plugin->is_valid_license() ) {
				$show_add_license = true;
			}
		}

		$banner_url = Url::add_utm_params(
			'pricing',
			array(
				'utm_medium'  => 'banner',
				'utm_content' => 'allaccess',
			)
		);

		$data = array(
			'plugins'          => $plugins,
			'show_add_license' => $show_add_license,
			'show_banner'      => $show_add_license,
			'banner_url'       => $banner_url,
		);
		$this->template->render( 'dashboard', $data, true );
	}

	/**
	 * Get plugin full details.
	 *
	 * @param PluginInstaller $plugin Plugin instance.
	 *
	 * @return array
	 */
	private function get_plugin_details( $plugin ) {
		$status = $this->get_plugin_status( $plugin );

		$details = array(
			'name'        => $plugin->get_plugin_name(),
			'status'      => $status,
			'status_text' => $this->get_plugin_status_text( $status ),
			'actions'     => $this->get_plugin_actions_by_status( $status, $plugin ),
			'buttons'     => $this->get_plugin_buttons( $plugin ),
		);

		return (array) apply_filters( 'tasty_framework_dashboard_plugin_details', $details, $plugin->get_plugin_name(), $plugin, $status );
	}

	/**
	 * Get plugin status.
	 *
	 * @param PluginInstaller $plugin Plugin instance.
	 *
	 * @return string
	 */
	private function get_plugin_status( $plugin ) {
		$installed = $plugin->is_installed();
		$active    = $plugin->is_active();

		if ( ! $installed ) {
			return 'not-installed';
		}

		if ( $installed && ! $active ) {
			return 'installed';
		}

		$licensed = $plugin->is_licensed();

		if ( $active && ! $licensed ) {
			return 'no-license';
		}

		if ( $plugin->is_error() ) {
			return 'error';
		}

		$expired = $plugin->is_expired();
		if ( $active && $licensed && $expired ) {
			return 'expired';
		}

		if ( $active && $licensed && ! $plugin->is_valid_license() ) {
			return 'no-license';
		}

		return 'active';
	}

	/**
	 * Get plugin status text.
	 *
	 * @param string $status Status code.
	 *
	 * @return string
	 */
	private function get_plugin_status_text( $status ) {
		$statuses = array(
			'active'        => __( 'Active', 'tasty' ),
			'installed'     => __( 'Installed', 'tasty' ),
			'not-installed' => __( 'Not installed', 'tasty' ),
			'no-license'    => __( 'No License', 'tasty' ),
			'expired'       => __( 'Expired', 'tasty' ),
			'error'         => __( 'Error', 'tasty' ),
		);

		if ( ! isset( $statuses[ $status ] ) ) {
			return '';
		}

		return $statuses[ $status ];
	}

	/**
	 * Get plugin actions by status.
	 *
	 * @param string          $status Status code.
	 * @param PluginInstaller $plugin Plugin instance.
	 *
	 * @return array
	 */
	private function get_plugin_actions_by_status( $status, $plugin ) {
		$actions = array();

		switch ( $status ) {
			case 'no-license':
			case 'not-installed':
				$actions[]       = array(
					'type' => 'external',
					'name' => __( 'Learn more', 'tasty' ),
					'link' => Url::add_utm_params( $plugin->get_product_details_page_url(), array( 'utm_content' => 'learn_more' ) ),
				);
				$get_license_url = Url::add_utm_params(
					$plugin->get_product_pricing_page_url(),
					array(
						'utm_medium'  => 'get_license',
						'utm_content' => $plugin->get_plugin_slug(),
					)
				);
				$actions[]       = array(
					'type'  => 'external',
					'name'  => __( 'Get a license', 'tasty' ),
					'link'  => $get_license_url,
					'class' => 'tasty-highlight',
				);
				break;

			case 'active':
			case 'expired':
				$actions[] = array(
					'type' => 'ajax',
					'name' => __( 'Remove license', 'tasty' ),
					'link' => add_query_arg(
						array(
							'action'      => 'tasty_framework_remove_license',
							'_ajax_nonce' => wp_create_nonce( 'tasty_framework_remove_license' ),
							'plugin'      => $plugin->get_plugin_name(),
						),
						admin_url( 'admin-ajax.php' )
					),
				);

				$actions['settings'] = array(
					'type' => 'internal',
					'name' => __( 'Settings', 'tasty' ),
					'link' => menu_page_url( $plugin->get_plugin_slug(), false ),
				);

				if ( $plugin->can_upgrade() ) {
					$upgrade_url = Url::add_utm_params(
						$plugin->get_product_pricing_page_url(),
						array(
							'utm_medium'  => 'upgrade',
							'utm_content' => $plugin->get_plugin_slug(),
						)
					);
					$actions[]   = array(
						'type'  => 'external',
						'name'  => __( 'Upgrade plan', 'tasty' ),
						'link'  => $upgrade_url,
						'class' => 'tasty-highlight',
					);
				}
				break;

			case 'installed':
				$learn_more_url = Url::add_utm_params(
					$plugin->get_product_details_page_url(),
					array(
						'utm_medium'  => 'learn_more',
						'utm_content' => $plugin->get_plugin_slug(),
					)
				);
				$actions[]      = array(
					'type' => 'external',
					'name' => __( 'Learn more', 'tasty' ),
					'link' => $learn_more_url,
				);
				$actions[]      = array(
					'type' => 'ajax',
					'name' => __( 'Activate plugin', 'tasty' ),
					'link' => add_query_arg(
						array(
							'action'      => 'tasty_framework_activate_plugin',
							'_ajax_nonce' => wp_create_nonce( 'tasty_framework_activate_plugin' ),
							'plugin'      => $plugin->get_plugin_name(),
						),
						admin_url( 'admin-ajax.php' )
					),
				);
				break;
		}

		if ( $plugin->is_active() ) {
			$actions[] = array(
				'type' => 'ajax',
				'name' => __( 'Deactivate', 'tasty' ),
				'link' => add_query_arg(
					array(
						'action'      => 'tasty_framework_deactivate_plugin',
						'_ajax_nonce' => wp_create_nonce( 'tasty_framework_deactivate_plugin' ),
						'plugin'      => $plugin->get_plugin_name(),
					),
					admin_url( 'admin-ajax.php' )
				),
			);
		}

		return $actions;
	}

	/**
	 * Get plugin buttons by status.
	 *
	 * @param PluginInstaller $plugin Plugin instance.
	 *
	 * @return array
	 */
	private function get_plugin_buttons( $plugin ) {
		$buttons   = array();
		$installed = $plugin->is_installed();
		$active    = $plugin->is_active();
		$licensed  = $plugin->is_licensed();

		if ( ! $installed ) {
			if ( ! $plugin->is_valid_license() ) {
				return array();
			}

			$buttons[] = array(
				'type' => 'ajax',
				'name' => __( 'Download', 'tasty' ),
				'link' => add_query_arg(
					array(
						'action'         => 'tasty_framework_download_plugin',
						'_ajax_nonce'    => wp_create_nonce( 'tasty_framework_download_plugin' ),
						'license_plugin' => $plugin->get_plugin_name(),
					),
					admin_url( 'admin-ajax.php' )
				),
			);
			return $buttons;
		}

		if ( $active ) {
			if ( $licensed && ! $plugin->is_error() && $plugin->is_expired() ) {
				$buttons[] = array(
					'type' => 'external',
					'name' => __( 'Renew', 'tasty' ),
					'link' => Url::add_utm_params(
						'account/downloads',
						array(
							'utm_medium'  => 'dashboard',
							'utm_content' => 'expired',
						)
					),
				);
			}
		} else {
			$buttons[] = array(
				'type' => 'ajax',
				'name' => __( 'Activate', 'tasty' ),
				'link' => add_query_arg(
					array(
						'action'      => 'tasty_framework_activate_plugin',
						'_ajax_nonce' => wp_create_nonce( 'tasty_framework_activate_plugin' ),
						'plugin'      => $plugin->get_plugin_name(),
					),
					admin_url( 'admin-ajax.php' )
				),
			);
		}

		return $buttons;
	}
}

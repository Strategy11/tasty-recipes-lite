<?php
/**
 * Manages interactions with the recipe explorer page.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Vars;

/**
 * Manages interactions with the recipe explorer page.
 */
class Recipe_Explorer {
	/**
	 * Load hooks.
	 * 
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_filter( 'tasty_framework_admin_page_ours', array( __CLASS__, 'make_recipes_cpt_menu_item_ours' ) );
		add_filter( 'tasty_framework_show_license_notice', array( __CLASS__, 'show_notice_in_recipes_cpt' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'handle_cpt_menu_item_classes' ) );
		add_action( 'tasty_tabs_buttons', array( __CLASS__, 'filter_tab_buttons' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'action_pre_get_posts' ) );
	}

	/**
	 * Filter the tab buttons.
	 * 
	 * @since x.x
	 *
	 * @param string $html The HTML of the tab buttons.
	 *
	 * @return string
	 */
	public static function filter_tab_buttons( $html ) {
		global $post_type;

		if ( 'tasty_recipe' !== $post_type || 'edit' !== get_current_screen()->base ) {
			return $html;
		}

		$html  = '<p class="tasty-tabs-buttons">';
		$html .= '<a class="tasty-button tasty-button-pink" href="#">' . __( 'Create Recipe', 'tasty-recipes-lite' ) . '</a>';

		return $html;
	}

	/**
	 * Workaround to make CPT pages act as tasty menu active items.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function handle_cpt_menu_item_classes() {
		global $menu, $submenu;

		if ( ! isset( $submenu['tasty'] ) ) {
			return;
		}

		foreach ( $menu as $cur_menu_key => $cur_menu ) {
			if ( 'tasty' !== $cur_menu[2] || 'tasty_recipe' !== get_current_screen()->post_type ) {
				continue;
			}

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$menu[ $cur_menu_key ][4] = 'wp-has-current-submenu';
		}

		foreach ( $submenu['tasty'] as $sub_key => $sub_item ) {
			if ( 'tasty-recipes' !== $sub_item[2] || 'tasty_recipe' !== get_current_screen()->post_type ) {
				continue;
			}

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu['tasty'][ $sub_key ][4] = 'current';
		}
	}

	/**
	 * Show license notice in tasty recipes custom post type page.
	 * 
	 * @since x.x
	 *
	 * @param bool   $show_notice Show notice status.
	 * @param object $plugin Current plugin.
	 *
	 * @return bool|mixed
	 */
	public static function show_notice_in_recipes_cpt( $show_notice, $plugin ) {
		if ( $show_notice || 'tasty-recipes' !== $plugin->get_plugin_slug() ) {
			return $show_notice;
		}

		return 'edit-tasty_recipe' === get_current_screen()->id;
	}

	/**
	 * Make recipes custom post type pages act as ours.
	 * 
	 * @since x.x
	 *
	 * @param bool $is_ours Current page is ours or not.
	 *
	 * @return bool
	 */
	public static function make_recipes_cpt_menu_item_ours( $is_ours ) {
		return self::is_current_page( 'tasty_recipe' ) || $is_ours;
	}

	/**
	 * Check if current page is the one mentioned.
	 *
	 * @param string $id Page id to check over (about, settings, tasty_recipe).
	 *
	 * @return bool
	 */
	public static function is_current_page( $id ) {
		if ( 'tasty_recipe' === $id ) {
			return isset( get_current_screen()->id ) && in_array( get_current_screen()->id, array( 'edit-tasty_recipe', 'tasty_recipe' ), true );
		}

		return 'wp-tasty_page_tasty-recipes' === get_current_screen()->id
					&& Vars::get_param( 'tab', 'sanitize_text_field', 'design' ) === $id;
	}

	/**
	 * Modifications to the main query.
	 * 
	 * @since x.x
	 *
	 * @param WP_Query $query WP_Query object.
	 */
	public static function action_pre_get_posts( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'tasty_recipe' !== $query->get( 'post_type' ) ) {
			return;
		}

		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	}
}

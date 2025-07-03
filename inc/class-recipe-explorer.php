<?php
/**
 * Manages interactions with the recipe explorer page.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Vars;
use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes\Ratings;

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
		add_action( 'manage_posts_columns', array( __CLASS__, 'action_manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'action_manage_posts_custom_column' ), 10, 2 );
		add_filter( 'manage_edit-tasty_recipe_sortable_columns', array( __CLASS__, 'action_manage_sortable_columns' ) );
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
	 * @param object $query WP_Query object.
	 * 
	 * @return void
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

	/**
	 * Registers the new custom columns for Tasty Recipes.
	 * 
	 * @since x.x
	 *
	 * @param array $columns Existing column names.
	 *
	 * @return array
	 */
	public static function action_manage_posts_columns( $columns ) {
		global $post_type;

		if ( 'tasty_recipe' !== $post_type ) {
			return $columns;
		}
	
		$columns['title']        = esc_html__( 'Recipe Title', 'tasty-recipes-lite' );
		$columns['rating']       = esc_html__( 'Rating', 'tasty-recipes-lite' );
		$columns['recipe-post']  = esc_html__( "Recipe's post", 'tasty-recipes-lite' );
		$columns['last-updated'] = esc_html__( 'Last updated', 'tasty-recipes-lite' );
		$columns['columns']      = esc_html__( 'Columns', 'tasty-recipes-lite' );

		if ( isset( $columns['date'] ) ) {
			unset( $columns['date'] );
		}

		return $columns;
	}

	/**
	 * Renders custom column content for the tasty_recipe post type.
	 * 
	 * @since x.x
	 *
	 * @param string $column_name Name of the column.
	 * @param int    $recipe_id   ID of the recipe being displayed.
	 *
	 * @return void
	 */
	public static function action_manage_posts_custom_column( $column_name, $recipe_id ) {
		global $post_type;

		if ( 'tasty_recipe' !== $post_type ) {
			return;
		}

		$post           = get_post( $recipe_id );
		$parent_post_id = get_post_meta( $recipe_id, '_tasty_recipe_parents', true );
		$parent_post    = get_post( $parent_post_id ) ?? false;
	
		switch ( $column_name ) {
			case 'rating':
				self::render_rating( $recipe_id );
				break;
			case 'recipe-post':
				echo '<a href="' . esc_url( get_edit_post_link( $parent_post_id ) ) . '">' . esc_html( $parent_post->post_title ) . '</a>';
				break;
			
			case 'last-updated':
				echo esc_html( $post->post_modified );
				break;
			
			case 'columns':
				echo '';
				break;
		}
	}

	/**
	 * Render the rating for the recipe.
	 * 
	 * @since x.x
	 *
	 * @param int $recipe_id The ID of the recipe.
	 *
	 * @return void
	 */
	public static function render_rating( $recipe_id ) {
		wp_enqueue_style(
			'tasty-recipes-ratings',
			plugin_dir_url( TASTY_RECIPES_LITE_FILE ) . 'assets/dist/ratings.css',
			array(),
			TASTY_RECIPES_LITE_VERSION
		);

		$recipe = Recipe::get_by_id( $recipe_id );

		$allowed_html = array(
			'span'   => array(
				'class'        => array(),
				'data-tr-clip' => array(),
				'data-rating'  => array(),
			),
			'svg'    => array(
				'class'       => array(),
				'fill'        => array(),
				'height'      => array(),
				'width'       => array(),
				'viewbox'     => array(),
				'xmlns'       => array(),
				'xmlns:xlink' => array(),
				'style'       => array(),
				'aria-hidden' => array(),
			),
			'use'    => array(
				'href' => array(),
			),
			'symbol' => array(
				'id'      => array(),
				'viewbox' => array(),
			),
			'path'   => array(
				'd' => array(),
			),
		);

		echo wp_kses( Ratings::get_rendered_rating( $recipe->get_average_rating() ), $allowed_html );
	}

	/**
	 * Remove all sortable columns.
	 * 
	 * @since x.x
	 *
	 * @return array
	 */
	public static function action_manage_sortable_columns() {
		return array();
	}
}

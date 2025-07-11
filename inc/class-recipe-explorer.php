<?php
/**
 * Manages interactions with the recipe explorer page.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Vars;
use Tasty\Framework\Admin\JSMessageBox;
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
		$html .= '<a class="tasty-button tasty-button-pink" id="tasty-create-recipe" href="#">' . __( 'Create Recipe', 'tasty-recipes-lite' ) . '</a>';

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
				if ( empty( $parent_post_id ) ) {
					echo '';
					break;
				}

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

	/**
	 * Register the rest api endpoints.
	 * 
	 * @since x.x
	 *
	 * @return void
	 */
	public static function action_rest_api_init() {
		register_rest_route(
			'tasty-recipes/v1',
			'/recipe-explorer/create',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_post_endpoint' ),
				'permission_callback' => array( __CLASS__, 'api_check_permission' ),
			)
		);

		register_rest_route(
			'tasty-recipes/v1',
			'/recipe-explorer/embed',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'embed_in_post_endpoint' ),
				'permission_callback' => array( __CLASS__, 'api_check_permission' ),
			)
		);
	}

	/**
	 * Create a post and append the recipe to it.
	 * 
	 * @since x.x
	 * 
	 * @param object $request The request object.
	 *
	 * @return void
	 */
	public static function create_post_endpoint( $request ) {
		$params = $request->get_params();
		$title  = $params['title'] ?? '';
		$recipe = Recipe::get_by_id( $params['recipe_id'] );

		if ( ! $recipe ) {
			wp_send_json_error( __( 'Recipe not found', 'tasty-recipes-lite' ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => self::generate_recipe_content( $params['recipe_id'] ),
				'post_status'  => 'draft',
				'post_type'    => 'post',
			)
		);

		$js_messagebox = JSMessageBox::instance()->init( get_current_user_id() );

		if ( is_wp_error( $post_id ) ) {
			$message  = '<h3>' . __( 'Error!', 'tasty-recipes-lite' ) . '</h3>';
			$message .= '<p>' . $post_id->get_error_message() . '</p>';

			$js_messagebox->add( 'error', $message );

			wp_send_json_error();
		}

		$recipe_title = ! empty( $recipe->get_title() ) ? $recipe->get_title() : __( 'The', 'tasty-recipes-lite' );

		$message  = '<h3>' . __( 'Post created!', 'tasty-recipes-lite' ) . '</h3>';
		$message .= '<p>' . $recipe_title . __( ' recipe has been embedded successfully.', 'tasty-recipes-lite' ) . '</p>';
		$message .= '<p><a style="color: #ED4996;" href="' . get_edit_post_link( $post_id ) . '">' . __( 'Go to Post', 'tasty-recipes-lite' ) . '</a></p>';
		
		$js_messagebox->add( 'success', $message );

		wp_send_json_success();
	}

	/**
	 * Embed the recipe in the post.
	 * 
	 * @since x.x
	 * 
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return void
	 */
	public static function embed_in_post_endpoint( $request ) {
		$params        = $request->get_params();
		$post_id       = $params['post_id'] ?? '';
		$js_messagebox = JSMessageBox::instance()->init( get_current_user_id() );

		if ( empty( $post_id ) ) {
			$js_messagebox->add( 'error', __( 'You need to select a post to embed the recipe in.', 'tasty-recipes-lite' ) );

			wp_send_json_error();
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			$js_messagebox->add( 'error', __( 'You do not have permission to edit this post.', 'tasty-recipes-lite' ) );

			wp_send_json_error();
		}

		$recipe = Recipe::get_by_id( $params['recipe_id'] );

		if ( ! $recipe ) {
			$js_messagebox->add( 'error', __( 'Recipe not found', 'tasty-recipes-lite' ) );

			wp_send_json_error();
		}
		
		$post = get_post( $post_id );

		if ( ! $post ) {
			$js_messagebox->add( 'error', __( 'Post not found', 'tasty-recipes-lite' ) );

			wp_send_json_error();
		}

		$recipe_content  = self::generate_recipe_content( $params['recipe_id'] );
		$updated_content = $post->post_content . "\n\n" . $recipe_content;

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $updated_content,
			)
		);

		$recipe_title = ! empty( $recipe->get_title() ) ? $recipe->get_title() : __( 'The recipe', 'tasty-recipes-lite' );

		$message  = '<h3>' . __( 'Recipe embedded!', 'tasty-recipes-lite' ) . '</h3>';
		$message .= '<p>' . $recipe_title . __( ' has been embedded successfully.', 'tasty-recipes-lite' ) . '</p>';
		$message .= '<p><a style="color: #ED4996;" href="' . get_edit_post_link( $post_id ) . '">' . __( 'Go to Recipe', 'tasty-recipes-lite' ) . '</a></p>';
		
		$js_messagebox->add( 'success', $message );

		wp_send_json_success();
	}

	/**
	 * Generate the recipe content.
	 * 
	 * @since x.x
	 *
	 * @param int $recipe_id The ID of the recipe.
	 *
	 * @return string
	 */
	private static function generate_recipe_content( $recipe_id ) {
		$content = '[wp-tasty id="' . $recipe_id . '"]';

		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( 'post' ) ) {
			$content = '<!-- wp:wp-tasty/tasty-recipe {"id":' . $recipe_id . ',"lastUpdated":' . time() . '} /-->';
		}

		return $content;
	}

	/**
	 * Check if the user has permission to access the api.
	 * 
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function api_check_permission() {
		return current_user_can( 'edit_posts' );
	}
}

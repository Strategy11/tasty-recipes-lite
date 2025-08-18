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
use Tasty_Recipes\Block_Editor;

/**
 * Manages interactions with the recipe explorer page.
 */
class Recipe_Explorer {
	/**
	 * Filter fields.
	 * 
	 * @var array
	 */
	private static $filter_fields = array( 'rating', 'cuisine', 'method', 'category', 'author', 'diet' );

	/**
	 * Load hooks.
	 * 
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'admin_head', array( __CLASS__, 'handle_cpt_menu_item_classes' ) );
		add_action( 'tasty_tabs_buttons', array( __CLASS__, 'filter_tab_buttons' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'action_pre_get_posts' ) );
		add_action( 'manage_posts_columns', array( __CLASS__, 'action_manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'action_manage_posts_custom_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'add_filters_button' ) );

		// We intercept the user meta because the default_hidden_columns isn't always called.
		add_filter( 'get_user_option_manageedit-tasty_recipecolumnshidden', array( __CLASS__, 'set_default_hidden_columns' ) );

		add_filter( 'manage_edit-tasty_recipe_sortable_columns', array( __CLASS__, 'action_manage_sortable_columns' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 10, 2 );
		add_filter( 'tasty_framework_admin_page_ours', array( __CLASS__, 'make_recipes_cpt_menu_item_ours' ) );
		add_filter( 'tasty_framework_show_license_notice', array( __CLASS__, 'show_notice_in_recipes_cpt' ), 10, 2 );
		add_filter( 'pre_get_posts', array( __CLASS__, 'filter_recipes_by_params' ), 10, 1 );
		add_filter( 'disable_months_dropdown', array( __CLASS__, 'disable_months_dropdown' ), 10, 2 );
		add_action( 'untrashed_post', array( __CLASS__, 'restore_recipe_as_published' ) );

		add_action( 'save_post_tasty_recipe', array( __CLASS__, 'clear_filter_cache' ) );
		add_action( 'deleted_post', array( __CLASS__, 'clear_filter_cache' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'clear_filter_cache' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'clear_filter_cache' ) );
	}

	/**
	 * Check if any filters are applied.
	 * 
	 * @since x.x
	 * 
	 * @return bool
	 */
	private static function has_any_filters_applied() {
		foreach ( self::$filter_fields as $field ) {
			if ( Utils::get_param( $field ) ) {
				return true;
			}
		}

		return false;
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
		global $post_type, $wp_query;

		if ( 'tasty_recipe' !== $post_type ) {
			return $html;
		}

		if (
			'edit' !== get_current_screen()->base ||
			( ! self::has_any_filters_applied() && ! $wp_query->have_posts() )
		) {
			return '';
		}

		$html  = '<p class="tasty-tabs-buttons">';
		$html .= '<a class="tasty-button tasty-button-pink" id="tasty-create-recipe" href="#">' . esc_html__( 'Create Recipe', 'tasty-recipes-lite' ) . '</a>';

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

		if ( Utils::get_param( 'orderby' ) ) {
			$query->set( 'orderby', Utils::get_param( 'orderby' ) );
		}

		if ( Utils::get_param( 'order' ) ) {
			$query->set( 'order', Utils::get_param( 'order' ) );
		}
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

		// Hidden columns by default.
		$columns['cuisine']  = esc_html__( 'Cuisine', 'tasty-recipes-lite' );
		$columns['method']   = esc_html__( 'Cooking Method', 'tasty-recipes-lite' );
		$columns['category'] = esc_html__( 'Category', 'tasty-recipes-lite' );
		$columns['diet']     = esc_html__( 'Diet', 'tasty-recipes-lite' );
		$columns['author']   = esc_html__( 'Author', 'tasty-recipes-lite' );

		$columns['columns'] = esc_html__( 'Columns', 'tasty-recipes-lite' );

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
				if ( ! $parent_post_id || ! $parent_post ) {
					break;
				}

				$post_title = ! empty( $parent_post->post_title ) ? $parent_post->post_title : __( '(no title)', 'tasty-recipes-lite' );

				echo '<a href="' . esc_url( get_edit_post_link( $parent_post_id ) ) . '">' . esc_html( $post_title ) . '</a>';
				break;
			
			case 'last-updated':
				echo esc_html( $post->post_modified );
				break;
			
			case 'columns':
				break;
				
			// Handle hidden columns content.
			case 'cuisine':
				$cuisine = get_post_meta( $recipe_id, 'cuisine', true );
				echo esc_html( ! empty( $cuisine ) ? $cuisine : '' );
				break;
				
			case 'method':
				$method = get_post_meta( $recipe_id, 'method', true );
				echo esc_html( ! empty( $method ) ? $method : '' );
				break;
				
			case 'category':
				$category = get_post_meta( $recipe_id, 'category', true );
				echo esc_html( ! empty( $category ) ? $category : '' );
				break;
				
			case 'diet':
				$diet = get_post_meta( $recipe_id, 'diet', true );
				echo esc_html( ! empty( $diet ) ? $diet : '' );
				break;
				
			case 'author':
				$author_id = $post->post_author;
				$author    = get_userdata( (int) $author_id );
				echo esc_html( $author ? $author->display_name : '—' );
				break;
		}
	}

	/**
	 * Set default hidden columns for new users or when no preference is saved.
	 * 
	 * @since x.x
	 *
	 * @param mixed $result The user option value.
	 *
	 * @return mixed
	 */
	public static function set_default_hidden_columns( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		$result = array(
			'cuisine'  => 'cuisine',
			'method'   => 'method',
			'category' => 'category',
			'diet'     => 'diet',
			'author'   => 'author',
		);
		
		return $result;
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

		$recipe       = Recipe::get_by_id( $recipe_id );
		$allowed_html = Utils::get_allowed_html();

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
		return array(
			'title'        => 'title',
			'rating'       => 'rating',
			'recipe-post'  => 'recipe-post',
			'last-updated' => 'last-updated',
			'cuisine'      => 'cuisine',
			'method'       => 'method',
			'category'     => 'category',
			'diet'         => 'diet',
			'author'       => 'author',
		);
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
			'/recipe-explorer/recipe/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'retrieve_recipe_endpoint' ),
				'permission_callback' => array( __CLASS__, 'api_check_permission' ),
			)
		);
		register_rest_route(
			'tasty-recipes/v1',
			'/recipe-explorer/delete/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_recipe_endpoint' ),
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

		$message  = '<h3>' . esc_html__( 'Post created!', 'tasty-recipes-lite' ) . '</h3>';
		$message .= '<p>';
		$message .= sprintf(
			/* translators: %s: recipe title */
			__( '%s recipe has been embedded successfully.', 'tasty-recipes-lite' ),
			esc_html( $recipe_title )
		);
		$message .= '</p>';
		$message .= '<p><a style="color: #ED4996;" href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . esc_html__( 'Go to Post', 'tasty-recipes-lite' ) . '</a></p>';
		
		$js_messagebox->add( 'success', $message );

		wp_send_json_success();
	}

	/**
	 * Retrieve a recipe.
	 * 
	 * @since x.x
	 * 
	 * @param object $request The request object.
	 * 
	 * @return void
	 */
	public static function retrieve_recipe_endpoint( $request ) {
		$params = $request->get_params();
		$recipe = Recipe::get_by_id( $params['id'] );

		if ( ! $recipe ) {
			wp_send_json_error( __( 'Recipe not found', 'tasty-recipes-lite' ) );
		}

		wp_send_json_success( $recipe->to_json() );
	}

	/**
	 * Delete a recipe.
	 * 
	 * @since x.x
	 * 
	 * @param object $request The request object.
	 * 
	 * @return void
	 */
	public static function delete_recipe_endpoint( $request ) {
		$params = $request->get_params();
		$recipe = Recipe::get_by_id( $params['id'] );

		$js_messagebox = JSMessageBox::instance()->init( get_current_user_id() );

		if ( ! $recipe ) {
			$js_messagebox->add( 'error', __( 'Recipe not found', 'tasty-recipes-lite' ) );

			wp_send_json_error();
		}

		wp_delete_post( $params['id'], true );

		$recipe_title = ! empty( $recipe->get_title() ) ? $recipe->get_title() : __( 'The recipe', 'tasty-recipes-lite' );

		$message  = '<h3>' . esc_html__( 'Recipe deleted!', 'tasty-recipes-lite' ) . '</h3>';
		$message .= '<p>';
		$message .= sprintf(
			/* translators: %s: recipe title */
			__( '%s has been deleted successfully.', 'tasty-recipes-lite' ),
			esc_html( $recipe_title )
		);
		$message .= '</p>';
		
		$js_messagebox->add( 'success', $message );

		wp_send_json_success();
	}

	/**
	 * Embed the recipe in the post.
	 * 
	 * @since x.x
	 * 
	 * @param object $request The request object.
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

		$message  = '<h3>' . esc_html__( 'Recipe embedded!', 'tasty-recipes-lite' ) . '</h3>';
		$message .= '<p>';
		$message .= sprintf(
			/* translators: %s: recipe title */
			__( '%s recipe has been embedded successfully.', 'tasty-recipes-lite' ),
			esc_html( $recipe_title )
		);
		$message .= '</p>';
		$message .= '<p><a style="color: #ED4996;" href="' . get_edit_post_link( $post_id ) . '">' . esc_html__( 'Go to Recipe', 'tasty-recipes-lite' ) . '</a></p>';
		
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
		$recipe = Recipe::get_by_id( $recipe_id );

		$content = '[wp-tasty id="' . $recipe_id . '"]';

		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( 'post' ) ) {
			$content = Block_Editor::get_block_for_recipe( $recipe );
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
		$nonce = Utils::server_param( 'HTTP_X_WP_NONCE' );

		return current_user_can( 'edit_posts' ) && wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Filter the post row actions.
	 * 
	 * @since x.x
	 * 
	 * @param array  $actions The actions array.
	 * @param object $post The post object.
	 * 
	 * @return array
	 */
	public static function filter_post_row_actions( $actions, $post ) {
		if ( 'tasty_recipe' !== $post->post_type ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );

		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = '<a href="#" data-recipe-id="' . $post->ID . '" class="tasty-recipes-edit-recipe">' . esc_html__( 'Edit', 'tasty-recipes-lite' ) . '</a>';
		}

		$parent_post_id = get_post_meta( $post->ID, '_tasty_recipe_parents', true );

		if ( isset( $actions['trash'] ) && $parent_post_id && get_post( $parent_post_id ) ) {
			$button  = '<a class="tasty-recipes-no-delete submitdelete" href="#" data-post-url="' . esc_url( get_edit_post_link( $parent_post_id ) ) . '">';
			$button .= esc_html__( 'Trash', 'tasty-recipes-lite' );
			$button .= '</a>';

			$actions['trash'] = $button;
		}

		if ( isset( $actions['delete'] ) ) {
			$button  = '<a class="tasty-recipes-permanent-delete submitdelete" href="#" data-post-id="' . $post->ID . '" data-name="' . esc_attr( $post->post_title ) . '">';
			$button .= esc_html__( 'Delete Permanently', 'tasty-recipes-lite' );
			$button .= '</a>';

			$actions['delete'] = $button;
		}

		return $actions;
	}

	/**
	 * Add the filters button.
	 * 
	 * @since x.x
	 * 
	 * @return void
	 */
	public static function add_filters_button() {
		if ( 'tasty_recipe' !== get_current_screen()->post_type ) {
			return;
		}
		?>
			<div id="tasty-recipes-filters-button-container">
				<button class="button" id="tasty-recipes-filters-button">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none">
						<?php // phpcs:ignore ?>
						<path fill="#353547" d="M3.958 5.833a.625.625 0 1 0 0 1.25v-1.25Zm12.083 1.25a.625.625 0 1 0 0-1.25v1.25Zm-12.083 0h12.083v-1.25H3.958v1.25ZM5.625 9.167a.625.625 0 0 0 0 1.25v-1.25Zm8.75 1.25a.625.625 0 1 0 0-1.25v1.25Zm-8.75 0h8.75v-1.25h-8.75v1.25ZM7.292 12.5a.625.625 0 1 0 0 1.25V12.5Zm5.417 1.25a.625.625 0 0 0 0-1.25v1.25Zm-5.417 0h5.417V12.5H7.292v1.25Z"/>
					</svg>
					<?php esc_html_e( 'Filters', 'tasty-recipes-lite' ); ?>
					<?php self::maybe_show_filter_count(); ?>
				</button>
			</div>
		<?php
	}

	/**
	 * Maybe show the filter count.
	 * 
	 * @since x.x
	 * 
	 * @return void
	 */
	private static function maybe_show_filter_count() {
		$filter_count = 0;

		foreach ( self::$filter_fields as $field ) {
			if ( Utils::get_param( $field ) ) {
				++$filter_count;
			}
		}

		if ( $filter_count > 0 ) {
			echo '<span class="tasty-recipes-filters-active-count">' . esc_html( number_format_i18n( $filter_count ) ) . '</span>';
		}
	}

	/**
	 * Get the unique filter values.
	 * 
	 * @since x.x
	 * 
	 * @param string $field The field to get the unique values for.
	 * 
	 * @return array
	 */
	public static function get_unique_filter_values( $field ) {
		$cache_key = 'tasty_recipes_unique_filter_values_' . $field;

		if ( wp_cache_get( $cache_key, 'tasty_recipes' ) ) {
			return wp_cache_get( $cache_key, 'tasty_recipes' );
		}

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value 
			FROM {$wpdb->postmeta} pm 
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.post_type = %s 
			AND p.post_status = 'publish' 
			AND pm.meta_key = %s 
			AND pm.meta_value != ''",
			'tasty_recipe',
			$field
		);

		// phpcs:ignore
		$values = $wpdb->get_col( $query );

		wp_cache_set( $cache_key, $values, 'tasty_recipes', 3600 );

		return $values;
	}

	/**
	 * Filter the recipes by params.
	 * 
	 * @since x.x
	 * 
	 * @param \WP_Query $query The query object.
	 * 
	 * @return \WP_Query
	 */
	public static function filter_recipes_by_params( $query ) {
		global $post_type, $pagenow;

		if ( 'tasty_recipe' !== $post_type || 'edit.php' !== $pagenow ) {
			return $query;
		}

		$meta_queries = array();
		$relation     = 'AND';

		if ( Utils::get_param( 'rating' ) ) {
			$rating_filter = Utils::get_param( 'rating' );
			
			// We run this custom query because sometimes the ratings are floats and we should round them to the nearest integer.
			add_filter(
				'posts_clauses',
				function ( $clauses ) use ( $rating_filter ) {
					global $wpdb;
				
					$clauses['join']  .= " INNER JOIN {$wpdb->postmeta} rating_meta ON ({$wpdb->posts}.ID = rating_meta.post_id AND rating_meta.meta_key = 'average_rating')";
					$clauses['where'] .= $wpdb->prepare( ' AND ROUND(CAST(rating_meta.meta_value AS DECIMAL(3,2))) = %d', $rating_filter );
				
					return $clauses;
				}
			);
		}

		if ( Utils::get_param( 'cuisine' ) ) {
			$meta_queries[] = array(
				'key'     => 'cuisine',
				'value'   => Utils::get_param( 'cuisine' ),
				'compare' => '=',
			);
		}

		if ( Utils::get_param( 'method' ) ) {
			$meta_queries[] = array(
				'key'     => 'method',
				'value'   => Utils::get_param( 'method' ),
				'compare' => '=',
			);
		}

		if ( Utils::get_param( 'category' ) ) {
			$meta_queries[] = array(
				'key'     => 'category',
				'value'   => Utils::get_param( 'category' ),
				'compare' => '=',
			);
		}

		if ( Utils::get_param( 'diet' ) ) {
			$meta_queries[] = array(
				'key'     => 'diet',
				'value'   => Utils::get_param( 'diet' ),
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_queries ) ) {
			$meta_queries['relation'] = $relation;
			$query->set( 'meta_query', $meta_queries );
		}

		if ( Utils::get_param( 'author' ) ) {
			$query->set(
				'author',
				Utils::get_param( 'author' )
			);
		}

		return $query;
	}

	/**
	 * Clear filter cache when a tasty_recipe post is created or updated.
	 * 
	 * @since x.x
	 * 
	 * @return void
	 */
	public static function clear_filter_cache() {
		global $post_type;

		if ( 'tasty_recipe' !== $post_type ) {
			return;
		}
		
		foreach ( self::$filter_fields as $field ) {
			$cache_key = 'tasty_recipes_unique_filter_values_' . $field;

			wp_cache_delete( $cache_key, 'tasty_recipes' );
		}
	}

	/**
	 * Disable the months dropdown.
	 * 
	 * @since x.x
	 * 
	 * @param bool   $disable Whether to disable the months dropdown.
	 * @param string $post_type The post type.
	 * 
	 * @return bool
	 */
	public static function disable_months_dropdown( $disable, $post_type ) {
		if ( 'tasty_recipe' === $post_type ) {
			return true;
		}

		return $disable;
	}

	/**
	 * Restore tasty_recipe posts as published instead of draft.
	 * 
	 * @since x.x
	 * 
	 * @param int $post_id The post ID that was restored.
	 * 
	 * @return void
	 */
	public static function restore_recipe_as_published( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post || 'tasty_recipe' !== $post->post_type ) {
			return;
		}

		if ( 'draft' === $post->post_status ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				)
			);
		}
	}
}

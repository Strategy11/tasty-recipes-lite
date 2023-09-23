<?php
/**
 * Customizations to the WordPress editor experience.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Objects\Recipe;
use WP_Post;

/**
 * Customizations to the WordPress editor experience.
 */
class Editor {

	/**
	 * Get message notice classes.
	 *
	 * @param array $message Message array.
	 *
	 * @since 3.8
	 *
	 * @return string[]
	 */
	private static function get_message_notice_classes( $message ) {
		$classes = array(
			'notice',
			'notice-' . $message['type'],
		);

		if ( ! empty( $message['dismissible'] ) && $message['dismissible'] ) {
			$classes[] = 'is-dismissible';
		}

		if ( ! empty( $message['class'] ) ) {
			$classes[] = $message['class'];
		}

		return $classes;
	}

	/**
	 * Renders a notice when a convertable recipe is detected.
	 */
	public static function action_admin_notices() {
		$screen  = get_current_screen();
		$post_id = Utils::get_param( 'post', 'intval', 0 );
		if ( 'post' !== $screen->base || empty( $post_id ) ) {
			return;
		}

		foreach ( self::get_converter_messages( $post_id ) as $message ) {
			if ( 'success' === $message['type'] ) {
				echo '<div class="notice updated is-dismissible"><p>' . esc_html( $message['content'] ) . '</p></div>';
			} else {
				echo '<div id="' . esc_attr( $message['id'] ) . '" class="' . esc_attr( implode( ' ', self::get_message_notice_classes( $message ) ) ) . '" data-dismiss_action="' . ( ! empty( $message['dismiss_action'] ) ? esc_url( $message['dismiss_action'] ) : '' ) . '">';
				echo '<p>' . esc_html( $message['content'] ) . '</p>';
				echo '<p>';
				foreach ( $message['actions'] as $action ) {
					echo '<a class="button ' . ( ! empty( $action['class'] ) ? esc_attr( $action['class'] ) : '' ) . '" href="' . esc_url( $action['url'] ) . '" data-name="' . ( ! empty( $action['name'] ) ? esc_attr( $action['name'] ) : '' ) . '">' . esc_html( $action['label'] ) . '</a>&nbsp;';
				}
				echo '</p>';
				echo '</div>';

			}
		}
	}

	/**
	 * Get global ignored conversion types.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	private static function get_ignored_conversion_types() {
		return (array) get_option( 'tasty_recipes_ignore_convert_types', array() );
	}

	/**
	 * Check if this post is ignored for this converter.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $converter_type Converter key to check.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	private static function is_ignored_post_for_conversion_type( $post_id, $converter_type ) {
		$option = self::get_ignored_conversion_types();
		return (
				! empty( $option )
				&&
				isset( $option[ $converter_type ] )
			)
			||
			get_post_meta( $post_id, 'tasty_recipes_ignore_convert_' . $converter_type, true );
	}

	/**
	 * Get convert Url.
	 *
	 * @param WP_Post $post Current post object.
	 * @param string  $converter_type Converter key.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private static function get_convert_url( $post, $converter_type ) {
		$query_args = array(
			'action'          => 'tasty_recipes_convert_recipe',
			'nonce'           => wp_create_nonce( 'tasty_recipes_convert_recipe' . $post->ID ),
			'post_id'         => $post->ID,
			'type'            => $converter_type,
			'is_block_editor' => Utils::is_block_editor( $post ), // Convert to a Tasty Recipes block when this post has blocks.
		);
		return add_query_arg( $query_args, admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Get dismiss convert Url for this post only.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $converter_type Converter key.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private static function get_dismiss_convert_url_for_post( $post_id, $converter_type ) {
		return add_query_arg(
			array(
				'action'  => 'tasty_recipes_ignore_convert',
				'nonce'   => wp_create_nonce( 'tasty_recipes_convert_recipe' . $post_id ),
				'post_id' => $post_id,
				'type'    => $converter_type,
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Get dismiss convert Url globally for this converter.
	 *
	 * @param string $converter_type Converter key.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private static function get_dismiss_convert_url_for_type( $converter_type ) {
		return add_query_arg(
			array(
				'action'      => 'tasty_recipes_ignore_type_convert',
				'type'        => $converter_type,
				'_ajax_nonce' => wp_create_nonce( 'tasty_recipes_ignore_type_convert' ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Get revert dismissing convert Url globally for this converter or for this post only.
	 *
	 * @param string $converter_type Converter key.
	 * @param int    $post_id Current post ID.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private static function get_revert_ignore_url( $converter_type, $post_id ) {
		return add_query_arg(
			array(
				'action'   => 'tasty_recipes_revert_ignore_type_convert',
				'_wpnonce' => wp_create_nonce( 'tasty_recipes_revert_ignore_type_convert' ),
				'type'     => $converter_type,
				'post_id'  => $post_id,
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Gets the converter messages for a given post.
	 *
	 * @param integer $post_id ID for the post to inspect.
	 * @return array
	 */
	public static function get_converter_messages( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$messages = array();
		$content  = $post->post_content;
		// Correct Windows-style line endings.
		$content = str_replace( "\r\n", "\n", $content );
		$content = str_replace( "\r", "\n", $content );
		foreach ( Tasty_Recipes::get_converters() as $key => $config ) {
			$class = $config['class'];
			if ( ! $class::get_existing_to_convert( $content ) ) {
				continue;
			}
			if ( self::is_ignored_post_for_conversion_type( $post_id, $key ) ) {
				continue;
			}

			$messages[] = array(
				'id'             => 'tasty_recipes_convert_' . $key,
				'class'          => 'tasty_recipes_convert_notice',
				'type'           => 'info',
				'content'        => sprintf(
					// translators: Converter label.
					esc_html__( '%s recipe detected. Would you like to convert it to Tasty Recipes?', 'tasty-recipes' ),
					$config['label']
				),
				'actions'        => array(
					array(
						'url'   => self::get_convert_url( $post, $key ),
						'label' => esc_html__( 'Convert', 'tasty-recipes' ),
						'class' => 'button-primary',
						'name'  => 'convert',
					),
					array(
						'url'   => self::get_dismiss_convert_url_for_type( $key ),
						'label' => sprintf(
							// translators: %s name of a plugin.
							esc_html__( 'Do not ask again for %s recipes', 'tasty-recipes' ),
							$config['label']
						),
						'name'  => 'dismiss_converter',
					),
				),
				'dismissible'    => true,
				'dismiss_action' => self::get_dismiss_convert_url_for_post( $post_id, $key ),
			);
		}

		if ( ! empty( $messages ) && ! wp_revisions_enabled( $post ) ) {
			$messages[] = array(
				'id'      => 'tasty_recipes_convert_revisions_warning',
				'type'    => 'warning',
				'content' => esc_html__( 'Warning: Post revisions are not enabled, please back up post content before converting to Tasty Recipes.', 'tasty-recipes' ),
				'actions' => array(
					array(
						'url'   => 'https://www.wptasty.com/convert-single',
						'label' => __( 'Learn more', 'tasty-recipes' ),
						'name'  => 'learn_more',
					),
				),
			);
		}

		$get_tasty_recipes_message = Utils::get_param( 'tasty_recipes_message' );

		if ( ! empty( $get_tasty_recipes_message ) && 'converted_recipe_success' === $get_tasty_recipes_message ) {
			$messages[] = array(
				'type'        => 'success',
				'content'     => esc_html__( 'Successfully migrated the recipe to Tasty Recipes. Enjoy!', 'tasty-recipes' ),
				'dismissible' => true,
			);
		}
		return $messages;
	}

	/**
	 * Whether or not the Tasty Recipes button should display.
	 *
	 * @return boolean
	 */
	public static function is_tasty_recipes_editor_view() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return true;
		}
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return false;
		}
		return ! in_array( $screen->base, array( 'widgets', 'customize' ), true );
	}

	/**
	 * Registers an 'Add Recipe' button to Media Buttons.
	 *
	 * @param string $editor_id Editor instance to be displayed.
	 */
	public static function action_media_buttons( $editor_id ) {

		if ( ! self::is_tasty_recipes_editor_view() ) {
			return;
		}
		if ( ! apply_filters( 'tasty_recipes_add_media_button', true, $editor_id ) ) {
			return;
		}
		?>
		<button type="button" class="button tasty-recipes-add-recipe" data-editor="<?php echo esc_attr( $editor_id ); ?>">
			<span class="wp-media-buttons-icon dashicons dashicons-carrot"></span>
			<?php esc_html_e( 'Add Recipe', 'tasty-recipes' ); ?>
		</button>
		<?php
	}

	/**
	 * Adds a 'Add rel="nofollow" to link' checkbox to the WordPress link editor.
	 */
	public static function action_after_wp_tiny_mce() {
		?>
		<script>
			var originalWpLink;
			// Ensure both TinyMCE, underscores and wpLink are initialized.
			if ( typeof tinymce !== 'undefined' && typeof _ !== 'undefined' && typeof wpLink !== 'undefined' ) {
				// Ensure the #link-options div is present, because it's where we're appending our checkbox.
				if ( tinymce.$('#link-options').length ) {
					// Append our checkbox HTML to the #link-options div, which is already present in the DOM.
					tinymce.$('#link-options').append(<?php echo wp_json_encode( '<div class="link-nofollow"><label><span></span><input type="checkbox" id="wp-link-nofollow" /> ' . esc_html__( 'Add rel="nofollow" to link', 'tasty-recipes' ) . '</label></div>' ); ?>);
					// Clone the original wpLink object so we retain access to some functions.
					originalWpLink = _.clone( wpLink );
					wpLink.addRelNofollow = tinymce.$('#wp-link-nofollow');
					// Override the original wpLink object to include our custom functions.
					wpLink = _.extend( wpLink, {
						/*
						* Fetch attributes for the generated link based on
						* the link editor form properties.
						*
						* In this case, we're calling the original getAttrs()
						* function, and then including our own behavior.
						*/
						getAttrs: function() {
							var attrs = originalWpLink.getAttrs();
							attrs.rel = wpLink.addRelNofollow.prop( 'checked' ) ? 'nofollow' : '';
							return attrs;
						},
						/*
						* Build the link's HTML based on attrs when inserting
						* into the text editor.
						*
						* In this case, we're completely overriding the existing
						* function.
						*/
						buildHtml: function( attrs ) {
							var html = '<a href="' + attrs.href + '"';

							if ( attrs.target ) {
								html += ' target="' + attrs.target + '"';
							}
							if ( attrs.rel ) {
								html += ' rel="' + attrs.rel + '"';
							}
							return html + '>';
						},
						/*
						* Set the value of our checkbox based on the presence
						* of the rel='nofollow' link attribute.
						*
						* In this case, we're calling the original mceRefresh()
						* function, then including our own behavior
						*/
						mceRefresh: function( searchStr, text ) {
							originalWpLink.mceRefresh( searchStr, text );
							var editor = window.tinymce.get( window.wpActiveEditor )
							if ( typeof editor !== 'undefined' && ! editor.isHidden() ) {
								var linkNode = editor.dom.getParent( editor.selection.getNode(), 'a[href]' );
								if ( linkNode ) {
									wpLink.addRelNofollow.prop( 'checked', -1 !== editor.dom.getAttrib( linkNode, 'rel' ).indexOf('nofollow') );
								}
							}
						}
					});
				}
			}
			if ( typeof jQuery !== 'undefined' ) {
				// Remove EasyRecipe's nofollow UI if it's present.
				jQuery(document).ready(setTimeout(function(){
					if ( jQuery('#link-options #link-nofollow-checkbox').length ) {
						jQuery('#link-options #link-nofollow-checkbox').closest('label').remove();
					}
				}, 1));
			}
		</script>
		<?php
	}

	/**
	 * Validate the inputs before conversion and return the post to convert.
	 *
	 * @return array|WP_Post|null
	 */
	private static function get_current_post_for_conversion_ajax() {
		$nonce   = Utils::get_param( 'nonce' );
		$post_id = Utils::get_param( 'post_id', 'intval', 0 );
		if ( empty( $post_id ) || empty( $nonce ) ) {
			wp_die( esc_html__( "You don't have permission to do this.", 'tasty-recipes' ) );
		}

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			wp_die( esc_html__( "You don't have permission to do this.", 'tasty-recipes' ) );
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if (
			! current_user_can( $post_type_object->cap->edit_post, $post->ID )
			||
			! wp_verify_nonce( $nonce, 'tasty_recipes_convert_recipe' . $post->ID )
		) {
			wp_die( esc_html__( "You don't have permission to do this.", 'tasty-recipes' ) );
		}

		return $post;
	}

	/**
	 * Handles an AJAX request to convert a recipe.
	 */
	public static function handle_wp_ajax_convert_recipe() {
		$post = self::get_current_post_for_conversion_ajax();

		$recipe     = false;
		$type       = ! empty( Utils::get_param( 'is_block_editor' ) ) ? 'block' : 'shortcode';
		$converters = Tasty_Recipes::get_converters();
		$get_type   = Utils::get_param( 'type' );
		if ( isset( $converters[ $get_type ] ) ) {
			$class  = $converters[ $get_type ]['class'];
			$recipe = $class::convert_post( $post->ID, $type );
		}

		if ( ! $recipe ) {
			wp_die( esc_html__( 'Unknown error converting recipe.', 'tasty-recipes' ) );
		}

		$redirect_url = add_query_arg( 'tasty_recipes_message', 'converted_recipe_success', get_edit_post_link( $post->ID, 'raw' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handles an AJAX request to ignore a recipe conversion.
	 */
	public static function handle_wp_ajax_ignore_convert() {
		$post = self::get_current_post_for_conversion_ajax();

		update_post_meta( $post->ID, 'tasty_recipes_ignore_convert_' . Utils::get_param( 'type', 'sanitize_key' ), true );
		status_header( 200 );
		echo 'Done';
		exit;
	}

	/**
	 * Validate the variable and exit with json error when it is empty.
	 *
	 * @param string $type Variable you need to check.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	private static function validate_type_input_and_exit( $type = '' ) {
		if ( ! empty( $type ) ) {
			return;
		}

		wp_send_json_error(
			array(
				'message' => esc_html__( 'Invalid inputs', 'tasty-recipes' ),
			)
		);
	}

	/**
	 * Handles the AJAX request to ignore conversion for a converter globally.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_ignore_type_convert() {
		check_ajax_referer( 'tasty_recipes_ignore_type_convert' );

		$converter_type = Utils::sanitize_get_key( 'type' );

		self::validate_type_input_and_exit( $converter_type );
		Admin::validate_ajax_capability();

		$option                    = self::get_ignored_conversion_types();
		$option[ $converter_type ] = true;
		update_option( 'tasty_recipes_ignore_convert_types', $option, false );
		wp_send_json_success();
	}

	/**
	 * Handles the AJAX request to revert ignoring conversion for a converter globally.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_revert_ignore_type_convert() {
		check_ajax_referer( 'tasty_recipes_revert_ignore_type_convert' );

		$converter_type = Utils::sanitize_get_key( 'type' );

		self::validate_type_input_and_exit( $converter_type );
		Admin::validate_ajax_capability();

		$option = self::get_ignored_conversion_types();

		unset( $option[ $converter_type ] );
		update_option( 'tasty_recipes_ignore_convert_types', $option, false );

		$post_id = Utils::get_param( 'post_id', 'intval' );

		if ( ! empty( $post_id ) ) {
			delete_post_meta( $post_id, 'tasty_recipes_ignore_convert_' . $converter_type );
		}

		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	/**
	 * Handles an AJAX request to render a shortcode with its data.
	 */
	public static function handle_wp_ajax_parse_shortcode() {
		$shortcode = Utils::post_param( 'shortcode' );
		$post_id   = Utils::post_param( 'post_id', 'intval', 0 );
		$nonce     = Utils::post_param( 'nonce' );

		if ( empty( $shortcode ) || empty( $post_id ) || empty( $nonce ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			wp_send_json_error();
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if (
			! current_user_can( $post_type_object->cap->edit_post, $post->ID )
			||
			! wp_verify_nonce( $nonce, 'tasty_recipes_parse_shortcode' )
		) {
			wp_send_json_error();
		}

		self::render_shortcode_response( $shortcode, $post );
	}

	/**
	 * Handles an AJAX request to modify a recipe.
	 */
	public static function handle_wp_ajax_modify_recipe() {
		$post_recipe = Utils::post_param( 'recipe', null );
		$post_id     = Utils::post_param( 'post_id', 'intval', 0 );
		$nonce       = Utils::post_param( 'nonce' );

		if ( empty( $post_recipe ) || empty( $post_id ) || empty( $nonce ) ) {
			wp_send_json_error();
		}

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			wp_send_json_error();
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if (
			! current_user_can( $post_type_object->cap->edit_post, $post->ID )
			||
			! wp_verify_nonce( $nonce, 'tasty_recipes_modify_recipe' )
		) {
			wp_send_json_error();
		}

		if ( ! empty( $post_recipe['id'] ) ) {
			$recipe = Recipe::get_by_id( (int) $post_recipe['id'] );
		} else {
			$recipe = Recipe::create();
		}

		if ( empty( $recipe ) ) {
			wp_send_json_error(
				array(
					'type'    => 'no-items',
					'message' => __( 'No recipe found.', 'tasty-recipes' ),
				)
			);
		}

		foreach ( Recipe::get_attributes() as $field => $meta ) {
			if ( ! isset( $post_recipe[ $field ] ) ) {
				continue;
			}

			$setter = "set_{$field}";

			$sanitize_callback = ! empty( $meta['sanitize_callback'] ) ? $meta['sanitize_callback'] : 'sanitize_text_field';
			if ( 'intval' === $sanitize_callback && empty( $post_recipe[ $field ] ) ) {
				$data = '';
			} else {
				$data = wp_unslash( $sanitize_callback( $post_recipe[ $field ] ) );
			}

			/**
			 * Permit modification of data before saving it to the database.
			 *
			 * @param mixed $data   Data to be saved.
			 * @param string $field Field to be saved.
			 */
			$data = apply_filters( 'tasty_recipes_pre_save_editor_data', $data, $field );

			if ( method_exists( $recipe, $setter ) ) {
				$recipe->$setter( $data );
				// Allow saving of any nutrition attributes that have been added.
			} elseif ( in_array( $field, Recipe::get_nutrition_attribute_keys(), true ) ) {
				update_post_meta( $recipe->get_id(), $field, $data );
			}
		}

		/**
		 * Allows Tasty Links to save its own data.
		 *
		 * @param array  $data   Recipe data to save (not sanitized).
		 * @param object $recipe Recipe instance.
		 */
		do_action( 'tasty_recipes_after_save_editor_data', $post_recipe, $recipe );

		$image_id = $recipe->get_image_id();
		if ( $image_id ) {
			Content_Model::generate_attachment_image_sizes( $image_id );
		}

		$shortcode = Shortcodes::get_shortcode_for_recipe( $recipe );
		self::render_shortcode_response( $shortcode, $post );
	}

	/**
	 * Renders a shortcode response with its corresponding JSON.
	 *
	 * @param string $shortcode Shortcode string to render.
	 * @param object $post      Post containing the shortcode.
	 */
	private static function render_shortcode_response( $shortcode, $post ) {
		setup_postdata( $post );
		$parsed = do_shortcode( $shortcode );

		if ( empty( $parsed ) ) {
			wp_send_json_error(
				array(
					'type'    => 'no-items',
					'message' => __( 'No recipe found.', 'tasty-recipes' ),
				)
			);
		}

		$recipe_json = Tasty_Recipes::get_instance()->recipe_json;
		/**
		 * Permit modification of the recipe JSON before it's returned.
		 *
		 * @param array $recipe_json Existing recipe JSON blob.
		 */
		$recipe_json = apply_filters( 'tasty_recipes_shortcode_response_recipe_json', $recipe_json );
		// These fields are stored without paragraph tags,
		// so they need to be added for TinyMCE compat.
		foreach ( array(
			'description',
			'ingredients',
			'instructions',
			'notes',
		) as $field ) {
			$recipe_json[ $field ] = wpautop( $recipe_json[ $field ] );
		}
		wp_send_json_success(
			array(
				'head'      => '',
				'body'      => $parsed,
				'recipe'    => $recipe_json,
				'shortcode' => $shortcode,
			)
		);
	}

	/**
	 * Get recipe IDs from post if found.
	 *
	 * @param WP_Post $post Post object to extract recipes from.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	private static function get_recipe_ids_from_post( $post ) {
		if ( ! self::post_has_recipes( $post ) ) {
			return array();
		}

		return array_unique( Tasty_Recipes::get_recipe_ids_from_content( $post->post_content ) );
	}

	/**
	 * Check if post has recipes.
	 *
	 * @param WP_Post $post Post object to check.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	private static function post_has_recipes( $post ) {
		return has_shortcode( $post->post_content, Shortcodes::RECIPE_SHORTCODE ) || Block_Editor::post_has_recipes( $post );
	}

	/**
	 * Assign parent post ID to recipes.
	 *
	 * @param int   $post_ID Parent post ID.
	 * @param int[] $recipe_ids Array of recipe IDs to be assigned.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	private static function assign_post_to_recipes( $post_ID, $recipe_ids ) {
		foreach ( $recipe_ids as $recipe_id ) {
			$recipe = Recipe::get_by_id( $recipe_id );
			if ( ! $recipe ) {
				continue;
			}
			$recipe->add_parent_post( $post_ID );
		}
	}

	/**
	 * Remove parent post ID from recipes.
	 *
	 * @param int   $post_ID Parent post ID.
	 * @param int[] $recipe_ids Array of recipe IDs to be deassigned.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	private static function deassign_post_to_recipes( $post_ID, $recipe_ids ) {
		foreach ( $recipe_ids as $recipe_id ) {
			$recipe = Recipe::get_by_id( $recipe_id );
			if ( ! $recipe ) {
				continue;
			}
			$recipe->remove_parent_post( $post_ID );
		}
	}

	/**
	 * Handle adding new post with recipes.
	 *
	 * @param int     $post_ID Created Post ID.
	 * @param WP_Post $post Created Post object.
	 * @param bool    $update True if updated post otherwise false for new posts.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function handle_add_new_post( $post_ID, $post, $update ) {
		if ( $update ) {
			return;
		}

		if ( 'tasty_recipe' === get_post_type( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
			return;
		}

		self::assign_post_to_recipes(
			$post_ID,
			self::get_recipe_ids_from_post( $post )
		);
	}

	/**
	 * Handle edit post with recipes.
	 *
	 * @param int     $post_ID Updated Post ID.
	 * @param WP_Post $post_after After update post object.
	 * @param WP_Post $post_before Before update post object.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function handle_edit_post( $post_ID, $post_after, $post_before ) {
		if ( 'tasty_recipe' === get_post_type( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
			return;
		}

		if ( $post_after->post_content === $post_before->post_content ) {
			return;
		}

		$recipes_before  = self::get_recipe_ids_from_post( $post_before );
		$recipes_after   = self::get_recipe_ids_from_post( $post_after );
		$removed_recipes = array_diff( $recipes_before, $recipes_after );

		self::assign_post_to_recipes( $post_ID, $recipes_after );
		self::deassign_post_to_recipes( $post_ID, $removed_recipes );
	}

	/**
	 * Get array of dismissed converters to be used in post metabox.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	public static function get_dismissed_converters() {
		global $post;

		$post_id = $post ? $post->ID : Utils::get_param( 'post', 'intval', 0 );

		if ( empty( $post_id ) ) {
			return array();
		}

		$all_converters = Tasty_Recipes::get_converters();
		$output         = array();

		foreach ( $all_converters as $converter_key => $converter ) {
			if ( ! self::is_ignored_post_for_conversion_type( $post_id, $converter_key ) ) {
				continue;
			}

			$output[ $converter_key ] = array(
				'title' => $converter['label'],
				'url'   => self::get_revert_ignore_url( $converter_key, $post_id ),
			);
		}

		return $output;
	}

}

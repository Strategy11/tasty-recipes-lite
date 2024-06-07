<?php
/**
 * Manages Classic editor metabox.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite;

use TastyRecipesLiteMain;

/**
 * Manages Classic editor metabox.
 */
class MetaBox {
	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @var MetaBox
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return MetaBox
	 */
	public static function get_instance() {
		if ( null !== self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();
		self::$instance->add_hooks();
		return self::$instance;
	}

	/**
	 * Subscribe hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_hooks() {
		add_action( 'load-post.php', array( $this, 'meta_box_init' ) );
	}

	/**
	 * Gets current post type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_current_post_type() {
		global $post;

		if ( $post && $post->post_type ) {
			return $post->post_type;
		}

		global $typenow;

		if ( $typenow ) {
			return $typenow;
		}

		global $current_screen;

		if ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}

		return Utils::request_param( 'post_type', 'sanitize_key', '' );
	}

	/**
	 * Initialize metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function meta_box_init() {
		$post_type = $this->get_current_post_type();
		if ( empty( $post_type ) ) {
			return;
		}

		if ( ! is_post_type_viewable( $post_type ) ) {
			return;
		}

		if ( Utils::is_block_editor() ) {
			return;
		}

		if ( empty( Editor::get_dismissed_converters() ) ) {
			return;
		}

		// For Classic editor.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
	}

	/**
	 * Add the metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function create_meta_box() {
		add_meta_box(
			'tasty-recipes-metabox',
			'Tasty Recipes',
			array( $this, 'print_metabox_html' ),
			null,
			'side',
			'low'
		);
	}

	/**
	 * Print metabox HTML.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print_metabox_html() {
		TastyRecipesLiteMain::echo_template_part(
			'parts/metabox',
			array(
				'dismissed_converters' => Editor::get_dismissed_converters(),
			)
		);
	}

}

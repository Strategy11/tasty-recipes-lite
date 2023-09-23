<?php
/**
 * Tasty Recipes widget for Elementor.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Integrations\Elementor;

use Tasty\Recipes\Lite\Shortcodes;
use Elementor\Widget_Base;

/**
 * Tasty Recipes widget for Elementor.
 */
class RecipeWidget extends Widget_Base {

	/**
	 * Gets the widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tasty-recipe';
	}

	/**
	 * Gets the widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'Tasty Recipe';
	}

	/**
	 * Gets the widget's icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dashicons dashicons-carrot';
	}

	/**
	 * Categories for the widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Registers necessary controls.
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'tasty-recipes' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'recipe_id',
			array(
				'type' => 'tasty-recipe-control',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the shortcode on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! empty( $settings['recipe_id'] ) ) {
			echo do_shortcode( '[' . Shortcodes::RECIPE_SHORTCODE . ' id="' . (int) $settings['recipe_id'] . '"]' );
		} else {
			echo '<p>' . esc_html__( 'No Tasty Recipe created yet. Click \'Edit Recipe\' in the widget controls to create one.', 'tasty-recipes' ) . '</p>';
		}
	}

	/**
	 * Renders the shortcode for saving to the database.
	 */
	public function render_plain_content() {
		$settings = $this->get_settings_for_display();
		echo '[' . esc_html( Shortcodes::RECIPE_SHORTCODE ) . ' id="' . (int) $settings['recipe_id'] . '"]';
	}

	/**
	 * Renders the shortcode in the editor.
	 */
	protected function _content_template() {} // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

}

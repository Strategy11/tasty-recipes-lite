<?php
/**
 * What the AI features work on, per recipe type.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

defined( 'ABSPATH' ) || exit;

/**
 * Describes the content the AI may write or read, so other recipe types can
 * use the AI features without a food-only prompt.
 *
 * @since x.x
 */
final class Subject {

	/**
	 * The subject for a recipe type.
	 *
	 * @since x.x
	 *
	 * @param string $recipe_type Recipe type, such as "recipe".
	 *
	 * @return array{noun: string, topic: string, off_topic_message: string, not_found_message: string}
	 */
	public static function get( $recipe_type = 'recipe' ) {
		$defaults = array(
			'noun'              => 'recipe',
			'topic'             => 'food: recipes, dishes, ingredients, cooking, baking, drinks, serving or storing food',
			'off_topic_message' => __( 'The AI can only help with recipes and cooking. Try asking for a change to this recipe.', 'tasty-recipes-lite' ),
			'not_found_message' => __( 'We couldn’t find a recipe in that photo. Try a clearer photo of the whole recipe.', 'tasty-recipes-lite' ),
		);

		/**
		 * Filters what the AI features work on for a recipe type.
		 *
		 * @since x.x
		 *
		 * @param array  $subject {
		 *
		 *     @type string $noun              What one item is called, such as "recipe" (in English, for the model).
		 *     @type string $topic             What requests may be about (in English, for the model).
		 *     @type string $off_topic_message Shown when a request is about something else.
		 *     @type string $not_found_message Shown when a photo doesn't contain one.
		 * }
		 *
		 * @param string $recipe_type Recipe type.
		 */
		$subject = apply_filters( 'tasty_recipes_ai_subject', $defaults, sanitize_key( (string) $recipe_type ) );

		foreach ( $defaults as $key => $default ) {
			$value = is_array( $subject ) && isset( $subject[ $key ] ) && is_string( $subject[ $key ] )
				? trim( wp_strip_all_tags( $subject[ $key ] ) )
				: '';

			// One line each, so a filtered value can't add lines to the instructions.
			$defaults[ $key ] = '' !== $value ? (string) preg_replace( '/\s+/', ' ', $value ) : $default;
		}

		return $defaults;
	}
}

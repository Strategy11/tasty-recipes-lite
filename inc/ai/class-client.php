<?php
/**
 * Sends prompts to the connected AI provider.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Entry point for features that need the site's AI model.
 *
 * @since x.x
 */
final class Client {

	/**
	 * Whether a provider, model and readable key are all set up.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_ready() {
		$status = Connection::get_public_status();

		return $status['connected'] && '' !== $status['model'];
	}

	/**
	 * Generates text with the connected provider and model.
	 *
	 * @since x.x
	 *
	 * @param string $prompt The user prompt.
	 * @param array  $args {
	 *     Optional. Request options.
	 *
	 *     @type string $system     System instructions.
	 *     @type int    $max_tokens Maximum output tokens. Default set per provider.
	 *     @type array  $images     Images for the model to read, each
	 *                              array{ mime: string, data: string } (base64).
	 *     @type string $feature    Short ID of the calling feature, passed to filters.
	 * }
	 *
	 * @return string|WP_Error The generated text, or an error safe to show to users.
	 */
	public static function generate( $prompt, $args = array() ) {
		$provider = Connection::get_connected_provider();
		$model    = Connection::get_model();

		if ( ! $provider ) {
			return new WP_Error( 'tasty_recipes_ai_not_connected', __( 'Connect an AI provider in Tasty Recipes settings first.', 'tasty-recipes-lite' ) );
		}

		if ( '' === $model ) {
			return new WP_Error( 'tasty_recipes_ai_no_model', __( 'Choose a model in Tasty Recipes settings first.', 'tasty-recipes-lite' ) );
		}

		/**
		 * Filters whether an AI request may run.
		 *
		 * Return a WP_Error to block it, for example to add a usage limit.
		 *
		 * @since x.x
		 *
		 * @param true|WP_Error $allowed Whether the request may run.
		 * @param array         $args    Request options.
		 */
		$allowed = apply_filters( 'tasty_recipes_ai_before_request', true, $args );
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$key = Connection::get_api_key();
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$text = $provider->generate(
			$key,
			$model,
			(string) $prompt,
			array_intersect_key( (array) $args, array_flip( array( 'system', 'max_tokens', 'images' ) ) )
		);
		Vault::wipe( $key );

		/**
		 * Fires after an AI request finishes. Receives no key or prompt text.
		 *
		 * @since x.x
		 *
		 * @param string          $provider Provider ID.
		 * @param string          $model    Model ID.
		 * @param bool            $success  Whether text came back.
		 * @param string          $feature  Calling feature ID, if given.
		 */
		do_action(
			'tasty_recipes_ai_after_request',
			$provider->get_id(),
			$model,
			! is_wp_error( $text ),
			isset( $args['feature'] ) ? (string) $args['feature'] : ''
		);

		return $text;
	}
}

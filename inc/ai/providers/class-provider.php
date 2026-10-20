<?php
/**
 * Base class for AI providers.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * A provider adapter: validates keys, lists models and generates text over
 * the provider's HTTP API.
 *
 * Adapters only ever talk to their own fixed HTTPS endpoints. The API key is
 * passed in per call, sent in a header (never in a URL) and scrubbed from
 * any error text before it leaves the adapter.
 *
 * @since x.x
 */
abstract class Provider {

	/**
	 * Provider ID, used in storage and the REST API.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Provider name shown in the settings.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Where users create an API key.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	abstract public function get_credentials_url();

	/**
	 * Lists the models the key can use that read images and write text.
	 *
	 * Tasty Recipes features send images (recipe photos, scanned cards), so
	 * text-only models are left out. A successful call also proves the key works.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 *
	 * @return array<int, array{id: string, label: string}>|WP_Error
	 */
	abstract public function list_models( $api_key );

	/**
	 * Generates text.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 * @param string $model   Model ID.
	 * @param string $prompt  User prompt.
	 * @param array  $args {
	 *     Optional. Request options.
	 *
	 *     @type string $system     System instructions.
	 *     @type int    $max_tokens Maximum output tokens.
	 *     @type array  $images     Images to send with the prompt, each
	 *                              array{ mime: string, data: string } with
	 *                              base64 data.
	 * }
	 *
	 * @return string|WP_Error The generated text, or an error.
	 */
	abstract public function generate( $api_key, $model, $prompt, $args = array() );

	/**
	 * Name of the model line get_recommended_model() picks from, shown to users.
	 *
	 * @since x.x
	 *
	 * @return string For example "GPT Luna", or empty for no recommendation.
	 */
	public function get_model_family_label() {
		return '';
	}

	/**
	 * The model to preselect from the models the key can access: the newest
	 * one in the provider's fast, low-cost line.
	 *
	 * Only called when get_model_family_label() isn't empty.
	 *
	 * @since x.x
	 *
	 * @param array<int, array{id: string, label: string}> $models Available models.
	 *
	 * @return string Model ID, or an empty string when none fits.
	 */
	abstract public function get_recommended_model( $models );

	/**
	 * The ID with the highest version among IDs matching a pattern.
	 *
	 * The pattern's first capture group is the version, with parts separated
	 * by dots or dashes ("4.5", "4-5"). On a tie the shortest ID wins, so an
	 * alias ("claude-haiku-4-5") beats its dated snapshot.
	 *
	 * @since x.x
	 *
	 * @param string[] $ids     Model IDs.
	 * @param string   $pattern Regex with the version in group 1.
	 *
	 * @return string The newest ID, or an empty string.
	 */
	protected static function newest_matching( $ids, $pattern ) {
		$best         = '';
		$best_version = '';

		foreach ( $ids as $id ) {
			if ( ! preg_match( $pattern, $id, $match ) ) {
				continue;
			}

			$version = str_replace( '-', '.', $match[1] );
			$compare = '' === $best ? 1 : version_compare( $version, $best_version );

			if ( $compare > 0 || ( 0 === $compare && strlen( $id ) < strlen( $best ) ) ) {
				$best         = $id;
				$best_version = $version;
			}
		}

		return $best;
	}

	/**
	 * Name of the wp-config.php constant that can hold this provider's key.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_key_constant() {
		return 'TASTY_RECIPES_' . strtoupper( str_replace( '-', '_', $this->get_id() ) ) . '_API_KEY';
	}

	/**
	 * Whether a model ID is safe to send to this provider.
	 *
	 * @since x.x
	 *
	 * @param string $model Model ID.
	 *
	 * @return bool
	 */
	public function is_valid_model_id( $model ) {
		return is_string( $model ) && (bool) preg_match( '#^[A-Za-z0-9][A-Za-z0-9._:/@-]{0,199}$#', $model );
	}

	/**
	 * The images passed to generate(), keeping only well-formed ones.
	 *
	 * @since x.x
	 *
	 * @param array $args Request options.
	 *
	 * @return array<int, array{mime: string, data: string}>
	 */
	protected function get_images( $args ) {
		$images = array();

		foreach ( (array) ( $args['images'] ?? array() ) as $image ) {
			$mime = $image['mime'] ?? '';
			$data = $image['data'] ?? '';

			if ( is_string( $mime ) && preg_match( '#^image/(jpeg|png|webp|gif)$#', $mime ) && is_string( $data ) && '' !== $data ) {
				$images[] = array(
					'mime' => $mime,
					'data' => $data,
				);
			}
		}

		return $images;
	}

	/**
	 * Sends a JSON request to the provider.
	 *
	 * @since x.x
	 *
	 * @param string     $method  HTTP method.
	 * @param string     $url     Full HTTPS URL on the provider's API host.
	 * @param string     $api_key The API key, used only to scrub errors.
	 * @param array      $headers Request headers, including authentication.
	 * @param array|null $body    JSON body, or null for none.
	 *
	 * @return array|WP_Error Decoded JSON response, or an error.
	 */
	protected function request( $method, $url, $api_key, $headers, $body = null ) {
		$args = array(
			'method'      => $method,
			'headers'     => array_merge(
				array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				),
				$headers
			),
			/**
			 * Filters how long to wait for an AI provider to respond, in seconds.
			 *
			 * @since x.x
			 *
			 * @param int    $timeout  Timeout in seconds.
			 * @param string $provider Provider ID.
			 */
			'timeout'     => (int) apply_filters( 'tasty_recipes_ai_request_timeout', 60, $this->get_id() ),
			'redirection' => 0,
			'sslverify'   => true,
			'user-agent'  => 'TastyRecipes/' . TASTY_RECIPES_LITE_VERSION . '; WordPress',
		);

		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_safe_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'tasty_recipes_ai_unreachable',
				sprintf(
					/* translators: %s: AI provider name, e.g. OpenAI. */
					__( 'Could not reach %s. Please try again in a moment.', 'tasty-recipes-lite' ),
					$this->get_label()
				)
			);
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status < 200 || $status >= 300 ) {
			return $this->error_from_response( $status, is_array( $data ) ? $data : array(), $api_key );
		}

		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'tasty_recipes_ai_bad_response',
				sprintf(
					/* translators: %s: AI provider name. */
					__( '%s sent a response we could not read.', 'tasty-recipes-lite' ),
					$this->get_label()
				)
			);
		}

		return $data;
	}

	/**
	 * Turns an error response into a WP_Error that is safe to show.
	 *
	 * @since x.x
	 *
	 * @param int    $status  HTTP status.
	 * @param array  $data    Decoded error body.
	 * @param string $api_key The API key, removed from the message.
	 *
	 * @return WP_Error
	 */
	protected function error_from_response( $status, $data, $api_key ) {
		if ( 401 === $status || 403 === $status ) {
			return new WP_Error(
				'tasty_recipes_ai_invalid_key',
				sprintf(
					/* translators: %s: AI provider name. */
					__( '%s did not accept this API key. Check the key and try again.', 'tasty-recipes-lite' ),
					$this->get_label()
				),
				array( 'status' => 400 )
			);
		}

		if ( 429 === $status ) {
			return new WP_Error(
				'tasty_recipes_ai_rate_limited',
				sprintf(
					/* translators: %s: AI provider name. */
					__( '%s is rate limiting requests or the account is out of credit. Please try again later.', 'tasty-recipes-lite' ),
					$this->get_label()
				),
				array( 'status' => 429 )
			);
		}

		$message = $this->extract_error_message( $data );

		return new WP_Error(
			'tasty_recipes_ai_provider_error',
			sprintf(
				/* translators: 1: AI provider name, 2: error message from the provider. */
				__( '%1$s returned an error: %2$s', 'tasty-recipes-lite' ),
				$this->get_label(),
				'' !== $message ? self::scrub( $message, $api_key ) : (string) $status
			),
			array( 'status' => 502 )
		);
	}

	/**
	 * Reads the message out of a provider error body.
	 *
	 * @since x.x
	 *
	 * @param array $data Decoded error body.
	 *
	 * @return string
	 */
	protected function extract_error_message( $data ) {
		$message = $data['error']['message'] ?? '';

		return is_string( $message ) ? $message : '';
	}

	/**
	 * Removes the key (and anything key-like) from text and caps its length.
	 *
	 * @since x.x
	 *
	 * @param string $text    Text that may contain the key.
	 * @param string $api_key The API key.
	 *
	 * @return string
	 */
	public static function scrub( $text, $api_key = '' ) {
		$text = wp_strip_all_tags( (string) $text );

		if ( '' !== $api_key ) {
			$text = str_replace( $api_key, '[redacted]', $text );
		}

		// Long token-like strings (partial keys some providers echo back).
		$text = (string) preg_replace( '/\b[A-Za-z0-9_\-]{24,}\b/', '[redacted]', $text );

		return mb_substr( trim( $text ), 0, 300 );
	}
}

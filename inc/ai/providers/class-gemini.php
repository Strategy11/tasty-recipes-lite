<?php
/**
 * Google Gemini provider.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Gemini API adapter. The key goes in the x-goog-api-key header rather than
 * the ?key= query string, so it never ends up in URL logs.
 *
 * @since x.x
 */
class Gemini extends Provider {

	/**
	 * API base URL.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const API_BASE = 'https://generativelanguage.googleapis.com/v1beta';

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'gemini';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return 'Google Gemini';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_credentials_url() {
		return 'https://aistudio.google.com/api-keys';
	}

	/**
	 * Lists the models the key can use that read images and write text.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 *
	 * @return array<int, array{id: string, label: string}>|WP_Error
	 */
	public function list_models( $api_key ) {
		$data = $this->request( 'GET', self::API_BASE . '/models?pageSize=1000', $api_key, $this->auth_headers( $api_key ) );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$models = array();
		foreach ( (array) ( $data['models'] ?? array() ) as $model ) {
			$id      = preg_replace( '#^models/#', '', (string) ( $model['name'] ?? '' ) );
			$methods = (array) ( $model['supportedGenerationMethods'] ?? array() );

			// Gemini models read images; skip Gemma, embeddings, and the
			// speech, live-audio and image-generation variants.
			$reads_images = 0 === strpos( $id, 'gemini-' )
				&& ! preg_match( '/(tts|embedding|image|live|native-audio|audio)/', $id );

			if ( ! $this->is_valid_model_id( $id ) || ! in_array( 'generateContent', $methods, true ) || ! $reads_images ) {
				continue;
			}

			$models[] = array(
				'id'    => $id,
				'label' => is_string( $model['displayName'] ?? null ) ? $model['displayName'] : $id,
			);
		}

		return $models;
	}

	/**
	 * Name of the model line this provider uses.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_model_family_label() {
		return 'Gemini Flash';
	}

	/**
	 * Picks Google's always-current Flash alias, Gemini's fast, low-cost line,
	 * or the newest stable Flash model when the alias isn't listed.
	 *
	 * @since x.x
	 *
	 * @param array<int, array{id: string, label: string}> $models Available models.
	 *
	 * @return string
	 */
	public function get_recommended_model( $models ) {
		$ids = wp_list_pluck( $models, 'id' );

		if ( in_array( 'gemini-flash-latest', $ids, true ) ) {
			return 'gemini-flash-latest';
		}

		return self::newest_matching( $ids, '/^gemini-(\d+(?:\.\d+)?)-flash$/' );
	}

	/**
	 * Generates text.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 * @param string $model   Model ID.
	 * @param string $prompt  User prompt.
	 * @param array  $args    Request options: system, max_tokens.
	 *
	 * @return string|WP_Error
	 */
	public function generate( $api_key, $model, $prompt, $args = array() ) {
		if ( ! $this->is_valid_model_id( $model ) ) {
			return new WP_Error( 'tasty_recipes_ai_bad_model', __( 'Choose a model before using AI features.', 'tasty-recipes-lite' ) );
		}

		$parts = array();
		foreach ( $this->get_images( $args ) as $image ) {
			$parts[] = array(
				'inline_data' => array(
					'mime_type' => $image['mime'],
					'data'      => $image['data'],
				),
			);
		}
		$parts[] = array( 'text' => (string) $prompt );

		$body = array(
			'contents'         => array(
				array(
					'role'  => 'user',
					'parts' => $parts,
				),
			),
			'generationConfig' => array(
				'maxOutputTokens' => (int) ( $args['max_tokens'] ?? 4096 ),
			),
		);

		if ( ! empty( $args['system'] ) ) {
			$body['systemInstruction'] = array(
				'parts' => array( array( 'text' => (string) $args['system'] ) ),
			);
		}

		$url  = self::API_BASE . '/models/' . rawurlencode( $model ) . ':generateContent';
		$data = $this->request( 'POST', $url, $api_key, $this->auth_headers( $api_key ), $body );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( ! empty( $data['promptFeedback']['blockReason'] ) ) {
			return new WP_Error( 'tasty_recipes_ai_refused', __( 'The model declined to answer this request.', 'tasty-recipes-lite' ) );
		}

		$text = '';
		foreach ( (array) ( $data['candidates'][0]['content']['parts'] ?? array() ) as $part ) {
			if ( is_string( $part['text'] ?? null ) && empty( $part['thought'] ) ) {
				$text .= $part['text'];
			}
		}

		if ( '' === trim( $text ) ) {
			return new WP_Error( 'tasty_recipes_ai_empty', __( 'The model returned an empty response.', 'tasty-recipes-lite' ) );
		}

		return $text;
	}

	/**
	 * Turns an error response into a WP_Error that is safe to show.
	 *
	 * Gemini answers an invalid key with a 400 rather than a 401.
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
		$reasons = wp_list_pluck( (array) ( $data['error']['details'] ?? array() ), 'reason' );

		if ( in_array( 'API_KEY_INVALID', $reasons, true ) ) {
			$status = 401;
		}

		return parent::error_from_response( $status, $data, $api_key );
	}

	/**
	 * Authentication headers.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 *
	 * @return array
	 */
	private function auth_headers( $api_key ) {
		return array(
			'x-goog-api-key' => $api_key,
		);
	}
}

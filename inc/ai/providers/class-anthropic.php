<?php
/**
 * Anthropic provider.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Anthropic Messages API adapter.
 *
 * @since x.x
 */
class Anthropic extends Provider {

	/**
	 * API base URL.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const API_BASE = 'https://api.anthropic.com/v1';

	/**
	 * API version header value.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const API_VERSION = '2023-06-01';

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'anthropic';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return 'Anthropic';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_credentials_url() {
		return 'https://platform.claude.com/settings/keys';
	}

	/**
	 * Name of the model line this provider uses.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_model_family_label() {
		return 'Claude Haiku';
	}

	/**
	 * Picks the newest Claude Haiku model, Anthropic's fast, low-cost line.
	 *
	 * @since x.x
	 *
	 * @param array<int, array{id: string, label: string}> $models Available models.
	 *
	 * @return string
	 */
	public function get_recommended_model( $models ) {
		return self::newest_matching( wp_list_pluck( $models, 'id' ), '/^claude-haiku-(\d+(?:-\d{1,2})?)(?:-\d{8})?$/' );
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
	public function list_models(
		#[\SensitiveParameter]
		$api_key
	) {
		$data = $this->request( 'GET', self::API_BASE . '/models?limit=1000', $api_key, $this->auth_headers( $api_key ) );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$models = array();
		foreach ( (array) ( $data['data'] ?? array() ) as $model ) {
			$id = $model['id'] ?? '';

			// Only models that read images. Older API responses without
			// capabilities are kept: every current Claude model reads images.
			$reads_images = $model['capabilities']['image_input']['supported'] ?? true;

			if ( ! $this->is_valid_model_id( $id ) || ! $reads_images ) {
				continue;
			}

			$models[] = array(
				'id'    => $id,
				'label' => is_string( $model['display_name'] ?? null ) ? $model['display_name'] : $id,
			);
		}

		// The API lists newest first, which is the order worth keeping.
		return $models;
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
	public function generate(
		#[\SensitiveParameter]
		$api_key,
		$model,
		$prompt,
		$args = array()
	) {
		$content = array();
		foreach ( $this->get_images( $args ) as $image ) {
			$content[] = array(
				'type'   => 'image',
				'source' => array(
					'type'       => 'base64',
					'media_type' => $image['mime'],
					'data'       => $image['data'],
				),
			);
		}
		$content[] = array(
			'type' => 'text',
			'text' => (string) $prompt,
		);

		$body = array(
			'model'      => $model,
			'max_tokens' => (int) ( $args['max_tokens'] ?? 16000 ),
			'messages'   => array(
				array(
					'role'    => 'user',
					'content' => $content,
				),
			),
		);

		if ( ! empty( $args['system'] ) ) {
			$body['system'] = (string) $args['system'];
		}

		$data = $this->request( 'POST', self::API_BASE . '/messages', $api_key, $this->auth_headers( $api_key ), $body );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( 'refusal' === ( $data['stop_reason'] ?? '' ) ) {
			return new WP_Error( 'tasty_recipes_ai_refused', __( 'The model declined to answer this request.', 'tasty-recipes-lite' ) );
		}

		$text = '';
		foreach ( (array) ( $data['content'] ?? array() ) as $block ) {
			if ( 'text' === ( $block['type'] ?? '' ) && is_string( $block['text'] ?? null ) ) {
				$text .= $block['text'];
			}
		}

		if ( '' === trim( $text ) ) {
			return new WP_Error( 'tasty_recipes_ai_empty', __( 'The model returned an empty response.', 'tasty-recipes-lite' ) );
		}

		return $text;
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
	private function auth_headers(
		#[\SensitiveParameter]
		$api_key
	) {
		return array(
			'x-api-key'         => $api_key,
			'anthropic-version' => self::API_VERSION,
		);
	}
}

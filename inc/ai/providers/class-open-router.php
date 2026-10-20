<?php
/**
 * OpenRouter provider.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * OpenRouter adapter. OpenRouter speaks the OpenAI Chat Completions format
 * and routes to many model vendors with one key.
 *
 * @since x.x
 */
class Open_Router extends Open_AI {

	/**
	 * API base URL.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const API_BASE = 'https://openrouter.ai/api/v1';

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'openrouter';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return 'OpenRouter';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_credentials_url() {
		return 'https://openrouter.ai/settings/keys';
	}

	/**
	 * No model line: OpenRouter exists to let users choose, so nothing is
	 * preselected.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public function get_model_family_label() {
		return '';
	}

	/**
	 * Lists the models the key can use that read images and write text.
	 *
	 * OpenRouter's model list is public, so the key is checked separately first.
	 *
	 * @since x.x
	 *
	 * @param string $api_key The API key.
	 *
	 * @return array<int, array{id: string, label: string}>|WP_Error
	 */
	public function list_models( $api_key ) {
		$key_check = $this->request( 'GET', self::API_BASE . '/key', $api_key, $this->auth_headers( $api_key ) );

		if ( is_wp_error( $key_check ) ) {
			return $key_check;
		}

		$data = $this->request( 'GET', self::API_BASE . '/models', $api_key, $this->auth_headers( $api_key ) );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$models = array();
		foreach ( (array) ( $data['data'] ?? array() ) as $model ) {
			$id      = $model['id'] ?? '';
			$inputs  = (array) ( $model['architecture']['input_modalities'] ?? array() );
			$outputs = (array) ( $model['architecture']['output_modalities'] ?? array( 'text' ) );

			// Only models that read images and write text.
			if ( ! $this->is_valid_model_id( $id ) || ! in_array( 'image', $inputs, true ) || ! in_array( 'text', $outputs, true ) ) {
				continue;
			}

			$models[] = array(
				'id'    => $id,
				'label' => is_string( $model['name'] ?? null ) ? $model['name'] : $id,
			);
		}

		usort(
			$models,
			function ( $a, $b ) {
				return strcasecmp( $a['label'], $b['label'] );
			}
		);

		return $models;
	}

	/**
	 * Builds the chat request body.
	 *
	 * @since x.x
	 *
	 * @param string $model      Model ID.
	 * @param array  $messages   Chat messages.
	 * @param int    $max_tokens Maximum output tokens.
	 *
	 * @return array
	 */
	protected function chat_body( $model, $messages, $max_tokens ) {
		return array(
			'model'      => $model,
			'messages'   => $messages,
			'max_tokens' => $max_tokens,
		);
	}
}

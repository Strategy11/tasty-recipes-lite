<?php
/**
 * OpenAI provider.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI\Providers;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * OpenAI Chat Completions adapter.
 *
 * @since x.x
 */
class Open_AI extends Provider {

	/**
	 * API base URL.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const API_BASE = 'https://api.openai.com/v1';

	/**
	 * Model families that read images and write text. OpenAI's model list
	 * doesn't report capabilities, so families are matched by name: GPT-4o,
	 * GPT-4 Turbo, GPT-4.1, GPT-4.5, GPT-5 and later (including Luna and Sol),
	 * and the o1, o3 and o4-mini reasoning models.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const VISION_PATTERN = '/^(chatgpt-4o|gpt-4o|gpt-4-turbo|gpt-4\.[15]|gpt-(?:[5-9]|\d{2,})(?:[.-]|$)|o1(?!-mini|-preview)|o3(?!-mini)|o4-mini)/';

	/**
	 * Variants within those families that don't take images or don't return
	 * chat text (audio, realtime, search, transcription, coding and image
	 * generation models). Excluded when unsure.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const NON_VISION_PATTERN = '/(embedding|tts|whisper|dall-e|image|audio|realtime|transcribe|search|computer-use|codex|instruct|sora|moderation)/i';

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'openai';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label() {
		return 'OpenAI';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_credentials_url() {
		return 'https://platform.openai.com/api-keys';
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
		$data = $this->request( 'GET', static::API_BASE . '/models', $api_key, $this->auth_headers( $api_key ) );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$models = array();
		foreach ( (array) ( $data['data'] ?? array() ) as $model ) {
			$id = $model['id'] ?? '';
			if ( ! $this->is_valid_model_id( $id ) || ! self::reads_images( $id ) ) {
				continue;
			}

			$models[] = array(
				'id'    => $id,
				'label' => $id,
			);
		}

		usort(
			$models,
			function ( $a, $b ) {
				return strcmp( $a['id'], $b['id'] );
			}
		);

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
		return 'GPT Luna';
	}

	/**
	 * Whether an OpenAI model ID belongs to a family that reads images.
	 *
	 * @since x.x
	 *
	 * @param string $id Model ID.
	 *
	 * @return bool
	 */
	public static function reads_images( $id ) {
		return (bool) preg_match( self::VISION_PATTERN, $id ) && ! preg_match( self::NON_VISION_PATTERN, $id );
	}

	/**
	 * Picks the newest GPT Luna model, OpenAI's fast, low-cost line.
	 *
	 * @since x.x
	 *
	 * @param array<int, array{id: string, label: string}> $models Available models.
	 *
	 * @return string
	 */
	public function get_recommended_model( $models ) {
		return self::newest_matching( wp_list_pluck( $models, 'id' ), '/^gpt-(\d+(?:\.\d+)?)-luna(?:-\d{4}-\d{2}-\d{2})?$/' );
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
		$messages = array();

		if ( ! empty( $args['system'] ) ) {
			$messages[] = array(
				'role'    => 'system',
				'content' => (string) $args['system'],
			);
		}

		$content = array(
			array(
				'type' => 'text',
				'text' => (string) $prompt,
			),
		);
		foreach ( $this->get_images( $args ) as $image ) {
			$content[] = array(
				'type'      => 'image_url',
				'image_url' => array( 'url' => 'data:' . $image['mime'] . ';base64,' . $image['data'] ),
			);
		}

		$messages[] = array(
			'role'    => 'user',
			'content' => $content,
		);

		$data = $this->request(
			'POST',
			static::API_BASE . '/chat/completions',
			$api_key,
			$this->auth_headers( $api_key ),
			$this->chat_body( $model, $messages, (int) ( $args['max_tokens'] ?? 4096 ) )
		);

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$message = $data['choices'][0]['message'] ?? array();

		if ( ! empty( $message['refusal'] ) ) {
			return new WP_Error( 'tasty_recipes_ai_refused', __( 'The model declined to answer this request.', 'tasty-recipes-lite' ) );
		}

		$text = $message['content'] ?? '';

		if ( ! is_string( $text ) || '' === trim( $text ) ) {
			return new WP_Error( 'tasty_recipes_ai_empty', __( 'The model returned an empty response.', 'tasty-recipes-lite' ) );
		}

		return $text;
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
			'model'                 => $model,
			'messages'              => $messages,
			'max_completion_tokens' => $max_tokens,
		);
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
	protected function auth_headers(
		#[\SensitiveParameter]
		$api_key
	) {
		return array(
			'Authorization' => 'Bearer ' . $api_key,
		);
	}
}

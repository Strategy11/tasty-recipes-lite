<?php
/**
 * Test case for the AI provider classes.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Providers\Anthropic;
use Tasty_Recipes\AI\Providers\Gemini;
use Tasty_Recipes\AI\Providers\Open_AI;
use Tasty_Recipes\AI\Providers\Open_Router;
use Tasty_Recipes\AI\Providers\Provider;

/**
 * Test case for the AI provider classes.
 */
class Test_AI_Providers extends Tasty_Recipes_Testcase {

	/**
	 * Key sent in every test request.
	 *
	 * @var string
	 */
	const KEY = 'sk-test-0123456789abcdefghijklmnop';

	/**
	 * Canned responses, keyed by a part of the URL.
	 *
	 * @var array<string, array{0: int, 1: mixed}>
	 */
	private $responses = array();

	/**
	 * Requests the providers made.
	 *
	 * @var array
	 */
	private $requests = array();

	/**
	 * Answers provider requests from $responses.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->responses = array();
		$this->requests  = array();
		add_filter( 'pre_http_request', array( $this, 'mock_provider_response' ), 20, 3 );
	}

	/**
	 * Removes the HTTP mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_provider_response' ), 20 );

		parent::tearDown();
	}

	/**
	 * Returns the canned response for a provider URL.
	 *
	 * @param mixed  $pre  Existing value.
	 * @param array  $args Request arguments.
	 * @param string $url  Request URL.
	 *
	 * @return mixed
	 */
	public function mock_provider_response( $pre, $args, $url ) {
		foreach ( $this->responses as $match => $response ) {
			if ( false === strpos( $url, $match ) ) {
				continue;
			}

			$this->requests[] = array(
				'url'  => $url,
				'args' => $args,
				'body' => isset( $args['body'] ) ? json_decode( $args['body'], true ) : null,
			);

			if ( $response instanceof WP_Error ) {
				return $response;
			}

			return array(
				'headers'  => array(),
				'body'     => is_string( $response[1] ) ? $response[1] : wp_json_encode( $response[1] ),
				'response' => array(
					'code'    => $response[0],
					'message' => '',
				),
				'cookies'  => array(),
			);
		}

		return $pre;
	}

	/**
	 * OpenAI keeps only models that read images, sorted by ID.
	 *
	 * @return void
	 */
	public function test_open_ai_lists_vision_models() {
		$this->responses['api.openai.com/v1/models'] = array(
			200,
			array(
				'data' => array(
					array( 'id' => 'gpt-5.1-luna' ),
					array( 'id' => 'gpt-4o-mini' ),
					array( 'id' => 'gpt-4o' ),
					array( 'id' => 'text-embedding-3-large' ),
					array( 'id' => 'gpt-4o-audio-preview' ),
					array( 'id' => 'gpt-image-1' ),
					array( 'id' => 'o3-mini' ),
					array( 'id' => 'dall-e-3' ),
					array( 'id' => 'whisper-1' ),
					array( 'id' => 'gpt-3.5-turbo' ),
					array( 'id' => 'bad id with spaces' ),
				),
			),
		);

		$models = ( new Open_AI() )->list_models( self::KEY );

		$this->assertSame( array( 'gpt-4o', 'gpt-4o-mini', 'gpt-5.1-luna' ), wp_list_pluck( $models, 'id' ) );
		$this->assertSame( 'Bearer ' . self::KEY, $this->requests[0]['args']['headers']['Authorization'] );
		$this->assertSame( 0, $this->requests[0]['args']['redirection'] );
	}

	/**
	 * OpenAI's vision check, model by model.
	 *
	 * @dataProvider data_open_ai_models
	 *
	 * @param string $id       Model ID.
	 * @param bool   $expected Whether it reads images.
	 *
	 * @return void
	 */
	public function test_open_ai_reads_images( $id, $expected ) {
		$this->assertSame( $expected, Open_AI::reads_images( $id ) );
	}

	/**
	 * Model IDs and whether they read images.
	 *
	 * @return array
	 */
	public function data_open_ai_models() {
		return array(
			array( 'gpt-4o', true ),
			array( 'chatgpt-4o-latest', true ),
			array( 'gpt-4-turbo', true ),
			array( 'gpt-4.1-mini', true ),
			array( 'gpt-5', true ),
			array( 'gpt-5-mini', true ),
			array( 'gpt-5.1-luna-2026-01-01', true ),
			array( 'gpt-10', true ),
			array( 'o1', true ),
			array( 'o3', true ),
			array( 'o4-mini', true ),
			array( 'o1-mini', false ),
			array( 'o1-preview', false ),
			array( 'o3-mini', false ),
			array( 'gpt-4', false ),
			array( 'gpt-3.5-turbo', false ),
			array( 'gpt-4o-transcribe', false ),
			array( 'gpt-4o-realtime-preview', false ),
			array( 'gpt-4o-search-preview', false ),
			array( 'gpt-5-codex', false ),
			array( 'gpt-image-1', false ),
			array( 'omni-moderation-latest', false ),
		);
	}

	/**
	 * Each provider recommends the newest model in its line.
	 *
	 * @dataProvider data_recommended_models
	 *
	 * @param Provider $provider Provider.
	 * @param array    $ids      Model IDs.
	 * @param string   $expected Recommended ID.
	 *
	 * @return void
	 */
	public function test_recommended_model( $provider, $ids, $expected ) {
		$models = array_map(
			static function ( $id ) {
				return array(
					'id'    => $id,
					'label' => $id,
				);
			},
			$ids
		);

		$this->assertSame( $expected, $provider->get_recommended_model( $models ) );
	}

	/**
	 * Model lists and the model each provider should pick.
	 *
	 * @return array
	 */
	public function data_recommended_models() {
		return array(
			'openai newest luna'        => array( new Open_AI(), array( 'gpt-5-luna', 'gpt-5.1-luna', 'gpt-4o' ), 'gpt-5.1-luna' ),
			'openai prefers alias'      => array( new Open_AI(), array( 'gpt-5.1-luna-2026-01-01', 'gpt-5.1-luna' ), 'gpt-5.1-luna' ),
			'openai none'               => array( new Open_AI(), array( 'gpt-4o' ), '' ),
			'anthropic newest haiku'    => array( new Anthropic(), array( 'claude-haiku-4-5-20251001', 'claude-3-5-haiku-20241022', 'claude-sonnet-4-5' ), 'claude-haiku-4-5-20251001' ),
			'anthropic major beats minor' => array( new Anthropic(), array( 'claude-haiku-4-5', 'claude-haiku-5' ), 'claude-haiku-5' ),
			'gemini latest alias'       => array( new Gemini(), array( 'gemini-2.5-flash', 'gemini-flash-latest' ), 'gemini-flash-latest' ),
			'gemini newest flash'       => array( new Gemini(), array( 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro' ), 'gemini-2.5-flash' ),
			'openrouter has no default' => array( new Open_Router(), array( 'openai/gpt-5.1-luna' ), '' ),
		);
	}

	/**
	 * Anthropic drops models that say they can't read images.
	 *
	 * @return void
	 */
	public function test_anthropic_lists_vision_models() {
		$this->responses['api.anthropic.com/v1/models'] = array(
			200,
			array(
				'data' => array(
					array(
						'id'           => 'claude-haiku-4-5-20251001',
						'display_name' => 'Claude Haiku 4.5',
					),
					array(
						'id'           => 'claude-text-only',
						'display_name' => 'Text only',
						'capabilities' => array( 'image_input' => array( 'supported' => false ) ),
					),
				),
			),
		);

		$models = ( new Anthropic() )->list_models( self::KEY );

		$this->assertSame(
			array(
				array(
					'id'    => 'claude-haiku-4-5-20251001',
					'label' => 'Claude Haiku 4.5',
				),
			),
			$models
		);
		$this->assertSame( self::KEY, $this->requests[0]['args']['headers']['x-api-key'] );
	}

	/**
	 * Gemini keeps generateContent Gemini models without audio or image output.
	 *
	 * @return void
	 */
	public function test_gemini_lists_vision_models() {
		$this->responses['generativelanguage.googleapis.com'] = array(
			200,
			array(
				'models' => array(
					array(
						'name'                       => 'models/gemini-2.5-flash',
						'displayName'                => 'Gemini 2.5 Flash',
						'supportedGenerationMethods' => array( 'generateContent' ),
					),
					array(
						'name'                       => 'models/gemini-2.5-flash-preview-tts',
						'supportedGenerationMethods' => array( 'generateContent' ),
					),
					array(
						'name'                       => 'models/gemini-2.5-flash-image',
						'supportedGenerationMethods' => array( 'generateContent' ),
					),
					array(
						'name'                       => 'models/gemma-3-27b-it',
						'supportedGenerationMethods' => array( 'generateContent' ),
					),
					array(
						'name'                       => 'models/text-embedding-004',
						'supportedGenerationMethods' => array( 'embedContent' ),
					),
				),
			),
		);

		$models = ( new Gemini() )->list_models( self::KEY );

		$this->assertSame( array( 'gemini-2.5-flash' ), wp_list_pluck( $models, 'id' ) );
		$this->assertSame( self::KEY, $this->requests[0]['args']['headers']['x-goog-api-key'] );
		$this->assertStringNotContainsString( self::KEY, $this->requests[0]['url'] );
	}

	/**
	 * OpenRouter checks the key, then keeps image-in, text-out models.
	 *
	 * @return void
	 */
	public function test_open_router_lists_vision_models() {
		$this->responses['openrouter.ai/api/v1/key']    = array( 200, array( 'data' => array() ) );
		$this->responses['openrouter.ai/api/v1/models'] = array(
			200,
			array(
				'data' => array(
					array(
						'id'           => 'openai/gpt-4o',
						'name'         => 'OpenAI: GPT-4o',
						'architecture' => array(
							'input_modalities'  => array( 'text', 'image' ),
							'output_modalities' => array( 'text' ),
						),
					),
					array(
						'id'           => 'anthropic/claude-haiku-4.5',
						'name'         => 'Anthropic: Claude Haiku 4.5',
						'architecture' => array( 'input_modalities' => array( 'text', 'image' ) ),
					),
					array(
						'id'           => 'meta/llama-text',
						'name'         => 'Llama',
						'architecture' => array( 'input_modalities' => array( 'text' ) ),
					),
					array(
						'id'           => 'google/image-gen',
						'name'         => 'Image Gen',
						'architecture' => array(
							'input_modalities'  => array( 'text', 'image' ),
							'output_modalities' => array( 'image' ),
						),
					),
				),
			),
		);

		$models = ( new Open_Router() )->list_models( self::KEY );

		$this->assertSame( array( 'anthropic/claude-haiku-4.5', 'openai/gpt-4o' ), wp_list_pluck( $models, 'id' ) );
		$this->assertCount( 2, $this->requests );
	}

	/**
	 * A rejected OpenRouter key stops before the model list is fetched.
	 *
	 * @return void
	 */
	public function test_open_router_checks_key_first() {
		$this->responses['openrouter.ai/api/v1/key'] = array( 401, array( 'error' => array( 'message' => 'No auth' ) ) );

		$result = ( new Open_Router() )->list_models( self::KEY );

		$this->assertSame( 'tasty_recipes_ai_invalid_key', $result->get_error_code() );
		$this->assertCount( 1, $this->requests );
	}

	/**
	 * Generating sends the system prompt, images and token limit in each
	 * provider's format and returns the text.
	 *
	 * @return void
	 */
	public function test_generate_request_formats() {
		$args = array(
			'system'     => 'Be brief',
			'max_tokens' => 100,
			'images'     => array(
				array(
					'mime' => 'image/png',
					'data' => 'aGVsbG8=',
				),
				array(
					'mime' => 'text/html',
					'data' => 'PHNjcmlwdD4=',
				),
			),
		);

		$this->responses['api.openai.com/v1/chat/completions'] = array( 200, array( 'choices' => array( array( 'message' => array( 'content' => 'OpenAI text' ) ) ) ) );
		$this->assertSame( 'OpenAI text', ( new Open_AI() )->generate( self::KEY, 'gpt-4o', 'Hi', $args ) );
		$body = $this->requests[0]['body'];
		$this->assertSame( 'system', $body['messages'][0]['role'] );
		$this->assertSame( 100, $body['max_completion_tokens'] );
		$this->assertCount( 2, $body['messages'][1]['content'] );
		$this->assertSame( 'data:image/png;base64,aGVsbG8=', $body['messages'][1]['content'][1]['image_url']['url'] );

		$this->responses['api.anthropic.com/v1/messages'] = array(
			200,
			array(
				'content' => array(
					array(
						'type' => 'thinking',
						'text' => 'Hidden',
					),
					array(
						'type' => 'text',
						'text' => 'Claude text',
					),
				),
			),
		);
		$this->assertSame( 'Claude text', ( new Anthropic() )->generate( self::KEY, 'claude-haiku-4-5', 'Hi', $args ) );
		$body = $this->requests[1]['body'];
		$this->assertSame( 'Be brief', $body['system'] );
		$this->assertSame( 100, $body['max_tokens'] );
		$this->assertSame( 'image', $body['messages'][0]['content'][0]['type'] );
		$this->assertSame( 'text', $body['messages'][0]['content'][1]['type'] );

		$this->responses['generativelanguage.googleapis.com'] = array(
			200,
			array(
				'candidates' => array(
					array(
						'content' => array(
							'parts' => array(
								array(
									'text'    => 'Thinking...',
									'thought' => true,
								),
								array( 'text' => 'Gemini text' ),
							),
						),
					),
				),
			),
		);
		$this->assertSame( 'Gemini text', ( new Gemini() )->generate( self::KEY, 'gemini-2.5-flash', 'Hi', $args ) );
		$this->assertStringEndsWith( '/models/gemini-2.5-flash:generateContent', $this->requests[2]['url'] );
		$body = $this->requests[2]['body'];
		$this->assertSame( 'Be brief', $body['systemInstruction']['parts'][0]['text'] );
		$this->assertSame( 100, $body['generationConfig']['maxOutputTokens'] );
		$this->assertCount( 2, $body['contents'][0]['parts'] );

		$this->responses['openrouter.ai/api/v1/chat/completions'] = array( 200, array( 'choices' => array( array( 'message' => array( 'content' => 'Router text' ) ) ) ) );
		$this->assertSame( 'Router text', ( new Open_Router() )->generate( self::KEY, 'openai/gpt-4o', 'Hi', $args ) );
		$this->assertSame( 100, $this->requests[3]['body']['max_tokens'] );
		$this->assertArrayNotHasKey( 'max_completion_tokens', $this->requests[3]['body'] );
	}

	/**
	 * Refusals and empty replies become errors.
	 *
	 * @return void
	 */
	public function test_generate_refusals_and_empty_replies() {
		$this->responses['api.openai.com/v1/chat/completions'] = array( 200, array( 'choices' => array( array( 'message' => array( 'refusal' => 'No' ) ) ) ) );
		$this->assertSame( 'tasty_recipes_ai_refused', ( new Open_AI() )->generate( self::KEY, 'gpt-4o', 'Hi' )->get_error_code() );

		$this->responses['api.anthropic.com/v1/messages'] = array( 200, array( 'stop_reason' => 'refusal' ) );
		$this->assertSame( 'tasty_recipes_ai_refused', ( new Anthropic() )->generate( self::KEY, 'claude-haiku-4-5', 'Hi' )->get_error_code() );

		$this->responses['generativelanguage.googleapis.com'] = array( 200, array( 'promptFeedback' => array( 'blockReason' => 'SAFETY' ) ) );
		$this->assertSame( 'tasty_recipes_ai_refused', ( new Gemini() )->generate( self::KEY, 'gemini-2.5-flash', 'Hi' )->get_error_code() );

		$this->responses['api.openai.com/v1/chat/completions'] = array( 200, array( 'choices' => array( array( 'message' => array( 'content' => '  ' ) ) ) ) );
		$this->assertSame( 'tasty_recipes_ai_empty', ( new Open_AI() )->generate( self::KEY, 'gpt-4o', 'Hi' )->get_error_code() );

		$this->assertSame( 'tasty_recipes_ai_bad_model', ( new Gemini() )->generate( self::KEY, '../bad model', 'Hi' )->get_error_code() );
	}

	/**
	 * HTTP failures map to errors the settings screen can explain.
	 *
	 * @dataProvider data_http_errors
	 *
	 * @param int|WP_Error $status   Response status, or a transport error.
	 * @param mixed        $body     Response body.
	 * @param string       $expected Expected error code.
	 *
	 * @return void
	 */
	public function test_http_errors( $status, $body, $expected ) {
		$this->responses['api.openai.com'] = $status instanceof WP_Error ? $status : array( $status, $body );

		$result = ( new Open_AI() )->list_models( self::KEY );

		$this->assertSame( $expected, $result->get_error_code() );
	}

	/**
	 * Responses and the errors they map to.
	 *
	 * @return array
	 */
	public function data_http_errors() {
		return array(
			'unauthorized' => array( 401, array(), 'tasty_recipes_ai_invalid_key' ),
			'forbidden'    => array( 403, array(), 'tasty_recipes_ai_invalid_key' ),
			'rate limited' => array( 429, array(), 'tasty_recipes_ai_rate_limited' ),
			'server error' => array( 500, array( 'error' => array( 'message' => 'Overloaded' ) ), 'tasty_recipes_ai_provider_error' ),
			'not json'     => array( 200, '<html>', 'tasty_recipes_ai_bad_response' ),
			'unreachable'  => array( new WP_Error( 'http_request_failed', 'cURL error 28' ), null, 'tasty_recipes_ai_unreachable' ),
		);
	}

	/**
	 * Gemini reports bad keys as 400 with a reason; they still read as bad keys.
	 *
	 * @return void
	 */
	public function test_gemini_invalid_key_reason() {
		$this->responses['generativelanguage.googleapis.com'] = array(
			400,
			array(
				'error' => array(
					'message' => 'API key not valid.',
					'details' => array( array( 'reason' => 'API_KEY_INVALID' ) ),
				),
			),
		);

		$this->assertSame( 'tasty_recipes_ai_invalid_key', ( new Gemini() )->list_models( self::KEY )->get_error_code() );
	}

	/**
	 * Provider error messages never echo the key, or anything key-like.
	 *
	 * @return void
	 */
	public function test_provider_errors_are_scrubbed() {
		$this->responses['api.openai.com'] = array(
			500,
			array( 'error' => array( 'message' => 'Key ' . self::KEY . ' and sk-proj-ABCDEFGHIJKLMNOPQRSTUVWXYZ012345 failed <b>badly</b>' ) ),
		);

		$message = ( new Open_AI() )->list_models( self::KEY )->get_error_message();

		$this->assertStringNotContainsString( self::KEY, $message );
		$this->assertStringNotContainsString( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345', $message );
		$this->assertStringNotContainsString( '<b>', $message );
		$this->assertStringContainsString( '[redacted]', $message );
		$this->assertStringContainsString( 'failed badly', $message );
	}

	/**
	 * Scrubbing cuts long messages.
	 *
	 * @return void
	 */
	public function test_scrub_limits_length() {
		$this->assertSame( 300, mb_strlen( Provider::scrub( str_repeat( 'word ', 200 ) ) ) );
	}

	/**
	 * Each provider reads its key from its own constant.
	 *
	 * @return void
	 */
	public function test_key_constants() {
		$this->assertSame( 'TASTY_RECIPES_OPENAI_API_KEY', ( new Open_AI() )->get_key_constant() );
		$this->assertSame( 'TASTY_RECIPES_ANTHROPIC_API_KEY', ( new Anthropic() )->get_key_constant() );
		$this->assertSame( 'TASTY_RECIPES_GEMINI_API_KEY', ( new Gemini() )->get_key_constant() );
		$this->assertSame( 'TASTY_RECIPES_OPENROUTER_API_KEY', ( new Open_Router() )->get_key_constant() );
	}
}

<?php
/**
 * REST routes for the AI provider connection settings.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

use Tasty_Recipes\Admin;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Settings endpoints. Only site administrators can use them; the key is
 * accepted on connect and never returned by any route.
 *
 * @since x.x
 */
final class Rest {

	/**
	 * Route namespace.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const NAMESPACE = 'tasty-recipes/v1';

	/**
	 * Requests a user may make to routes that call the provider, per window.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const RATE_LIMIT = 10;

	/**
	 * Rate limit window, in seconds.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const RATE_WINDOW = 600;

	/**
	 * Registers the routes.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/ai/connection',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_connection' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'connect' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
					'args'                => array(
						'provider' => array(
							'type'     => 'string',
							'required' => true,
							'enum'     => array_keys( Connection::get_providers() ),
						),
						'api_key'  => array(
							'type'              => 'string',
							'default'           => '',
							'validate_callback' => array( __CLASS__, 'validate_api_key' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( __CLASS__, 'disconnect' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/ai/models',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_models' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'refresh' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/ai/model',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'set_model' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'model' => array(
						'type'      => 'string',
						'required'  => true,
						'maxLength' => 200,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/ai/test',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'test' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	/**
	 * Only administrators may see or change the connection.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_manage() {
		return current_user_can( Admin::CAPABILITY );
	}

	/**
	 * Rejects values that can't be an API key before anything else runs.
	 *
	 * @since x.x
	 *
	 * @param mixed $value The submitted key.
	 *
	 * @return true|WP_Error
	 */
	public static function validate_api_key( $value ) {
		if ( '' === $value ) {
			return true;
		}

		if ( ! is_string( $value ) || ! preg_match( '/^[\x21-\x7E]{8,512}$/', $value ) ) {
			return new WP_Error( 'tasty_recipes_ai_bad_key', __( 'That doesn’t look like an API key.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * GET /ai/connection
	 *
	 * @since x.x
	 *
	 * @return WP_REST_Response
	 */
	public static function get_connection() {
		return self::no_store( Connection::get_public_status() );
	}

	/**
	 * POST /ai/connection — validates the key with the provider, then saves it.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function connect( WP_REST_Request $request ) {
		$limited = self::check_rate_limit();
		if ( is_wp_error( $limited ) ) {
			return $limited;
		}

		$api_key = trim( (string) $request->get_param( 'api_key' ) );
		$models  = Connection::connect( (string) $request->get_param( 'provider' ), $api_key );
		Vault::wipe( $api_key );

		if ( is_wp_error( $models ) ) {
			return self::with_status( $models );
		}

		return self::no_store(
			array(
				'status' => Connection::get_public_status(),
				'models' => $models,
			)
		);
	}

	/**
	 * DELETE /ai/connection
	 *
	 * @since x.x
	 *
	 * @return WP_REST_Response
	 */
	public static function disconnect() {
		Connection::disconnect();

		return self::no_store( Connection::get_public_status() );
	}

	/**
	 * GET /ai/models
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_models( WP_REST_Request $request ) {
		$refresh = (bool) $request->get_param( 'refresh' );

		if ( $refresh ) {
			$limited = self::check_rate_limit();
			if ( is_wp_error( $limited ) ) {
				return $limited;
			}
		}

		$models = Connection::get_models( $refresh );

		if ( is_wp_error( $models ) ) {
			return self::with_status( $models );
		}

		return self::no_store(
			array(
				'status' => Connection::get_public_status(),
				'models' => $models,
			)
		);
	}

	/**
	 * POST /ai/model — saves the picked model right away.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function set_model( WP_REST_Request $request ) {
		$result = Connection::set_model( (string) $request->get_param( 'model' ) );

		return is_wp_error( $result ) ? $result : self::no_store( Connection::get_public_status() );
	}

	/**
	 * POST /ai/test — sends a tiny prompt to confirm everything works.
	 *
	 * @since x.x
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function test() {
		$limited = self::check_rate_limit();
		if ( is_wp_error( $limited ) ) {
			return $limited;
		}

		$started = microtime( true );
		$reply   = Client::generate(
			'Reply with the single word: OK',
			array(
				'max_tokens' => 1024,
				'feature'    => 'connection_test',
			)
		);

		if ( is_wp_error( $reply ) ) {
			return self::with_status( $reply );
		}

		return self::no_store(
			array(
				'success'    => true,
				'durationMs' => (int) round( ( microtime( true ) - $started ) * 1000 ),
			)
		);
	}

	/**
	 * Limits how often a user can make requests that reach the provider,
	 * so the endpoints can't be used to hammer the provider or burn credit.
	 *
	 * @since x.x
	 *
	 * @return true|WP_Error
	 */
	private static function check_rate_limit() {
		$key   = 'tasty_recipes_ai_rate_' . get_current_user_id();
		$count = (int) get_transient( $key );

		if ( $count >= self::RATE_LIMIT ) {
			return new WP_Error(
				'tasty_recipes_ai_too_many_requests',
				__( 'Too many attempts. Please wait a few minutes and try again.', 'tasty-recipes-lite' ),
				array( 'status' => 429 )
			);
		}

		set_transient( $key, $count + 1, self::RATE_WINDOW );

		return true;
	}

	/**
	 * Adds an HTTP status to provider errors that don't have one.
	 *
	 * @since x.x
	 *
	 * @param WP_Error $error The error.
	 *
	 * @return WP_Error
	 */
	private static function with_status( WP_Error $error ) {
		$data = $error->get_error_data();

		if ( ! is_array( $data ) || empty( $data['status'] ) ) {
			$error->add_data( array( 'status' => 400 ) );
		}

		return $error;
	}

	/**
	 * Wraps data in a response that browsers and proxies must not cache.
	 *
	 * @since x.x
	 *
	 * @param mixed $data Response data.
	 *
	 * @return WP_REST_Response
	 */
	private static function no_store( $data ) {
		$response = new WP_REST_Response( $data );
		$response->header( 'Cache-Control', 'no-store, private' );

		return $response;
	}
}

<?php
/**
 * Stores and manages the site's OAuth connection to Nutrifox.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations\Nutrifox;

use Tasty_Recipes;
use Tasty_Recipes\Options;
use WP_Error;

/**
 * Stores and manages the site's OAuth connection to Nutrifox.
 *
 * @since x.x
 */
class Connection {

	/**
	 * Connection has valid tokens.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const STATUS_CONNECTED = 'connected';

	/**
	 * Site has never connected, was disconnected, or Nutrifox revoked access.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const STATUS_DISCONNECTED = 'disconnected';

	/**
	 * OAuth scopes requested from Nutrifox.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const SCOPES = 'labels:read labels:write webhooks';

	/**
	 * Admin-post action that receives the OAuth redirect.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const CALLBACK_ACTION = 'tasty_recipes_nutrifox_callback';

	/**
	 * Prefix for transients holding pending authorization data.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	private const STATE_TRANSIENT_PREFIX = 'tasty_recipes_nutrifox_state_';

	/**
	 * Keys stored encrypted inside the connection option.
	 *
	 * @since x.x
	 *
	 * @var string[]
	 */
	private const SECRET_KEYS = array( 'client_secret', 'access_token', 'refresh_token' );

	/**
	 * Gets the current connection status without contacting Nutrifox.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_status() {
		$connection = self::get_connection();

		if ( ! empty( $connection['access_token'] ) && ! empty( $connection['refresh_token'] ) ) {
			return self::STATUS_CONNECTED;
		}

		return self::STATUS_DISCONNECTED;
	}

	/**
	 * Whether the site holds usable Nutrifox tokens.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_connected() {
		return self::STATUS_CONNECTED === self::get_status();
	}

	/**
	 * Gets the email of the connected Nutrifox account.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_email() {
		return self::is_connected() ? self::get_connection()['email'] : '';
	}

	/**
	 * Gets how long the site has been connected, e.g. "2 months".
	 *
	 * @since x.x
	 *
	 * @return string Empty when not connected or the connection date is unknown.
	 */
	public static function get_connected_for() {
		$connected_at = self::is_connected() ? self::get_connection()['connected_at'] : 0;

		return $connected_at ? human_time_diff( $connected_at ) : '';
	}

	/**
	 * Gets the URL of the optional external trial page.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_trial_url() {
		/**
		 * Filters the URL the "Try free 14 day trial" button opens.
		 *
		 * @since x.x
		 *
		 * @param string $trial_url Trial URL. Empty starts the OAuth signup flow.
		 */
		$trial_url = apply_filters( 'tasty_recipes_nutrifox_trial_url', '' );

		return is_string( $trial_url ) ? esc_url_raw( $trial_url ) : '';
	}

	/**
	 * Gets the connection data used by the recipe modal.
	 *
	 * @since x.x
	 *
	 * @return array{status: string, email: string, connectedFor: string, canConnect: bool, trialUrl: string, restBase: string, nutrifoxUrl: string}
	 */
	public static function get_editor_data() {
		return array(
			'status'       => self::get_status(),
			'email'        => self::get_email(),
			'connectedFor' => self::get_connected_for(),
			'canConnect'   => Connection_Routes::can_manage_connection(),
			'trialUrl'     => self::get_trial_url(),
			'restBase'     => Connection_Routes::REST_NAMESPACE . '/' . Connection_Routes::REST_BASE,
			'nutrifoxUrl'  => Tasty_Recipes::nutrifox_url(),
		);
	}

	/**
	 * Gets the URL Nutrifox redirects back to after authorization.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return add_query_arg( 'action', self::CALLBACK_ACTION, admin_url( 'admin-post.php' ) );
	}

	/**
	 * Builds the Nutrifox authorization URL for the current user.
	 *
	 * @since x.x
	 *
	 * @param string $intent Either "connect" or "trial".
	 *
	 * @return string|WP_Error
	 */
	public static function get_authorize_url( $intent ) {
		$client = self::ensure_client();
		if ( is_wp_error( $client ) ) {
			return $client;
		}

		$intent   = 'trial' === $intent ? 'trial' : 'connect';
		$state    = wp_generate_password( 40, false );
		$verifier = wp_generate_password( 64, false );

		set_transient(
			self::STATE_TRANSIENT_PREFIX . $state,
			array(
				'user_id'  => get_current_user_id(),
				'verifier' => $verifier,
			),
			15 * MINUTE_IN_SECONDS
		);

		$args = array(
			'client_id'             => $client['client_id'],
			'redirect_uri'          => $client['redirect_uri'],
			'response_type'         => 'code',
			'scope'                 => self::SCOPES,
			'state'                 => $state,
			'code_challenge'        => self::pkce_challenge( $verifier ),
			'code_challenge_method' => 'S256',
			'utm_source'            => 'tasty-recipes',
			'utm_medium'            => 'recipe-modal',
			'utm_campaign'          => $intent,
		);

		if ( 'trial' === $intent ) {
			$args['signup'] = 1;
		}

		return add_query_arg( array_map( 'rawurlencode', $args ), Tasty_Recipes::nutrifox_url() . '/oauth/authorize' );
	}

	/**
	 * Completes the authorization after Nutrifox redirects back.
	 *
	 * @since x.x
	 *
	 * @param string $state OAuth state echoed back by Nutrifox.
	 * @param string $code  Authorization code.
	 * @param string $error OAuth error code, if the user denied access.
	 *
	 * @return string|WP_Error Resulting status: "connected" or "denied".
	 */
	public static function complete_authorization( $state, $code, $error = '' ) {
		$pending = $state ? get_transient( self::STATE_TRANSIENT_PREFIX . $state ) : false;

		if ( ! is_array( $pending ) || (int) $pending['user_id'] !== get_current_user_id() ) {
			return new WP_Error( 'tasty_recipes_nutrifox_state', __( 'This connection request expired. Please try again.', 'tasty-recipes-lite' ) );
		}

		delete_transient( self::STATE_TRANSIENT_PREFIX . $state );

		if ( 'access_denied' === $error ) {
			return 'denied';
		}

		if ( ! $code ) {
			return new WP_Error( 'tasty_recipes_nutrifox_code', __( 'Nutrifox did not return an authorization code.', 'tasty-recipes-lite' ) );
		}

		$connection = self::get_connection();
		$response   = self::remote_request(
			'POST',
			'/oauth/token',
			array(
				'body' => array(
					'grant_type'    => 'authorization_code',
					'client_id'     => $connection['client_id'],
					'client_secret' => $connection['client_secret'],
					'redirect_uri'  => $connection['redirect_uri'],
					'code'          => $code,
					'code_verifier' => $pending['verifier'],
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 !== $response['code'] || empty( $response['body']['access_token'] ) ) {
			return self::response_error( $response );
		}

		self::store_tokens( $response['body'] );

		$connection                 = self::get_connection();
		$connection['connected_at'] = time();
		self::save_connection( $connection );

		self::refresh_account();

		return self::STATUS_CONNECTED;
	}

	/**
	 * Confirms the connection with Nutrifox so revoked access disconnects right away.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function check_status() {
		if ( self::is_connected() ) {
			self::refresh_account();
		}

		return self::get_status();
	}

	/**
	 * Sends an authenticated request to the Nutrifox API.
	 *
	 * @since x.x
	 *
	 * @param string $method HTTP method.
	 * @param string $path   Path relative to the Nutrifox URL.
	 * @param array  $args   Extra wp_remote_request() arguments.
	 *
	 * @return array{code: int, body: array}|WP_Error
	 */
	public static function authorized_request( $method, $path, $args = array() ) {
		$token = self::get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$response = self::remote_request( $method, $path, self::with_bearer( $args, $token ) );
		if ( is_wp_error( $response ) || 401 !== $response['code'] ) {
			return $response;
		}

		$token = self::refresh_tokens();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$response = self::remote_request( $method, $path, self::with_bearer( $args, $token ) );
		if ( ! is_wp_error( $response ) && 401 === $response['code'] ) {
			return self::handle_revoked_access();
		}

		return $response;
	}

	/**
	 * Disconnects the site after Nutrifox revokes its tokens.
	 *
	 * @since x.x
	 *
	 * @return true|WP_Error
	 */
	public static function disconnect() {
		if ( ! self::is_connected() ) {
			return true;
		}

		$response = self::authorized_request( 'DELETE', '/api/v1/connection' );

		if ( is_wp_error( $response ) ) {
			// Nutrifox already revoked access, and authorized_request() cleared the tokens.
			return 'tasty_recipes_nutrifox_not_connected' === $response->get_error_code() ? true : $response;
		}

		if ( 204 !== $response['code'] ) {
			return self::response_error( $response );
		}

		self::clear_tokens();

		return true;
	}

	/**
	 * Gets a valid access token, refreshing it when it is about to expire.
	 *
	 * @since x.x
	 *
	 * @return string|WP_Error
	 */
	private static function get_access_token() {
		$connection = self::get_connection();

		if ( empty( $connection['access_token'] ) ) {
			return new WP_Error( 'tasty_recipes_nutrifox_not_connected', __( 'Nutrifox is not connected.', 'tasty-recipes-lite' ) );
		}

		if ( $connection['expires_at'] > time() + MINUTE_IN_SECONDS ) {
			return $connection['access_token'];
		}

		return self::refresh_tokens();
	}

	/**
	 * Exchanges the refresh token for a new token pair.
	 *
	 * @since x.x
	 *
	 * @return string|WP_Error New access token.
	 */
	private static function refresh_tokens() {
		$connection = self::get_connection();

		$response = self::remote_request(
			'POST',
			'/oauth/token',
			array(
				'body' => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $connection['refresh_token'],
					'client_id'     => $connection['client_id'],
					'client_secret' => $connection['client_secret'],
					'scope'         => self::SCOPES,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( in_array( $response['code'], array( 400, 401 ), true ) ) {
			return self::handle_revoked_access();
		}

		if ( 200 !== $response['code'] || empty( $response['body']['access_token'] ) ) {
			return self::response_error( $response );
		}

		self::store_tokens( $response['body'] );

		return $response['body']['access_token'];
	}

	/**
	 * Fetches the connected account details.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function refresh_account() {
		$response = self::authorized_request( 'GET', '/api/v1/me' );
		if ( is_wp_error( $response ) || 200 !== $response['code'] ) {
			return;
		}

		$connection          = self::get_connection();
		$connection['email'] = sanitize_email( $response['body']['email'] ?? '' );
		self::save_connection( $connection );
	}

	/**
	 * Drops the tokens after Nutrifox revoked access, returning the site to not connected.
	 *
	 * @since x.x
	 *
	 * @return WP_Error
	 */
	private static function handle_revoked_access() {
		self::clear_tokens();

		return new WP_Error( 'tasty_recipes_nutrifox_not_connected', __( 'Nutrifox is not connected.', 'tasty-recipes-lite' ) );
	}

	/**
	 * Registers this site as an OAuth client on Nutrifox when needed.
	 *
	 * @since x.x
	 *
	 * @return array{client_id: string, redirect_uri: string}|WP_Error
	 */
	private static function ensure_client() {
		$connection   = self::get_connection();
		$redirect_uri = self::get_redirect_uri();

		if ( $connection['client_id'] && $connection['redirect_uri'] === $redirect_uri ) {
			return array(
				'client_id'    => $connection['client_id'],
				'redirect_uri' => $redirect_uri,
			);
		}

		$response = self::remote_request(
			'POST',
			'/api/oauth/sites',
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'site_url'     => home_url(),
						'redirect_uri' => $redirect_uri,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 201 !== $response['code'] || empty( $response['body']['client_id'] ) ) {
			return self::response_error( $response );
		}

		self::save_connection(
			array(
				'client_id'     => (string) $response['body']['client_id'],
				'client_secret' => (string) $response['body']['client_secret'],
				'redirect_uri'  => $redirect_uri,
			)
		);

		return array(
			'client_id'    => (string) $response['body']['client_id'],
			'redirect_uri' => $redirect_uri,
		);
	}

	/**
	 * Saves a token response from Nutrifox.
	 *
	 * @since x.x
	 *
	 * @param array $tokens Token response body.
	 *
	 * @return void
	 */
	private static function store_tokens( $tokens ) {
		$connection = self::get_connection();

		$connection['access_token']  = (string) $tokens['access_token'];
		$connection['refresh_token'] = (string) ( $tokens['refresh_token'] ?? $connection['refresh_token'] );
		$connection['expires_at']    = time() + (int) ( $tokens['expires_in'] ?? HOUR_IN_SECONDS );

		self::save_connection( $connection );
	}

	/**
	 * Removes the tokens while keeping the OAuth client.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function clear_tokens() {
		$connection = self::get_connection();

		$connection['access_token']  = '';
		$connection['refresh_token'] = '';
		$connection['expires_at']    = 0;
		$connection['email']         = '';
		$connection['connected_at']  = 0;

		self::save_connection( $connection );
	}

	/**
	 * Gets the stored connection with secrets decrypted.
	 *
	 * @since x.x
	 *
	 * @return array{client_id: string, client_secret: string, redirect_uri: string, access_token: string, refresh_token: string, expires_at: int, email: string, connected_at: int}
	 */
	private static function get_connection() {
		$stored = get_option( Options::NUTRIFOX_CONNECTION, array() );
		$stored = is_array( $stored ) ? $stored : array();

		$secrets = isset( $stored['secrets'] ) ? json_decode( self::decrypt( (string) $stored['secrets'] ), true ) : array();
		$secrets = is_array( $secrets ) ? $secrets : array();

		return array(
			'client_id'     => (string) ( $stored['client_id'] ?? '' ),
			'client_secret' => (string) ( $secrets['client_secret'] ?? '' ),
			'redirect_uri'  => (string) ( $stored['redirect_uri'] ?? '' ),
			'access_token'  => (string) ( $secrets['access_token'] ?? '' ),
			'refresh_token' => (string) ( $secrets['refresh_token'] ?? '' ),
			'expires_at'    => (int) ( $stored['expires_at'] ?? 0 ),
			'email'         => (string) ( $stored['email'] ?? '' ),
			'connected_at'  => (int) ( $stored['connected_at'] ?? 0 ),
		);
	}

	/**
	 * Saves the connection, encrypting the secrets.
	 *
	 * @since x.x
	 *
	 * @param array $connection Connection data.
	 *
	 * @return void
	 */
	private static function save_connection( $connection ) {
		$secrets = array_intersect_key( $connection, array_flip( self::SECRET_KEYS ) );
		$stored  = array_diff_key( $connection, $secrets );

		$stored['secrets'] = self::encrypt( (string) wp_json_encode( $secrets ) );

		update_option( Options::NUTRIFOX_CONNECTION, $stored, false );
	}

	/**
	 * Sends a request to Nutrifox and decodes the JSON response.
	 *
	 * @since x.x
	 *
	 * @param string $method HTTP method.
	 * @param string $path   Path relative to the Nutrifox URL.
	 * @param array  $args   Extra wp_remote_request() arguments.
	 *
	 * @return array{code: int, body: array}|WP_Error
	 */
	private static function remote_request( $method, $path, $args = array() ) {
		$args = array_merge(
			array(
				'method'  => $method,
				'timeout' => 15,
			),
			$args
		);

		$args['headers'] = array_merge( array( 'Accept' => 'application/json' ), $args['headers'] ?? array() );

		$response = wp_remote_request( Tasty_Recipes::nutrifox_url() . $path, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return array(
			'code' => (int) wp_remote_retrieve_response_code( $response ),
			'body' => is_array( $body ) ? $body : array(),
		);
	}

	/**
	 * Adds the bearer token header to request arguments.
	 *
	 * @since x.x
	 *
	 * @param array  $args  Request arguments.
	 * @param string $token Access token.
	 *
	 * @return array
	 */
	private static function with_bearer( $args, $token ) {
		$args['headers'] = array_merge( $args['headers'] ?? array(), array( 'Authorization' => 'Bearer ' . $token ) );

		return $args;
	}

	/**
	 * Converts an unexpected Nutrifox response into an error.
	 *
	 * @since x.x
	 *
	 * @param array{code: int, body: array} $response Decoded response.
	 *
	 * @return WP_Error
	 */
	public static function response_error( $response ) {
		$message = $response['body']['message'] ?? $response['body']['error_description'] ?? '';

		if ( ! $message ) {
			// translators: Nutrifox HTTP error code.
			$message = sprintf( __( 'Nutrifox API request failed (HTTP code %d)', 'tasty-recipes-lite' ), $response['code'] );
		}

		return new WP_Error( 'tasty_recipes_nutrifox_api', sanitize_text_field( $message ), array( 'status' => 502 ) );
	}

	/**
	 * Builds the S256 PKCE code challenge.
	 *
	 * @since x.x
	 *
	 * @param string $verifier Code verifier.
	 *
	 * @return string
	 */
	private static function pkce_challenge( $verifier ) {
		$hash = hash( 'sha256', $verifier, true );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Required by the PKCE spec.
		return rtrim( strtr( base64_encode( $hash ), '+/', '-_' ), '=' );
	}

	/**
	 * Encrypts a value with a key derived from the site salts.
	 *
	 * @since x.x
	 *
	 * @param string $plain Value to encrypt.
	 *
	 * @return string
	 */
	private static function encrypt( $plain ) {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return 'p:' . bin2hex( $plain );
		}

		$iv     = random_bytes( 12 );
		$tag    = '';
		$cipher = openssl_encrypt( $plain, 'aes-256-gcm', self::encryption_key(), OPENSSL_RAW_DATA, $iv, $tag );

		return 'e:' . bin2hex( $iv . $tag . $cipher );
	}

	/**
	 * Decrypts a value produced by encrypt().
	 *
	 * @since x.x
	 *
	 * @param string $encrypted Encrypted value.
	 *
	 * @return string Empty when the value cannot be decrypted, e.g. after salts change.
	 */
	private static function decrypt( $encrypted ) {
		$prefix = substr( $encrypted, 0, 2 );
		$data   = (string) hex2bin( substr( $encrypted, 2 ) );

		if ( 'p:' === $prefix ) {
			return $data;
		}

		if ( 'e:' !== $prefix || strlen( $data ) < 28 || ! function_exists( 'openssl_decrypt' ) ) {
			return '';
		}

		$plain = openssl_decrypt( substr( $data, 28 ), 'aes-256-gcm', self::encryption_key(), OPENSSL_RAW_DATA, substr( $data, 0, 12 ), substr( $data, 12, 16 ) );

		return false === $plain ? '' : $plain;
	}

	/**
	 * Derives the encryption key from the site salts.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function encryption_key() {
		return hash( 'sha256', wp_salt( 'auth' ) . 'tasty-recipes-nutrifox', true );
	}
}

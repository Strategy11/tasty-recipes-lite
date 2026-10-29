<?php
/**
 * Stores the site's AI provider connection.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

use Tasty_Recipes\AI\Providers\Anthropic;
use Tasty_Recipes\AI\Providers\Gemini;
use Tasty_Recipes\AI\Providers\Open_AI;
use Tasty_Recipes\AI\Providers\Open_Router;
use Tasty_Recipes\AI\Providers\Provider;
use Tasty_Recipes\Options;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * The connected provider, model and API key.
 *
 * The key is stored encrypted (see Vault) in a non-autoloaded option, or
 * read from a wp-config.php constant and never stored. Only get_api_key()
 * returns it, and nothing in this class sends it to the browser.
 *
 * @since x.x
 */
final class Connection {

	/**
	 * Transient caching the model list for the connected key.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const MODELS_TRANSIENT = 'tasty_recipes_ai_models';

	/**
	 * Key source: stored encrypted in the database.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SOURCE_DATABASE = 'database';

	/**
	 * Key source: a wp-config.php constant.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SOURCE_CONSTANT = 'constant';

	/**
	 * Registered providers, keyed by ID.
	 *
	 * @since x.x
	 *
	 * @return array<string, Provider>
	 */
	public static function get_providers() {
		$providers = array(
			new Open_AI(),
			new Anthropic(),
			new Gemini(),
			new Open_Router(),
		);

		/**
		 * Filters the AI providers users can connect.
		 *
		 * @since x.x
		 *
		 * @param Provider[] $providers Provider adapters.
		 */
		$providers = apply_filters( 'tasty_recipes_ai_providers', $providers );

		$keyed = array();
		foreach ( (array) $providers as $provider ) {
			if ( $provider instanceof Provider ) {
				$keyed[ $provider->get_id() ] = $provider;
			}
		}

		return $keyed;
	}

	/**
	 * A provider by ID.
	 *
	 * @since x.x
	 *
	 * @param string $id Provider ID.
	 *
	 * @return Provider|null
	 */
	public static function get_provider( $id ) {
		$providers = self::get_providers();

		return $providers[ $id ] ?? null;
	}

	/**
	 * The stored connection, without decrypting the key.
	 *
	 * @since x.x
	 *
	 * @return array{provider: string, model: string, model_auto: bool, key: string, key_hint: string}
	 */
	private static function get_stored() {
		$stored = get_option( Options::AI_CONNECTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$model  = get_option( Options::AI_MODEL, '' );

		return array(
			'provider'   => is_string( $stored['provider'] ?? null ) ? $stored['provider'] : '',
			'model'      => is_string( $model ) ? $model : '',
			'model_auto' => ! empty( $stored['model_auto'] ),
			'key'        => is_string( $stored['key'] ?? null ) ? $stored['key'] : '',
			'key_hint'   => is_string( $stored['key_hint'] ?? null ) ? $stored['key_hint'] : '',
		);
	}

	/**
	 * The model to preselect for a provider, if it has one.
	 *
	 * @since x.x
	 *
	 * @param Provider $provider The provider.
	 * @param array    $models   Available models.
	 *
	 * @return string Model ID, or an empty string.
	 */
	private static function get_recommended_model( Provider $provider, $models ) {
		if ( '' === $provider->get_model_family_label() || ! is_array( $models ) ) {
			return '';
		}

		return $provider->get_recommended_model( $models );
	}

	/**
	 * Saves the connection (always non-autoloaded) and, when given, the model.
	 *
	 * @since x.x
	 *
	 * @param array $connection Connection data from get_stored().
	 *
	 * @return void
	 */
	private static function store( $connection ) {
		if ( array_key_exists( 'model', $connection ) ) {
			self::write_model( $connection['model'] );
			unset( $connection['model'] );
		}

		if ( false === get_option( Options::AI_CONNECTION, false ) ) {
			add_option( Options::AI_CONNECTION, $connection, '', false );
			return;
		}

		update_option( Options::AI_CONNECTION, $connection, false );
	}

	/**
	 * Writes the model option.
	 *
	 * @since x.x
	 *
	 * @param string $model Model ID, or empty to clear it.
	 *
	 * @return void
	 */
	private static function write_model( $model ) {
		if ( '' === $model ) {
			delete_option( Options::AI_MODEL );
			return;
		}

		update_option( Options::AI_MODEL, $model );
	}

	/**
	 * Reads a provider's key from its wp-config.php constant.
	 *
	 * @since x.x
	 *
	 * @param Provider $provider The provider.
	 *
	 * @return string The key, or an empty string.
	 */
	private static function get_constant_key( Provider $provider ) {
		$constant = $provider->get_key_constant();

		if ( ! defined( $constant ) ) {
			return '';
		}

		$value = constant( $constant );

		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * Validates a key, then saves the provider and (encrypted) key.
	 *
	 * @since x.x
	 *
	 * @param string $provider_id Provider ID.
	 * @param string $api_key     The API key, or empty to use the constant.
	 *
	 * @return array|WP_Error The model list on success, or an error.
	 */
	public static function connect(
		$provider_id,
		#[\SensitiveParameter]
		$api_key
	) {
		$provider = self::get_provider( $provider_id );

		if ( ! $provider ) {
			return new WP_Error( 'tasty_recipes_ai_unknown_provider', __( 'Choose an AI provider.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$constant_key  = self::get_constant_key( $provider );
		$uses_constant = '' !== $constant_key;
		$key           = $uses_constant ? $constant_key : $api_key;

		if ( '' === $key ) {
			return new WP_Error( 'tasty_recipes_ai_missing_key', __( 'Enter your API key.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$models = $provider->list_models( $key );

		if ( is_wp_error( $models ) ) {
			Vault::wipe( $key );
			return $models;
		}

		if ( ! $models ) {
			Vault::wipe( $key );

			return new WP_Error(
				'tasty_recipes_ai_no_vision_models',
				sprintf(
					/* translators: %s: AI provider name. */
					__( 'This %s API key can’t use any models that read images, which Tasty Recipes needs. Check the key’s permissions and try again.', 'tasty-recipes-lite' ),
					$provider->get_label()
				),
				array( 'status' => 400 )
			);
		}

		$model = self::get_recommended_model( $provider, $models );

		$connection = array(
			'provider'   => $provider->get_id(),
			'model'      => $model,
			'model_auto' => '' !== $model,
			'key'        => '',
			'key_hint'   => '',
		);

		if ( ! $uses_constant ) {
			$encrypted = Vault::encrypt( $key, self::key_context( $provider ) );

			if ( is_wp_error( $encrypted ) ) {
				Vault::wipe( $key );
				return $encrypted;
			}

			$connection['key']      = $encrypted;
			$connection['key_hint'] = substr( $key, -4 );
		}

		Vault::wipe( $key );
		self::store( $connection );
		set_transient( self::MODELS_TRANSIENT, $models, 12 * HOUR_IN_SECONDS );

		return $models;
	}

	/**
	 * Saves the model the user picked.
	 *
	 * @since x.x
	 *
	 * @param string $model Model ID.
	 *
	 * @return true|WP_Error
	 */
	public static function set_model( $model ) {
		$stored   = self::get_stored();
		$provider = self::get_provider( $stored['provider'] );

		if ( ! $provider || ! self::has_key() ) {
			return new WP_Error( 'tasty_recipes_ai_not_connected', __( 'Connect an AI provider first.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$models = self::get_models();
		$ids    = is_wp_error( $models ) ? array() : wp_list_pluck( $models, 'id' );

		if ( ! $provider->is_valid_model_id( $model ) || ( $ids && ! in_array( $model, $ids, true ) ) ) {
			return new WP_Error( 'tasty_recipes_ai_bad_model', __( 'That model isn’t available for this API key.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$stored['model']      = $model;
		$stored['model_auto'] = $model === self::get_recommended_model( $provider, $models );
		self::store( $stored );

		return true;
	}

	/**
	 * Removes the connection and its cached data.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function disconnect() {
		delete_option( Options::AI_CONNECTION );
		self::write_model( '' );
		delete_transient( self::MODELS_TRANSIENT );
	}

	/**
	 * The connected provider.
	 *
	 * @since x.x
	 *
	 * @return Provider|null
	 */
	public static function get_connected_provider() {
		return self::get_provider( self::get_stored()['provider'] );
	}

	/**
	 * The model ID to send requests to.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_model() {
		if ( self::get_stored()['model_auto'] ) {
			self::get_models();
		}

		return self::get_stored()['model'];
	}

	/**
	 * Where the connected provider's key comes from.
	 *
	 * @since x.x
	 *
	 * @return string One of the SOURCE_* constants, or an empty string.
	 */
	public static function get_key_source() {
		$stored   = self::get_stored();
		$provider = self::get_provider( $stored['provider'] );

		if ( ! $provider ) {
			return '';
		}

		if ( '' !== self::get_constant_key( $provider ) ) {
			return self::SOURCE_CONSTANT;
		}

		return '' !== $stored['key'] ? self::SOURCE_DATABASE : '';
	}

	/**
	 * Whether a key is available for the connected provider.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function has_key() {
		return '' !== self::get_key_source();
	}

	/**
	 * Decrypts the API key for an outgoing request.
	 *
	 * @since x.x
	 *
	 * @return string|WP_Error The key, or an error.
	 */
	public static function get_api_key() {
		$stored   = self::get_stored();
		$provider = self::get_provider( $stored['provider'] );

		if ( ! $provider ) {
			return new WP_Error( 'tasty_recipes_ai_not_connected', __( 'Connect an AI provider first.', 'tasty-recipes-lite' ) );
		}

		$constant_key = self::get_constant_key( $provider );
		if ( '' !== $constant_key ) {
			return $constant_key;
		}

		if ( '' === $stored['key'] ) {
			return new WP_Error( 'tasty_recipes_ai_not_connected', __( 'Connect an AI provider first.', 'tasty-recipes-lite' ) );
		}

		return Vault::decrypt( $stored['key'], self::key_context( $provider ) );
	}

	/**
	 * Models the connected key can use, cached for 12 hours.
	 *
	 * @since x.x
	 *
	 * @param bool $refresh Whether to skip the cache.
	 *
	 * @return array|WP_Error
	 */
	public static function get_models( $refresh = false ) {
		$cached = get_transient( self::MODELS_TRANSIENT );

		if ( ! $refresh && is_array( $cached ) ) {
			return $cached;
		}

		$provider = self::get_connected_provider();
		$key      = self::get_api_key();

		if ( ! $provider || is_wp_error( $key ) ) {
			return is_wp_error( $key ) ? $key : array();
		}

		$models = $provider->list_models( $key );
		Vault::wipe( $key );

		if ( is_wp_error( $models ) ) {
			return $models;
		}

		set_transient( self::MODELS_TRANSIENT, $models, 12 * HOUR_IN_SECONDS );

		// Keep the recommended model on the newest release in its line.
		$stored = self::get_stored();
		if ( $stored['model_auto'] ) {
			$recommended = self::get_recommended_model( $provider, $models );

			if ( '' !== $recommended && $recommended !== $stored['model'] ) {
				$stored['model'] = $recommended;
				self::store( $stored );
			}
		}

		return $models;
	}

	/**
	 * Connection details that are safe to send to the browser.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function get_public_status() {
		$stored   = self::get_stored();
		$provider = self::get_provider( $stored['provider'] );
		$source   = self::get_key_source();
		$readable = true;

		if ( self::SOURCE_DATABASE === $source ) {
			$key      = self::get_api_key();
			$readable = ! is_wp_error( $key );
			Vault::wipe( $key );
		}

		$providers = array();
		foreach ( self::get_providers() as $item ) {
			$providers[] = array(
				'id'             => $item->get_id(),
				'label'          => $item->get_label(),
				'credentialsUrl' => $item->get_credentials_url(),
				'hasConstantKey' => '' !== self::get_constant_key( $item ),
			);
		}

		$cached_models = get_transient( self::MODELS_TRANSIENT );
		$model_label   = $stored['model'];
		foreach ( is_array( $cached_models ) ? $cached_models : array() as $model ) {
			if ( ( $model['id'] ?? '' ) === $stored['model'] ) {
				$model_label = $model['label'];
			}
		}

		return array(
			'provider'       => $provider ? $provider->get_id() : '',
			'model'          => $stored['model'],
			'modelLabel'     => $model_label,
			'modelIsAuto'    => $stored['model_auto'],
			'recommended'    => $provider ? self::get_recommended_model( $provider, $cached_models ) : '',
			'connected'      => $provider && '' !== $source && $readable,
			'needsReconnect' => $provider && self::SOURCE_DATABASE === $source && ! $readable,
			'keySource'      => $source,
			'keyHint'        => self::SOURCE_DATABASE === $source ? $stored['key_hint'] : '',
			'canEncrypt'     => Vault::is_available(),
			'providers'      => $providers,
		);
	}

	/**
	 * Encryption context for a provider's key, so a key encrypted for one
	 * provider can't be decrypted as another's.
	 *
	 * @since x.x
	 *
	 * @param Provider $provider The provider.
	 *
	 * @return string
	 */
	private static function key_context( Provider $provider ) {
		return Options::AI_CONNECTION . '|' . $provider->get_id();
	}
}

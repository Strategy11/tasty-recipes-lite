<?php
/**
 * Encrypts and decrypts AI provider API keys.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Authenticated encryption for secrets stored in the database.
 *
 * @since x.x
 */
final class Vault {

	/**
	 * Prefix marking the payload format, so it can change later.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const FORMAT = 'trv1:';

	/**
	 * Constant that site owners can define in wp-config.php to hold their own
	 * encryption secret instead of relying on the auth salts.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SECRET_CONSTANT = 'TASTY_RECIPES_AI_ENCRYPTION_KEY';

	/**
	 * Whether encryption is available on this server.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_available() {
		return function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' )
			&& function_exists( 'hash_hkdf' );
	}

	/**
	 * Encrypts a secret.
	 *
	 * @since x.x
	 *
	 * @param string $plaintext The secret to encrypt.
	 * @param string $context   Binds the ciphertext to its use (for example the
	 *                          provider ID), so it can't be swapped into another slot.
	 *
	 * @return string|WP_Error The encrypted payload, or an error.
	 */
	public static function encrypt( $plaintext, $context ) {
		if ( ! self::is_available() ) {
			return self::unavailable_error();
		}

		try {
			$key        = self::derive_key();
			$nonce      = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
			$ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt( $plaintext, $context, $nonce, $key );
			self::wipe( $key );

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Binary-safe storage, not obfuscation.
			return self::FORMAT . base64_encode( $nonce . $ciphertext );
		} catch ( \Exception $e ) {
			return new WP_Error( 'tasty_recipes_ai_encrypt_failed', __( 'The API key could not be encrypted.', 'tasty-recipes-lite' ) );
		}
	}

	/**
	 * Decrypts a payload made by encrypt().
	 *
	 * @since x.x
	 *
	 * @param string $payload The encrypted payload.
	 * @param string $context The context it was encrypted with.
	 *
	 * @return string|WP_Error The secret, or an error.
	 */
	public static function decrypt( $payload, $context ) {
		if ( ! self::is_available() ) {
			return self::unavailable_error();
		}

		$failed = new WP_Error( 'tasty_recipes_ai_decrypt_failed', __( 'The saved API key could not be read. Please connect your provider again.', 'tasty-recipes-lite' ) );

		if ( ! is_string( $payload ) || 0 !== strpos( $payload, self::FORMAT ) ) {
			return $failed;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Binary-safe storage, not obfuscation.
		$raw         = base64_decode( substr( $payload, strlen( self::FORMAT ) ), true );
		$nonce_bytes = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

		if ( false === $raw || strlen( $raw ) <= $nonce_bytes ) {
			return $failed;
		}

		try {
			$key       = self::derive_key();
			$plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
				substr( $raw, $nonce_bytes ),
				$context,
				substr( $raw, 0, $nonce_bytes ),
				$key
			);
			self::wipe( $key );
		} catch ( \Exception $e ) {
			return $failed;
		}

		return is_string( $plaintext ) ? $plaintext : $failed;
	}

	/**
	 * Overwrites a string in memory where the runtime allows it.
	 *
	 * @since x.x
	 *
	 * @param string $value The value to wipe.
	 *
	 * @return void
	 */
	public static function wipe( &$value ) {
		if ( ! is_string( $value ) ) {
			return;
		}

		try {
			sodium_memzero( $value );
		} catch ( \Throwable $e ) {
			// sodium_compat can't wipe memory; drop the reference instead.
			$value = '';
		}
	}

	/**
	 * Derives the 256-bit encryption key.
	 *
	 * @since x.x
	 *
	 * @return string Raw key bytes.
	 */
	private static function derive_key() {
		$secret = defined( self::SECRET_CONSTANT ) ? constant( self::SECRET_CONSTANT ) : '';

		if ( ! is_string( $secret ) || strlen( $secret ) < 32 ) {
			$secret = wp_salt( 'auth' );
		}

		return hash_hkdf(
			'sha256',
			$secret,
			SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES,
			'tasty-recipes-ai-provider-key'
		);
	}

	/**
	 * Error for servers without libsodium support.
	 *
	 * @since x.x
	 *
	 * @return WP_Error
	 */
	private static function unavailable_error() {
		return new WP_Error(
			'tasty_recipes_ai_encryption_unavailable',
			__( 'This server can’t encrypt API keys. Ask your host to enable the PHP sodium extension, or define the key in wp-config.php.', 'tasty-recipes-lite' )
		);
	}
}

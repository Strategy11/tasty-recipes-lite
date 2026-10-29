<?php
/**
 * Test case for the Tasty_Recipes\AI\Vault class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Vault;

/**
 * Test case for the Tasty_Recipes\AI\Vault class.
 */
class Test_AI_Vault extends Tasty_Recipes_Testcase {

	/**
	 * Skips the tests on servers without sodium.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! Vault::is_available() ) {
			$this->markTestSkipped( 'Sodium is not available.' );
		}
	}

	/**
	 * A secret decrypts to the same value with the same context.
	 *
	 * @return void
	 */
	public function test_encrypt_round_trip() {
		$payload = Vault::encrypt( 'sk-secret-value', 'context-a' );

		$this->assertIsString( $payload );
		$this->assertStringStartsWith( Vault::FORMAT, $payload );
		$this->assertStringNotContainsString( 'sk-secret-value', $payload );
		$this->assertSame( 'sk-secret-value', Vault::decrypt( $payload, 'context-a' ) );
	}

	/**
	 * Each encryption uses a new nonce.
	 *
	 * @return void
	 */
	public function test_encrypt_uses_a_new_nonce_each_time() {
		$this->assertNotSame(
			Vault::encrypt( 'sk-secret-value', 'context-a' ),
			Vault::encrypt( 'sk-secret-value', 'context-a' )
		);
	}

	/**
	 * A key saved for one provider can't be read as another's.
	 *
	 * @return void
	 */
	public function test_decrypt_fails_with_another_context() {
		$payload = Vault::encrypt( 'sk-secret-value', 'tasty_recipes_ai_connection|openai' );
		$result  = Vault::decrypt( $payload, 'tasty_recipes_ai_connection|anthropic' );

		$this->assertWPError( $result );
		$this->assertSame( 'tasty_recipes_ai_decrypt_failed', $result->get_error_code() );
	}

	/**
	 * Damaged or foreign payloads are rejected.
	 *
	 * @dataProvider data_bad_payloads
	 *
	 * @param mixed $payload Payload to decrypt.
	 *
	 * @return void
	 */
	public function test_decrypt_rejects_bad_payloads( $payload ) {
		$result = Vault::decrypt( $payload, 'context-a' );

		$this->assertWPError( $result );
		$this->assertSame( 'tasty_recipes_ai_decrypt_failed', $result->get_error_code() );
	}

	/**
	 * Payloads that must not decrypt.
	 *
	 * @return array
	 */
	public function data_bad_payloads() {
		return array(
			'not a string'     => array( array( 'trv1:abc' ) ),
			'empty'            => array( '' ),
			'missing prefix'   => array( base64_encode( str_repeat( 'a', 64 ) ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'wrong prefix'     => array( 'trv0:' . base64_encode( str_repeat( 'a', 64 ) ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'invalid base64'   => array( Vault::FORMAT . '!!!not base64!!!' ),
			'shorter than nonce' => array( Vault::FORMAT . base64_encode( 'short' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);
	}

	/**
	 * Changing one byte of the ciphertext breaks decryption.
	 *
	 * @return void
	 */
	public function test_decrypt_rejects_tampered_ciphertext() {
		$payload = Vault::encrypt( 'sk-secret-value', 'context-a' );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$raw = base64_decode( substr( $payload, strlen( Vault::FORMAT ) ) );

		$raw[ strlen( $raw ) - 1 ] = chr( ord( $raw[ strlen( $raw ) - 1 ] ) ^ 1 );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$result = Vault::decrypt( Vault::FORMAT . base64_encode( $raw ), 'context-a' );

		$this->assertWPError( $result );
	}

	/**
	 * Wiping clears the string.
	 *
	 * @return void
	 */
	public function test_wipe_clears_the_value() {
		$value = 'sk-secret-value';

		Vault::wipe( $value );

		$this->assertNotSame( 'sk-secret-value', $value );
	}

	/**
	 * A secret constant changes the encryption key, so payloads made with the
	 * auth salt no longer decrypt.
	 *
	 * Defining the constant lasts for the rest of the run, so this is the only
	 * test that depends on it being unset.
	 *
	 * @return void
	 */
	public function test_secret_constant_replaces_the_auth_salt() {
		if ( defined( Vault::SECRET_CONSTANT ) ) {
			$this->markTestSkipped( 'The secret constant is already defined.' );
		}

		$with_salt = Vault::encrypt( 'sk-secret-value', 'context-a' );

		define( Vault::SECRET_CONSTANT, str_repeat( 'k', 40 ) );

		$this->assertWPError( Vault::decrypt( $with_salt, 'context-a' ) );

		$with_constant = Vault::encrypt( 'sk-secret-value', 'context-a' );
		$this->assertSame( 'sk-secret-value', Vault::decrypt( $with_constant, 'context-a' ) );
	}
}

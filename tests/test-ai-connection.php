<?php
/**
 * Test case for the Tasty_Recipes\AI\Connection class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Connection;
use Tasty_Recipes\AI\Vault;
use Tasty_Recipes\Options;

/**
 * Test case for the Tasty_Recipes\AI\Connection class.
 */
class Test_AI_Connection extends Tasty_Recipes_AI_Testcase {

	/**
	 * Connecting validates the key, stores it encrypted and picks the
	 * recommended model.
	 *
	 * @return void
	 */
	public function test_connect_stores_encrypted_key_and_recommended_model() {
		$models = Connection::connect( 'fake', self::FAKE_KEY );

		$this->assertSame( $this->provider->models, $models );
		$this->assertSame( array( self::FAKE_KEY ), $this->provider->listed_keys );

		$stored = get_option( Options::AI_CONNECTION );
		$this->assertSame( 'fake', $stored['provider'] );
		$this->assertStringStartsWith( Vault::FORMAT, $stored['key'] );
		$this->assertStringNotContainsString( self::FAKE_KEY, maybe_serialize( $stored ) );
		$this->assertSame( 'abcd', $stored['key_hint'] );

		$this->assertSame( 'fake-vision-2', get_option( Options::AI_MODEL ) );
		$this->assertSame( self::FAKE_KEY, Connection::get_api_key() );
		$this->assertSame( Connection::SOURCE_DATABASE, Connection::get_key_source() );
	}

	/**
	 * Unknown providers and missing keys are rejected before any request.
	 *
	 * @return void
	 */
	public function test_connect_rejects_unknown_provider_and_missing_key() {
		$unknown = Connection::connect( 'nope', self::FAKE_KEY );
		$this->assertSame( 'tasty_recipes_ai_unknown_provider', $unknown->get_error_code() );

		$missing = Connection::connect( 'fake', '' );
		$this->assertSame( 'tasty_recipes_ai_missing_key', $missing->get_error_code() );

		$this->assertSame( array(), $this->provider->listed_keys );
		$this->assertFalse( get_option( Options::AI_CONNECTION ) );
	}

	/**
	 * A key the provider rejects is not saved.
	 *
	 * @return void
	 */
	public function test_connect_does_not_save_a_rejected_key() {
		$this->provider->models = new WP_Error( 'tasty_recipes_ai_invalid_key', 'Bad key' );

		$result = Connection::connect( 'fake', self::FAKE_KEY );

		$this->assertSame( 'tasty_recipes_ai_invalid_key', $result->get_error_code() );
		$this->assertFalse( get_option( Options::AI_CONNECTION ) );
	}

	/**
	 * A key with no image-reading models is refused.
	 *
	 * @return void
	 */
	public function test_connect_requires_vision_models() {
		$this->provider->models = array();

		$result = Connection::connect( 'fake', self::FAKE_KEY );

		$this->assertSame( 'tasty_recipes_ai_no_vision_models', $result->get_error_code() );
		$this->assertFalse( get_option( Options::AI_CONNECTION ) );
	}

	/**
	 * Picking another listed model saves it and turns off auto mode.
	 *
	 * @return void
	 */
	public function test_set_model_saves_a_listed_model() {
		$this->connect_fake_provider();

		$this->assertTrue( Connection::set_model( 'fake-vision-1' ) );
		$this->assertSame( 'fake-vision-1', Connection::get_model() );
		$this->assertFalse( Connection::get_public_status()['modelIsAuto'] );
	}

	/**
	 * Models the key can't use, and malformed IDs, are rejected.
	 *
	 * @return void
	 */
	public function test_set_model_rejects_unlisted_and_invalid_models() {
		$this->connect_fake_provider();

		$this->assertSame( 'tasty_recipes_ai_bad_model', Connection::set_model( 'other-model' )->get_error_code() );
		$this->assertSame( 'tasty_recipes_ai_bad_model', Connection::set_model( '../../etc' )->get_error_code() );
		$this->assertSame( 'fake-vision-2', Connection::get_model() );
	}

	/**
	 * A model can't be picked before connecting.
	 *
	 * @return void
	 */
	public function test_set_model_requires_a_connection() {
		$this->assertSame( 'tasty_recipes_ai_not_connected', Connection::set_model( 'fake-vision-1' )->get_error_code() );
	}

	/**
	 * Auto mode moves to the newest recommended model when the list refreshes.
	 *
	 * @return void
	 */
	public function test_auto_model_follows_newest_release() {
		$this->connect_fake_provider();

		$this->provider->models[] = array(
			'id'    => 'fake-vision-3',
			'label' => 'Fake Vision 3',
		);
		Connection::get_models( true );

		$this->assertSame( 'fake-vision-3', Connection::get_model() );
	}

	/**
	 * A model picked by hand stays when the list refreshes.
	 *
	 * @return void
	 */
	public function test_manual_model_is_kept_on_refresh() {
		$this->connect_fake_provider();
		Connection::set_model( 'fake-vision-1' );

		$this->provider->models[] = array(
			'id'    => 'fake-vision-3',
			'label' => 'Fake Vision 3',
		);
		Connection::get_models( true );

		$this->assertSame( 'fake-vision-1', Connection::get_model() );
	}

	/**
	 * Disconnecting removes the key, model and cached models.
	 *
	 * @return void
	 */
	public function test_disconnect_clears_everything() {
		$this->connect_fake_provider();

		Connection::disconnect();

		$this->assertFalse( get_option( Options::AI_CONNECTION ) );
		$this->assertFalse( get_option( Options::AI_MODEL ) );
		$this->assertFalse( get_transient( Connection::MODELS_TRANSIENT ) );
		$this->assertFalse( Connection::has_key() );
		$this->assertFalse( Connection::get_public_status()['connected'] );
	}

	/**
	 * A key that can no longer be decrypted (for example after the salts
	 * changed) asks for a reconnect instead of failing silently.
	 *
	 * @return void
	 */
	public function test_unreadable_key_needs_reconnect() {
		$this->connect_fake_provider();

		$stored        = get_option( Options::AI_CONNECTION );
		$stored['key'] = Vault::encrypt( self::FAKE_KEY, 'another-context' );
		update_option( Options::AI_CONNECTION, $stored );

		$status = Connection::get_public_status();

		$this->assertFalse( $status['connected'] );
		$this->assertTrue( $status['needsReconnect'] );
		$this->assertWPError( Connection::get_api_key() );
	}

	/**
	 * The public status never includes the key.
	 *
	 * @return void
	 */
	public function test_public_status_never_includes_the_key() {
		$this->connect_fake_provider();

		$status = Connection::get_public_status();

		$this->assertTrue( $status['connected'] );
		$this->assertSame( 'fake', $status['provider'] );
		$this->assertSame( 'Fake Vision 2', $status['modelLabel'] );
		$this->assertSame( 'abcd', $status['keyHint'] );
		$this->assertStringNotContainsString( self::FAKE_KEY, wp_json_encode( $status ) );
		$this->assertStringNotContainsString( get_option( Options::AI_CONNECTION )['key'], wp_json_encode( $status ) );
	}

	/**
	 * A key defined in wp-config.php is used instead of a stored one and is
	 * never written to the database.
	 *
	 * Constants last for the whole run, so this uses its own provider ID.
	 *
	 * @return void
	 */
	public function test_constant_key_takes_priority() {
		$provider     = new Tasty_Recipes_Fake_AI_Provider();
		$provider->id = 'fake-constant';
		add_filter(
			'tasty_recipes_ai_providers',
			static function ( $providers ) use ( $provider ) {
				$providers[] = $provider;
				return $providers;
			}
		);

		$constant = $provider->get_key_constant();
		$this->assertSame( 'TASTY_RECIPES_FAKE_CONSTANT_API_KEY', $constant );
		if ( ! defined( $constant ) ) {
			define( $constant, 'constant-key-0123456789-wxyz' );
		}

		$models = Connection::connect( 'fake-constant', 'ignored-typed-key-0123456789' );

		$this->assertIsArray( $models );
		$this->assertSame( array( 'constant-key-0123456789-wxyz' ), $provider->listed_keys );
		$this->assertSame( Connection::SOURCE_CONSTANT, Connection::get_key_source() );
		$this->assertSame( 'constant-key-0123456789-wxyz', Connection::get_api_key() );

		$stored = get_option( Options::AI_CONNECTION );
		$this->assertSame( '', $stored['key'] );
		$this->assertSame( '', $stored['key_hint'] );

		$status    = Connection::get_public_status();
		$providers = array_column( $status['providers'], 'hasConstantKey', 'id' );
		$this->assertSame( '', $status['keyHint'] );
		$this->assertTrue( $providers['fake-constant'] );
		$this->assertFalse( $providers['fake'] );
		$this->assertStringNotContainsString( 'constant-key-0123456789-wxyz', wp_json_encode( $status ) );
	}
}

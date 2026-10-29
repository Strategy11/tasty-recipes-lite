<?php
/**
 * Test case for the AI usage data in the telemetry snapshot.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Client;
use Tasty_Recipes\Options;
use Tasty_Recipes\Telemetry;

/**
 * Test case for the AI usage data in the telemetry snapshot.
 */
class Test_AI_Telemetry extends Tasty_Recipes_AI_Testcase {

	/**
	 * Clears consent.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		delete_option( Options::ONBOARDING_USAGE_DATA );

		parent::tearDown();
	}

	/**
	 * Finished AI requests are counted per feature through the after-request
	 * action.
	 *
	 * @return void
	 */
	public function test_ai_requests_are_counted_per_feature() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		$this->connect_fake_provider();

		Client::generate( 'Hi', array( 'feature' => 'recipe_assist' ) );
		Client::generate( 'Hi', array( 'feature' => 'recipe_assist' ) );
		$this->provider->reply = new WP_Error( 'tasty_recipes_ai_empty', 'Empty' );
		Client::generate( 'Hi', array( 'feature' => 'recipe_assist' ) );
		Client::generate( 'Hi' );

		$this->assertSame(
			array(
				'recipe_assist' => array(
					'success' => 2,
					'failure' => 1,
				),
				'other'         => array(
					'success' => 0,
					'failure' => 1,
				),
			),
			get_option( Options::TELEMETRY_AI_REQUESTS )
		);
	}

	/**
	 * Nothing is counted without usage consent.
	 *
	 * @return void
	 */
	public function test_ai_requests_need_consent() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'no' );

		Telemetry::record_ai_request( 'fake', 'fake-vision-2', true, 'recipe_assist' );

		$this->assertFalse( get_option( Options::TELEMETRY_AI_REQUESTS ) );
	}

	/**
	 * Feature IDs are cleaned, and the number of features is capped.
	 *
	 * @return void
	 */
	public function test_ai_feature_ids_are_cleaned_and_capped() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );

		Telemetry::record_ai_request( 'fake', 'm', true, 'Recipe Assist<script>' );
		for ( $i = 0; $i < Telemetry::AI_FEATURE_LIMIT + 5; $i++ ) {
			Telemetry::record_ai_request( 'fake', 'm', true, 'feature_' . $i );
		}
		// Known features keep counting after the cap.
		Telemetry::record_ai_request( 'fake', 'm', true, 'feature_0' );

		$requests = get_option( Options::TELEMETRY_AI_REQUESTS );

		$this->assertArrayHasKey( 'recipeassistscript', $requests );
		$this->assertCount( Telemetry::AI_FEATURE_LIMIT, $requests );
		$this->assertSame( 2, $requests['feature_0']['success'] );
	}

	/**
	 * The snapshot reports the connection and request totals, and never the
	 * key or its hint.
	 *
	 * @return void
	 */
	public function test_snapshot_includes_ai_usage_without_the_key() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		$this->connect_fake_provider();
		Client::generate( 'Hi', array( 'feature' => 'recipe_from_image' ) );

		$payload = Telemetry::build_payload();
		$ai      = $payload['properties']['ai'];

		$this->assertSame(
			array(
				'connected'  => true,
				'provider'   => 'fake',
				'model'      => 'fake-vision-2',
				'model_auto' => true,
				'key_source' => 'database',
				'requests'   => array(
					'recipe_from_image' => array(
						'success' => 1,
						'failure' => 0,
					),
				),
			),
			$ai
		);

		$json = wp_json_encode( $payload );
		$this->assertStringNotContainsString( self::FAKE_KEY, $json );
		$this->assertStringNotContainsString( get_option( Options::AI_CONNECTION )['key'], $json );
		$this->assertStringNotContainsString( 'key_hint', $json );
	}

	/**
	 * Sites without a connection report it as off.
	 *
	 * @return void
	 */
	public function test_snapshot_without_connection() {
		$ai = Telemetry::build_payload()['properties']['ai'];

		$this->assertFalse( $ai['connected'] );
		$this->assertSame( '', $ai['provider'] );
		$this->assertSame( '', $ai['key_source'] );
		$this->assertSame( array(), $ai['requests'] );
	}
}

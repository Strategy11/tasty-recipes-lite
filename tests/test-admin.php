<?php
/**
 * Test case for the Tasty_Recipes\Admin controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Admin;
use Tasty_Recipes\Settings;

/**
 * Test case for the Tasty_Recipes\Admin controller class.
 */
class Test_Admin extends Tasty_Recipes_Testcase {

	/**
	 * PHP and plugin version should be appended to UA string.
	 */
	public function test_filter_http_request_args() {
		$r = array(
			'method'     => 'POST',
			'user-agent' => 'foo bar',
		);
		$r = Admin::filter_http_request_args( $r, Admin::STORE_URL );
		// First instance shouldn't be modified because it doesn't have a 'body'.
		$this->assertEquals( 'foo bar', $r['user-agent'] );
		$r['body'] = array(
			'item_name' => Admin::ITEM_NAME,
		);
		$r         = Admin::filter_http_request_args( $r, Admin::STORE_URL );
		$this->assertStringContainsString( 'foo bar; PHP/', $r['user-agent'] );
	}

	/**
	 * Ensure customization sanitization works as expected.
	 */
	public function test_sanitize_customization_option() {
		$this->assertEquals(
			array(
				'primary_color'   => '',
				'secondary_color' => '#FFF',
				'icon_color'      => 'rgba(1,1,1,0.1)',
				'h2_transform'    => '',
				'h3_transform'    => 'uppercase',
				'body_color'      => '#efefef',
			),
			Settings::sanitize_customization_option(
				array(
					'primary_color'   => 'abc123',
					'secondary_color' => '#FFF',
					'icon_color'      => 'rgba( 1, 1, 1, .1)',
					'invalid'         => 'ignore',
					'h2_transform'    => 'foobar',
					'h3_transform'    => 'uppercase',
					'body_color'      => '#efefef',
				)
			)
		);
	}

	public function test_handle_quick_links_backward_compatibility() {
		$data = array(
			'shouldReturnBothLinksWithBoth'                => array(
				'input'    => 'both',
				'expected' => array(
					'jump',
					'print',
				),
			),
			'shouldReturnNoLinksWithEmptyStringInput'      => array(
				'input'    => '',
				'expected' => array(),
			),
			'shouldReturnjumpArrayLinksWithjumpString'     => array(
				'input'    => 'jump',
				'expected' => array(
					'jump',
				),
			),
			'shouldReturnprintArrayLinksWithprintString'   => array(
				'input'    => 'print',
				'expected' => array(
					'print',
				),
			),
			'shouldReturnBothArrayLinksWithBothArrayInput' => array(
				'input'    => array(
					'jump',
					'print',
				),
				'expected' => array(
					'jump',
					'print',
				),
			),
			'shouldReturnjumpArrayLinksWithjumpArrayInput' => array(
				'input'    => array(
					'jump',
				),
				'expected' => array(
					'jump',
				),
			),
			'shouldReturnprintArrayLinksWithprintArrayInput' => array(
				'input'    => array(
					'print',
				),
				'expected' => array(
					'print',
				),
			),
		);

		foreach ( $data as $item ) {
			$this->assertSame( $item['expected'], Settings::handle_quick_links_backward_compatibility( $item['input'] ) );
		}
	}

	/**
	 * Editors can access the recipes menu capability.
	 *
	 * @since x.x
	 */
	public function test_editor_can_access_recipes() {
		$current_user = get_current_user_id();
		$editor_id    = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor_id );

		$items = $this->get_filtered_recipes_menu_item();
		$this->assertSame( Admin::CONTENT_CAPABILITY, $items['tasty-recipes']['capability'] );
		$this->assertTrue( current_user_can( $items['tasty-recipes']['capability'] ) );
		$this->assertFalse( current_user_can( Admin::CAPABILITY ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Authors cannot access the recipes menu capability.
	 *
	 * @since x.x
	 */
	public function test_author_cant_access_recipes() {
		$current_user = get_current_user_id();
		$author_id    = $this->factory->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $author_id );

		$items = $this->get_filtered_recipes_menu_item();
		$this->assertFalse( current_user_can( $items['tasty-recipes']['capability'] ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Administrators can access the recipes menu capability.
	 *
	 * @since x.x
	 */
	public function test_admin_can_access_recipes() {
		$current_user = get_current_user_id();
		$admin_id     = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$items = $this->get_filtered_recipes_menu_item();
		$this->assertTrue( current_user_can( $items['tasty-recipes']['capability'] ) );
		$this->assertTrue( current_user_can( Admin::CAPABILITY ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Get the recipes menu item after the plugin filter.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private function get_filtered_recipes_menu_item() {
		return Settings::add_menu_item(
			array(
				'tasty-recipes' => array(
					'title'      => 'Tasty Recipes Lite',
					'capability' => 'manage_options',
					'callback'   => '',
				),
			)
		);
	}
}

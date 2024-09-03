<?php
/**
 * Test case for the Tasty_Recipes\Admin controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Admin;

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
			Admin::sanitize_customization_option(
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
			$this->assertSame( $item['expected'], Admin::handle_quick_links_backward_compatibility( $item['input'] ) );
		}
	}
}

<?php
/**
 * Tests the Utils class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Utils;

/**
 * Tests the Utils controller class.
 */
class Test_Utils extends Tasty_Recipes_Testcase {

	/**
	 * Ensures expected behavior when parsing the ID from a YouTube URL.
	 */
	public function test_get_youtube_id() {
		$this->assertEquals( 'b_CmkUT25BE', Utils::get_youtube_id( 'https://www.youtube.com/watch?v=b_CmkUT25BE' ) );
		$this->assertEquals( 'b_CmkUT25BE', Utils::get_youtube_id( 'https://youtu.be/b_CmkUT25BE' ) );
		$this->assertEquals( 'HZpnBPiCYnA', Utils::get_youtube_id( 'https://www.youtube.com/embed/HZpnBPiCYnA?feature=oembed' ) );
		$this->assertEquals( 'JnLsjVy3soI', Utils::get_youtube_id( 'https://youtube.com/shorts/JnLsjVy3soI' ) );
		$this->assertFalse( Utils::get_youtube_id( 'https://vimeo.com/69044331' ) );
		$this->assertFalse( Utils::get_youtube_id( '' ) );
	}

	/**
	 * Ensures expected behavior when parsing the src from an iframe.
	 */
	public function test_get_element_attribute() {
		$this->assertEquals( 'https://www.youtube.com/embed/HZpnBPiCYnA?feature=oembed', Utils::get_element_attribute( '<iframe width="740" height="416" src="https://www.youtube.com/embed/HZpnBPiCYnA?feature=oembed" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen>', 'iframe', 'src' ) );
		$this->assertEquals( 'https://apple.com', Utils::get_element_attribute( '<a href="https://apple.com"></a>', 'a', 'href' ) );
		$this->assertFalse( Utils::get_element_attribute( 'foobar', 'a', 'href' ) );
	}

}

<?php
/**
 * Common engine class.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Utils;

use Tasty\Framework\Main;
use Tasty\Framework\Utils\Url;
use Tasty\Framework\Utils\Vars;

/**
 * Common engine class for reusable components.
 */
class Common {

	/**
	 * Show a message about joining the affiliate program.
	 *
	 * @return void
	 */
	public static function affiliate_banner() {
		$utm_params = array(
			'utm_medium'  => 'banner',
			'utm_content' => Vars::get_param( 'page' ),
		);
		$link       = Url::add_utm_params( 'affiliate', $utm_params );
		?>
		<div class="tasty-banner-grey tasty-flex">
			<img src="<?php echo esc_url( Main::plugin_url( 'assets/images/affiliate-icon.svg' ) ); ?>"
				width="50" height="46" data-pin-nopin="true" alt="affiliate program graphic" />
			<p>
				Get started today earning <strong>30% commissions</strong>
				on all referrals with the WP Tasty Affiliate Program!
				<a href="<?php echo esc_url( $link ); ?>" target="_blank"><strong>Join today!</strong></a>
			</p>
			<img src="<?php echo esc_url( Main::plugin_url( 'assets/images/affiliate-icon.svg' ) ); ?>"
				width="50" height="46" data-pin-nopin="true" alt="affiliate program graphic" />
		</div>
		<?php
	}

	/**
	 * Show the settings tabs.
	 *
	 * @param array $tabs The menu items to show.
	 *
	 * @return void
	 */
	public static function show_tabs( $tabs ) {
		$template = tasty_get_admin_template();
		$template->render( 'tabs', array( 'tabs' => $tabs ), true );
	}
}

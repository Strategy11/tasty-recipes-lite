<?php

namespace {
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
	define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
	define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
	define( 'EMPTY_TRASH_DAYS', 30 );
	define( 'ABSPATH', realpath( __FILE__ . '/../../../../' ) );
	define( 'WP_PLUGIN_DIR', realpath( __FILE__ . '/../' ) );
	define( 'WPINC', 'wp-includes' );
	define( 'OBJECT', 'OBJECT' );
	define( 'OBJECT_K', 'OBJECT_K' );
	define( 'ARRAY_A', 'ARRAY_A' );
	define( 'EP_PAGES', 4096 );
	define( 'EP_PERMALINK', 1 );
	define( 'COOKIEHASH', '' );
	define( 'COOKIE_DOMAIN', false );
	define( 'COOKIEPATH', '/' );
	define( 'DB_USER', 'root' );
	define( 'DB_PASSWORD', 'root' );
	define( 'DB_HOST', 'localhost' );

	class BigScoots_Cache {
		public static function clear_cache( $post_id ) {
		}
	}
}

namespace Elementor {
	abstract class Base_Object {
	}

	abstract class Base_Control extends Base_Object {
	}

	abstract class Base_Data_Control extends Base_Control {
		protected function get_control_uid( $input_type = 'default' ) {
		}
	}

	abstract class Widget_Base {
		public function start_controls_section( $section_id, array $args = array() ) {
		}
		public function add_control( $id, array $args, $options = array() ) {
		}
		public function end_controls_section() {
		}
		public function get_settings_for_display( $setting_key = null ) {
		}
	}

	class Controls_Manager {
		const TAB_CONTENT = 'content';
	}

	class Plugin {
		/**
		 * @return Plugin
		 */
		public static function instance() {
		}
	}
}

namespace WP_CLI\Utils {
	/**
	 * @param string       $format Format to use: 'table', 'json', 'csv', 'yaml', 'ids', 'count'.
	 * @param array        $items  An array of items to output.
	 * @param array|string $fields Named fields for each item of data. Can be array or comma-separated list.
	 */
	function format_items( $format, $items, $fields ) {
	}
}

namespace Yoast\WP\SEO\Config {

	class Schema_IDs {
		const ARTICLE_HASH = '#article';
	}
}

namespace Yoast\WP\SEO\Context {
	class Meta_Tags_Context {
	}
}

namespace Yoast\WP\SEO\Generators\Schema {
	abstract class Abstract_Schema_Piece {
		public $context;
	}
}

namespace Tasty_Recipes_Pro {
	class Ratings {
		public static function get_minimum_rating_without_comment( $comment_data = array() ) {
		}
	}
}

namespace Tasty\Framework\Admin\License{
	class Updater {
		public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
		}

		public function init() {
		}

		public function check_update( $_transient_data ) {
		}

		public function get_repo_api_data() {
		}

		public function show_update_notification( $file, $plugin ) {
		}

		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		}

		public function http_request_args( $args, $url ) {
		}

		public function show_changelog() {
		}

		public function get_cached_version_info( $cache_key = '' ) {
		}

		public function set_version_info_cache( $value = '', $cache_key = '' ) {
		}
	}
}

namespace Tasty\Framework\Utils {
	class Common {
		/**
		 * @param array $attributes Array of attributes to render.
		 *
		 * @return void
		 */
		public static function render_html_attributes( $attributes ) {
		}
	}
}

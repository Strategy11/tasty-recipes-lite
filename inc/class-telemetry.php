<?php
/**
 * Collects and sends usage snapshots when consent is granted.
 * 
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

/**
 * Collects and sends usage snapshots when consent is granted.
 *
 * @since x.x
 */
class Telemetry {

	/**
	 * Cron hook name for the daily usage snapshot.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const CRON_HOOK = 'tasty_recipes_telemetry_snapshot';

	/**
	 * Telemetry endpoint.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const DEFAULT_ENDPOINT = 'https://telemetry.wptasty.com/e';

	/**
	 * Max parent posts to inspect for editor engagement.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const EDITOR_ENGAGEMENT_PARENT_LIMIT = 200;

	/**
	 * Register telemetry hooks.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'init', array( __CLASS__, 'maybe_schedule_cron' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'send_usage_snapshot' ) );
		add_action( 'update_option_' . Options::ONBOARDING_USAGE_DATA, array( __CLASS__, 'handle_consent_option_change' ), 10, 2 );
		add_action( 'add_option_' . Options::ONBOARDING_USAGE_DATA, array( __CLASS__, 'handle_consent_option_add' ), 10, 2 );
	}

	/**
	 * Whether usage tracking consent is effectively granted.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function has_usage_consent() {
		$stored = get_option( Options::ONBOARDING_USAGE_DATA, null );

		if ( 'yes' === $stored ) {
			return true;
		}

		if ( 'no' === $stored ) {
			return false;
		}

		/**
		 * Filters the default usage consent when the option is unset.
		 *
		 * Lite defaults to opt-in (false). Extensions may return true.
		 *
		 * @since x.x
		 *
		 * @param bool $default Default consent when unset.
		 */
		return (bool) apply_filters( 'tasty_recipes_usage_consent_default', false );
	}

	/**
	 * Schedule or clear the daily snapshot cron based on consent.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function maybe_schedule_cron() {
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( self::has_usage_consent() ) {
			if ( ! $scheduled ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
			}
			return;
		}

		if ( $scheduled ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}
	}

	/**
	 * Handle consent option updates.
	 *
	 * @since x.x
	 *
	 * @param mixed $old_value Previous option value.
	 * @param mixed $value     New option value.
	 *
	 * @return void
	 */
	public static function handle_consent_option_change( $old_value, $value ) {
		self::maybe_schedule_cron();

		if ( 'yes' === $value && 'yes' !== $old_value ) {
			self::send_usage_snapshot();
		}
	}

	/**
	 * Handle first-time consent option creation.
	 *
	 * @since x.x
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 *
	 * @return void
	 */
	public static function handle_consent_option_add( $option, $value ) {
		unset( $option );
		self::maybe_schedule_cron();

		if ( 'yes' === $value ) {
			self::send_usage_snapshot();
		}
	}

	/**
	 * Build and send a usage snapshot.
	 *
	 * @since x.x
	 *
	 * @return bool True when the request received a 2xx response.
	 */
	public static function send_usage_snapshot() {
		if ( ! self::has_usage_consent() ) {
			return false;
		}

		$payload = self::build_payload();

		$response = wp_remote_post(
			self::DEFAULT_ENDPOINT,
			array(
				'timeout'  => 5,
				'blocking' => true,
				'headers'  => array(
					'Content-Type' => 'application/json',
				),
				'body'     => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$success = $code >= 200 && $code < 300;

		if ( $success ) {
			/**
			 * Fires after a usage snapshot is sent successfully.
			 *
			 * @since x.x
			 *
			 * @param array $payload Payload that was sent.
			 */
			do_action( 'tasty_recipes_telemetry_snapshot', $payload );
		}

		return $success;
	}

	/**
	 * Build the usage snapshot payload.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function build_payload() {
		$site_id = self::get_site_id();

		$properties = array(
			'$process_person_profile' => false,
			'$groups'                 => array(
				'site' => $site_id,
			),
			'plugin_version'          => defined( 'TASTY_RECIPES_LITE_VERSION' ) ? TASTY_RECIPES_LITE_VERSION : '',
			'product'                 => 'lite',
			'wp_version'              => self::get_wp_version(),
			'php_version'             => PHP_VERSION,
			'active_theme'            => self::get_active_theme(),
			'template'                => (string) get_option( Options::TEMPLATE, 'default' ),
			'template_variation'      => (int) get_option( Options::TEMPLATE_VARIATION, 1 ),
			'ratings_enabled'         => Ratings::is_enabled( 'any' ),
			'recipe_count'            => self::get_recipe_count(),
			'editor_engagement'       => self::get_editor_engagement(),
			'features'                => self::get_features(),
		);

		/**
		 * Filters telemetry properties before send.
		 *
		 * @since x.x
		 *
		 * @param array $properties Snapshot properties.
		 */
		$properties = apply_filters( 'tasty_recipes_telemetry_properties', $properties );

		return array(
			'event'       => 'tasty_recipes_usage_snapshot',
			'distinct_id' => $site_id,
			'properties'  => $properties,
		);
	}

	/**
	 * Get or create the stable site UUID used as distinct_id.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_site_id() {
		$site_id = get_option( Options::TELEMETRY_SITE_ID, '' );

		if ( is_string( $site_id ) && '' !== $site_id ) {
			return $site_id;
		}

		$site_id = wp_generate_uuid4();
		update_option( Options::TELEMETRY_SITE_ID, $site_id, false );

		return $site_id;
	}

	/**
	 * Collect Lite-owned feature toggle state.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_features() {
		$features = array(
			'enable_taxonomy_links' => self::is_option_enabled( Options::ENABLE_TAXONOMY_LINKS ),
			'poweredby'             => self::is_option_enabled( Options::POWEREDBY ),
			'ai_scraper_prevention' => self::is_option_enabled( Options::AI_SCRAPER_PREVENTION ),
			'shareasale'            => '' !== (string) get_option( Options::SHAREASALE, '' ),
			'copy_to_clipboard'     => self::is_option_enabled( Options::COPY_TO_CLIPBOARD ),
			'quick_links'           => array_values( array_filter( (array) get_option( Options::QUICK_LINKS, array() ) ) ),
			'card_buttons'          => array_values( array_filter( (array) get_option( Options::CARD_BUTTONS, array() ) ) ),
		);

		/**
		 * Filters the feature toggle snapshot.
		 *
		 * @since x.x
		 *
		 * @param array $features Feature toggles currently in use.
		 */
		return apply_filters( 'tasty_recipes_telemetry_features', $features );
	}

	/**
	 * Count published tasty_recipe posts.
	 *
	 * @since x.x
	 *
	 * @return int
	 */
	private static function get_recipe_count() {
		$counts = wp_count_posts( 'tasty_recipe' );

		if ( ! is_object( $counts ) || ! isset( $counts->publish ) ) {
			return 0;
		}

		return (int) $counts->publish;
	}

	/**
	 * Infer editor engagement from parent posts that embed recipes.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_editor_engagement() {
		global $wpdb;

		$engagement = array(
			'block'     => 0,
			'shortcode' => 0,
			'embed'     => 0,
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$embed_count         = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				Meta_Keys::RECIPE_PARENTS
			)
		);
		$engagement['embed'] = (int) $embed_count;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$parent_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d",
				Meta_Keys::RECIPE_PARENTS,
				self::EDITOR_ENGAGEMENT_PARENT_LIMIT * 2
			)
		);

		if ( ! is_array( $parent_ids ) ) {
			return $engagement;
		}

		$inspected = 0;
		foreach ( $parent_ids as $parent_id ) {
			if ( ! is_numeric( $parent_id ) ) {
				continue;
			}

			$post = get_post( (int) $parent_id );
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			if ( function_exists( 'has_block' ) && has_block( Block_Editor::RECIPE_BLOCK_TYPE, $post ) ) {
				++$engagement['block'];
			}

			if ( has_shortcode( $post->post_content, Shortcodes::RECIPE_SHORTCODE ) ) {
				++$engagement['shortcode'];
			}

			++$inspected;
			if ( $inspected >= self::EDITOR_ENGAGEMENT_PARENT_LIMIT ) {
				break;
			}
		}

		return $engagement;
	}

	/**
	 * Whether a stored option value represents an enabled toggle.
	 *
	 * @since x.x
	 *
	 * @param string $option_name    Option name.
	 * @param mixed  $default_value  Default when unset.
	 *
	 * @return bool
	 */
	private static function is_option_enabled( $option_name, $default_value = false ) {
		$value = get_option( $option_name, $default_value );

		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (int) $value > 0;
		}

		if ( ! is_string( $value ) ) {
			return ! empty( $value );
		}

		$disabled_values = array( '', '0', 'off', 'false', 'no' );

		return ! in_array( strtolower( $value ), $disabled_values, true );
	}

	/**
	 * Current WordPress version string.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_wp_version() {
		global $wp_version;

		return is_string( $wp_version ) ? $wp_version : '';
	}

	/**
	 * Active theme stylesheet slug.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function get_active_theme() {
		$theme = wp_get_theme();

		if ( ! $theme instanceof \WP_Theme ) {
			return (string) get_stylesheet();
		}

		$stylesheet = $theme->get_stylesheet();

		return is_string( $stylesheet ) ? $stylesheet : (string) get_stylesheet();
	}
}

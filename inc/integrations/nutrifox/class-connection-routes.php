<?php
/**
 * REST routes and OAuth callback for the Nutrifox connection.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations\Nutrifox;

use Tasty_Recipes\Editor;
use Tasty_Recipes\Utils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST routes and OAuth callback for the Nutrifox connection.
 *
 * @since x.x
 */
class Connection_Routes {

	/**
	 * REST namespace.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const REST_NAMESPACE = 'tasty-recipes/v1';

	/**
	 * REST route base.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const REST_BASE = 'nutrifox';

	/**
	 * Registers the hooks.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'admin_post_' . Connection::CALLBACK_ACTION, array( __CLASS__, 'handle_callback' ) );
		add_action( 'admin_post_nopriv_' . Connection::CALLBACK_ACTION, array( __CLASS__, 'handle_callback' ) );
	}

	/**
	 * Registers the REST routes.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/connect',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'connect' ),
				'permission_callback' => array( __CLASS__, 'can_manage_connection' ),
				'args'                => array(
					'intent' => array(
						'type'    => 'string',
						'enum'    => array( 'connect', 'trial' ),
						'default' => 'connect',
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'status' ),
				'permission_callback' => array( __CLASS__, 'can_view_connection' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/disconnect',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'disconnect' ),
				'permission_callback' => array( __CLASS__, 'can_manage_connection' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/labels',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_label' ),
				'permission_callback' => array( __CLASS__, 'can_edit_labels' ),
				'args'                => array(
					'title'       => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ingredients' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'wp_kses_post',
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/labels/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_label' ),
				'permission_callback' => array( __CLASS__, 'can_edit_labels' ),
				'args'                => array(
					'id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Whether the current user may check the connection status.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_view_connection() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Whether the current user may build or link labels, matching the recipe modal's create and edit capability.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_edit_labels() {
		return Editor::rest_check_permission();
	}

	/**
	 * Whether the current user may connect or disconnect the site.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_manage_connection() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns the Nutrifox authorization URL for the popup.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function connect( WP_REST_Request $request ) {
		$url = Connection::get_authorize_url( (string) $request->get_param( 'intent' ) );

		if ( is_wp_error( $url ) ) {
			return $url;
		}

		return rest_ensure_response( array( 'url' => $url ) );
	}

	/**
	 * Returns the connection status, confirmed with Nutrifox when stale.
	 *
	 * @since x.x
	 *
	 * @return WP_REST_Response
	 */
	public static function status() {
		$status = Connection::check_status();

		return rest_ensure_response(
			array(
				'status'       => $status,
				'email'        => Connection::get_email(),
				'connectedFor' => Connection::get_connected_for(),
			)
		);
	}

	/**
	 * Builds a Nutrifox label from the recipe in the modal.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function create_label( WP_REST_Request $request ) {
		$title = (string) $request->get_param( 'title' );
		$lines = Labels::get_ingredient_lines( (string) $request->get_param( 'ingredients' ) );

		if ( '' === trim( $title ) || ! $lines ) {
			return new WP_Error(
				'tasty_recipes_nutrifox_label',
				__( 'Add a recipe title and at least one ingredient before building a label.', 'tasty-recipes-lite' ),
				array( 'status' => 400 )
			);
		}

		$label = Labels::create( $title, $lines );

		return is_wp_error( $label ) ? $label : rest_ensure_response( $label );
	}

	/**
	 * Returns a Nutrifox label's review state, or a 404 when no such label exists.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_label( WP_REST_Request $request ) {
		$label_id = (int) $request->get_param( 'id' );
		$label    = Labels::get( $label_id );

		if ( ! is_wp_error( $label ) ) {
			return rest_ensure_response( $label );
		}

		$exists = Labels::exists( $label_id );
		if ( is_wp_error( $exists ) ) {
			return $exists;
		}

		if ( ! $exists ) {
			return new WP_Error(
				'tasty_recipes_nutrifox_label_not_found',
				// translators: %d: Nutrifox recipe ID.
				sprintf( __( 'No Nutrifox recipe found with ID %d. Check the ID and try again.', 'tasty-recipes-lite' ), $label_id ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response(
			array(
				'id'                  => $label_id,
				'pending_ingredients' => array(),
				'edit_url'            => '',
			)
		);
	}

	/**
	 * Disconnects the site from Nutrifox, revoking its access there.
	 *
	 * @since x.x
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function disconnect() {
		$result = Connection::disconnect();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'status'       => Connection::get_status(),
				'email'        => '',
				'connectedFor' => '',
			)
		);
	}

	/**
	 * Handles the OAuth redirect from Nutrifox inside the popup.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function handle_callback() {
		nocache_headers();

		if ( ! self::can_manage_connection() ) {
			self::render_callback_page( 'error', __( 'Please log in to WordPress as an administrator and try connecting again.', 'tasty-recipes-lite' ) );
		}

		$result = Connection::complete_authorization(
			Utils::get_param( 'state' ),
			Utils::get_param( 'code' ),
			Utils::get_param( 'error' )
		);

		if ( is_wp_error( $result ) ) {
			self::render_callback_page( 'error', $result->get_error_message() );
		}

		if ( 'denied' === $result ) {
			self::render_callback_page( 'denied', __( 'Connection cancelled. You can close this tab.', 'tasty-recipes-lite' ) );
		}

		self::render_callback_page( Connection::STATUS_CONNECTED, __( 'Nutrifox is connected. You can close this tab.', 'tasty-recipes-lite' ) );
	}

	/**
	 * Prints the popup page that reports the result to the recipe modal.
	 *
	 * @since x.x
	 *
	 * @param string $status  Result status.
	 * @param string $message Message shown when the popup cannot close itself.
	 *
	 * @return never
	 */
	private static function render_callback_page( $status, $message ) {
		$payload = array(
			'type'    => 'tasty-nutrifox',
			'status'  => $status,
			'message' => $message,
		);

		$script = sprintf(
			'if ( window.opener ) { window.opener.postMessage( %s, window.location.origin ); } window.close();',
			wp_json_encode( $payload )
		);
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Nutrifox', 'tasty-recipes-lite' ); ?></title>
		</head>
		<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; text-align: center; padding: 48px 24px;">
			<p><?php echo esc_html( $message ); ?></p>
			<p><a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to WordPress', 'tasty-recipes-lite' ); ?></a></p>
			<?php wp_print_inline_script_tag( $script ); ?>
		</body>
		</html>
		<?php
		exit;
	}
}

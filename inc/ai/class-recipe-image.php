<?php
/**
 * Reads a recipe from a photo with the connected AI model.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\AI;

use Tasty_Recipes\Admin;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Turns a photo of a handwritten or printed recipe into plain text.
 *
 * @since x.x
 */
final class Recipe_Image {

	/**
	 * Photos a user may read per window, to protect their provider credit.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const RATE_LIMIT = 30;

	/**
	 * Rate limit window, in seconds.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const RATE_WINDOW = 3600;

	/**
	 * Longest side the photo is scaled down to before sending. Enough to read
	 * handwriting, while keeping image tokens low.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const MAX_DIMENSION = 2000;

	/**
	 * Image types accepted from the browser.
	 *
	 * @since x.x
	 *
	 * @var string[]
	 */
	const ACCEPTED_TYPES = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic', 'image/heif' );

	/**
	 * Image types every supported provider reads directly.
	 *
	 * @since x.x
	 *
	 * @var string[]
	 */
	const SENDABLE_TYPES = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );

	/**
	 * The model's reply when the photo has no recipe.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const NO_RECIPE = 'NO_RECIPE';

	/**
	 * System instructions for the transcription. `{noun}` is filled per request.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SYSTEM_PROMPT = <<<'PROMPT'
You transcribe photos of a {noun}, handwritten or printed, into plain text.
Copy the {noun} faithfully: keep quantities, units and wording as written, and never add ingredients, steps or details that are not in the photo.
Keep the {noun} in its original language. Write only the {noun}, with no commentary and no Markdown.
PROMPT;

	/**
	 * The transcription request. The layout matches what the paste parser
	 * recognizes, so the text sorts into the right fields. `{noun}` is filled
	 * per request.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const PROMPT = <<<'PROMPT'
Transcribe the {noun} in this photo using the layout below.
Use the labels and headings exactly as written (in English), and replace each part in [brackets] with what the photo says.
Leave out any line or section the photo doesn't have; never write a label without a value.

Title: [{noun} title]
Description: [short description]
Prep Time: [time]
Cook Time: [time]
Total Time: [time]
Yield: [servings or amount]
Method: [cooking method]
Video: [video URL]

Ingredients
[one ingredient or item per line; start a group with a line like "For the sauce:"]

Instructions
[one step per line, numbered: 1. … 2. …]

Notes
[any notes or tips]

If the photo doesn't contain a {noun}, reply with only: NO_RECIPE
PROMPT;

	/**
	 * Registers the route.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			Rest::NAMESPACE,
			'/ai/recipe-from-image',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'read_photo' ),
				'permission_callback' => array( __CLASS__, 'can_use' ),
			)
		);
	}

	/**
	 * Anyone who can write recipes can read a recipe photo.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function can_use() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * What the recipe editor needs to know to offer photo reading.
	 *
	 * @since x.x
	 *
	 * @return array{ready: bool, provider: string, model: string, settingsUrl: string, maxUploadSize: int, maxUploadLabel: string}
	 */
	public static function get_editor_status() {
		$status   = Connection::get_public_status();
		$provider = Connection::get_connected_provider();

		return array(
			'ready'          => $status['connected'] && '' !== $status['model'],
			'provider'       => $provider ? $provider->get_label() : '',
			'model'          => $status['modelLabel'],
			'settingsUrl'    => current_user_can( Admin::CAPABILITY )
				? add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=tasty-recipes' ) )
				: '',
			'maxUploadSize'  => wp_max_upload_size(),
			'maxUploadLabel' => (string) size_format( wp_max_upload_size() ),
		);
	}

	/**
	 * POST /ai/recipe-from-image — returns the photo's recipe as text.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request Request with an `image` file and an optional recipe `type`.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function read_photo( WP_REST_Request $request ) {
		if ( ! self::get_editor_status()['ready'] ) {
			return new WP_Error(
				'tasty_recipes_ai_not_connected',
				__( 'Connect an AI provider in Tasty Recipes settings to read recipe photos.', 'tasty-recipes-lite' ),
				array( 'status' => 400 )
			);
		}

		$limited = self::check_rate_limit();
		if ( is_wp_error( $limited ) ) {
			return $limited;
		}

		$subject = Subject::get( sanitize_key( (string) $request->get_param( 'type' ) ) );
		$files   = $request->get_file_params();
		$image   = self::prepare_image( $files['image'] ?? null );

		if ( is_wp_error( $image ) ) {
			return $image;
		}

		$text = Client::generate(
			str_replace( '{noun}', $subject['noun'], self::PROMPT ),
			array(
				'system'     => str_replace( '{noun}', $subject['noun'], self::SYSTEM_PROMPT ),
				'images'     => array( $image ),
				'max_tokens' => 4096,
				'feature'    => 'recipe_from_image',
			)
		);
		unset( $image );

		if ( is_wp_error( $text ) ) {
			$text->add_data( array( 'status' => 502 ) );
			return $text;
		}

		$text = self::clean_transcription( $text );

		if ( '' === $text || self::NO_RECIPE === $text ) {
			return new WP_Error(
				'tasty_recipes_ai_no_recipe',
				$subject['not_found_message'],
				array( 'status' => 422 )
			);
		}

		$response = new WP_REST_Response( array( 'text' => $text ) );
		$response->header( 'Cache-Control', 'no-store, private' );

		return $response;
	}

	/**
	 * Validates the upload and turns it into a scaled-down image to send.
	 *
	 * @since x.x
	 *
	 * @param array|null $file Entry from $_FILES.
	 *
	 * @return array{mime: string, data: string}|WP_Error
	 */
	private static function prepare_image( $file ) {
		$invalid = new WP_Error(
			'tasty_recipes_ai_bad_image',
			__( 'Upload a JPG, PNG, WebP or HEIC photo of the recipe.', 'tasty-recipes-lite' ),
			array( 'status' => 400 )
		);

		$error = is_array( $file ) ? ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) : UPLOAD_ERR_NO_FILE;

		// The size limit is the site's own PHP upload limit.
		if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
			return new WP_Error(
				'tasty_recipes_ai_image_too_large',
				sprintf(
					/* translators: %s: maximum upload size, such as "8 MB". */
					__( 'That photo is larger than this site allows. Upload one under %s.', 'tasty-recipes-lite' ),
					size_format( wp_max_upload_size() )
				),
				array( 'status' => 413 )
			);
		}

		if ( UPLOAD_ERR_OK !== $error || empty( $file['tmp_name'] ) ) {
			return $invalid;
		}

		$path = $file['tmp_name'];

		if ( ! is_uploaded_file( $path ) ) {
			return $invalid;
		}

		// The real type, from the file's contents rather than its name.
		$mime = wp_get_image_mime( $path );

		if ( ! $mime || ! in_array( $mime, self::ACCEPTED_TYPES, true ) ) {
			return $invalid;
		}

		$scaled = self::scale_down( $path );

		if ( $scaled ) {
			return $scaled;
		}

		// No image editor on this server: send small, readable files as they are.
		if ( in_array( $mime, self::SENDABLE_TYPES, true ) && filesize( $path ) <= 4194304 ) {
			return array(
				'mime' => $mime,
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Local upload.
				'data' => base64_encode( (string) file_get_contents( $path ) ),
			);
		}

		return new WP_Error(
			'tasty_recipes_ai_image_unreadable',
			__( 'This photo couldn’t be prepared. Try a JPG or PNG under 4 MB.', 'tasty-recipes-lite' ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Scales the photo down and re-encodes it as JPEG, which also strips EXIF
	 * metadata such as location before it leaves the site.
	 *
	 * @since x.x
	 *
	 * @param string $path Upload temp file.
	 *
	 * @return array{mime: string, data: string}|null The image, or null if it couldn't be processed.
	 */
	private static function scale_down( $path ) {
		$editor = wp_get_image_editor( $path );

		if ( is_wp_error( $editor ) ) {
			return null;
		}

		$editor->resize( self::MAX_DIMENSION, self::MAX_DIMENSION, false );
		$editor->set_quality( 85 );

		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$temp  = wp_tempnam( 'tasty-recipes-photo.jpg' );
		$saved = $editor->save( $temp, 'image/jpeg' );

		if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
			wp_delete_file( $temp );
			return null;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Our own temp file.
		$bytes = file_get_contents( $saved['path'] );
		wp_delete_file( $saved['path'] );
		wp_delete_file( $temp );

		if ( ! is_string( $bytes ) || '' === $bytes ) {
			return null;
		}

		return array(
			'mime' => 'image/jpeg',
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Encoding the image for the provider's API.
			'data' => base64_encode( $bytes ),
		);
	}

	/**
	 * Removes wrappers a model sometimes adds around the transcription.
	 *
	 * @since x.x
	 *
	 * @param string $text Model output.
	 *
	 * @return string
	 */
	private static function clean_transcription( $text ) {
		$text = trim( (string) $text );
		$text = (string) preg_replace( '/^```[a-z]*\s*|\s*```$/i', '', $text );
		$text = (string) preg_replace( '/^\*\*(.+?)\*\*$/m', '$1', $text );

		return trim( wp_strip_all_tags( $text ) );
	}

	/**
	 * Limits how many photos a user can read per hour.
	 *
	 * @since x.x
	 *
	 * @return true|WP_Error
	 */
	private static function check_rate_limit() {
		$key   = 'tasty_recipes_ai_photo_rate_' . get_current_user_id();
		$count = (int) get_transient( $key );

		if ( $count >= self::RATE_LIMIT ) {
			return new WP_Error(
				'tasty_recipes_ai_too_many_requests',
				__( 'You’ve read a lot of recipe photos in the last hour. Please try again later.', 'tasty-recipes-lite' ),
				array( 'status' => 429 )
			);
		}

		set_transient( $key, $count + 1, self::RATE_WINDOW );

		return true;
	}
}

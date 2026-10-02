<?php
/**
 * Builds Nutrifox labels from recipes and keeps them in sync.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations\Nutrifox;

use Tasty_Recipes;
use Tasty_Recipes\Distribution_Metadata;
use Tasty_Recipes\Meta_Keys;
use Tasty_Recipes\Objects\Recipe;
use WP_Error;

/**
 * Builds Nutrifox labels from recipes and keeps them in sync.
 *
 * @since x.x
 */
class Labels {

	/**
	 * Registers the hooks.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'tasty_recipes_after_save_editor_data', array( __CLASS__, 'sync_after_save' ), 10, 2 );
	}

	/**
	 * Gets the plain-text ingredient lines Nutrifox should match.
	 *
	 * @since x.x
	 *
	 * @param string $ingredients Ingredients HTML.
	 *
	 * @return string[]
	 */
	public static function get_ingredient_lines( $ingredients ) {
		$lines = array();

		foreach ( Distribution_Metadata::parse_recipe_component_list( (string) $ingredients ) as $item ) {
			if ( 'heading' === $item['type'] ) {
				continue;
			}

			foreach ( (array) preg_split( '/\R/', (string) $item['value'] ) as $line ) {
				$line = trim( html_entity_decode( wp_strip_all_tags( (string) $line ), ENT_QUOTES, 'UTF-8' ) );
				if ( '' !== $line ) {
					$lines[] = mb_substr( $line, 0, 255 );
				}
			}
		}

		return array_values( array_unique( $lines ) );
	}

	/**
	 * Creates a Nutrifox recipe from a recipe title and ingredient lines.
	 *
	 * @since x.x
	 *
	 * @param string   $title Recipe title.
	 * @param string[] $lines Ingredient lines.
	 *
	 * @return array{id: int, pending_ingredients: string[], edit_url: string}|WP_Error
	 */
	public static function create( $title, $lines ) {
		$response = Connection::authorized_request(
			'POST',
			'/api/v1/recipes',
			self::json_args(
				array(
					'name'        => $title,
					'ingredients' => $lines,
				)
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 201 !== $response['code'] ) {
			return Connection::response_error( $response );
		}

		return self::to_editor_response( $response['body'] );
	}

	/**
	 * Gets a Nutrifox recipe owned by the connected account.
	 *
	 * @since x.x
	 *
	 * @param int $label_id Nutrifox recipe ID.
	 *
	 * @return array{id: int, pending_ingredients: string[], edit_url: string}|WP_Error
	 */
	public static function get( $label_id ) {
		$response = Connection::authorized_request( 'GET', '/api/v1/recipes/' . absint( $label_id ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 !== $response['code'] ) {
			return Connection::response_error( $response );
		}

		return self::to_editor_response( $response['body'] );
	}

	/**
	 * Whether a Nutrifox recipe with this ID exists, whichever account owns it.
	 *
	 * @since x.x
	 *
	 * @param int $label_id Nutrifox recipe ID.
	 *
	 * @return bool|WP_Error Error when Nutrifox can't be reached.
	 */
	public static function exists( $label_id ) {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		$response = wp_remote_get( sprintf( '%s/api/recipes/%d', Tasty_Recipes::nutrifox_url(), absint( $label_id ) ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 === $code || 404 === $code ) {
			return 200 === $code;
		}

		return Connection::response_error(
			array(
				'code' => $code,
				'body' => array(),
			)
		);
	}

	/**
	 * Pushes the recipe title and ingredients to Nutrifox after the recipe modal saves.
	 *
	 * @since x.x
	 *
	 * @param array  $post_recipe Submitted recipe data (not sanitized).
	 * @param Recipe $recipe      Saved recipe.
	 *
	 * @return void
	 */
	public static function sync_after_save( $post_recipe, $recipe ) {
		$recipe_id   = $recipe->get_id();
		$nutrifox_id = (int) $recipe->get_nutrifox_id();

		if ( empty( $post_recipe['nutrifox_sync'] ) || ! $nutrifox_id ) {
			delete_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_SYNC );
			return;
		}

		$sync  = get_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_SYNC, true );
		$title = (string) $recipe->get_title();
		$lines = self::get_ingredient_lines( $recipe->get_ingredients() );
		$hash  = md5( (string) wp_json_encode( array( $nutrifox_id, $title, $lines ) ) );

		if ( is_array( $sync ) && $hash === ( $sync['hash'] ?? '' ) ) {
			return;
		}

		$response = null;
		if ( Connection::is_connected() ) {
			$response = Connection::authorized_request(
				'PUT',
				'/api/v1/recipes/' . $nutrifox_id,
				self::json_args(
					array(
						'name'        => $title,
						'ingredients' => $lines,
					)
				)
			);
		}

		if ( ! is_array( $response ) || 200 !== $response['code'] ) {
			// Keep sync on with the old hash so the next save retries.
			update_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_SYNC, array( 'hash' => is_array( $sync ) ? (string) ( $sync['hash'] ?? '' ) : '' ) );
			return;
		}

		update_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_SYNC, array( 'hash' => $hash ) );
		update_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_RESPONSE, array_diff_key( $response['body'], array_flip( array( 'pending_ingredients', 'edit_url' ) ) ) );
		delete_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_ERROR );
	}

	/**
	 * Whether the recipe's Nutrifox label was built from it and syncs on save.
	 *
	 * @since x.x
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return bool
	 */
	public static function is_synced( $recipe_id ) {
		return is_array( get_post_meta( $recipe_id, Meta_Keys::NUTRIFOX_SYNC, true ) );
	}

	/**
	 * Builds JSON request arguments.
	 *
	 * @since x.x
	 *
	 * @param array $data Request body.
	 *
	 * @return array
	 */
	private static function json_args( $data ) {
		return array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		);
	}

	/**
	 * Picks the fields the recipe modal needs from a Nutrifox recipe.
	 *
	 * @since x.x
	 *
	 * @param array $body Nutrifox recipe payload.
	 *
	 * @return array{id: int, pending_ingredients: string[], edit_url: string}
	 */
	private static function to_editor_response( $body ) {
		return array(
			'id'                  => (int) ( $body['id'] ?? 0 ),
			'pending_ingredients' => array_values( array_map( 'strval', (array) ( $body['pending_ingredients'] ?? array() ) ) ),
			'edit_url'            => esc_url_raw( (string) ( $body['edit_url'] ?? '' ) ),
		);
	}
}

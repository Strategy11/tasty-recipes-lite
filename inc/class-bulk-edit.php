<?php
/**
 * Bulk editing for recipes in the recipe explorer.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes\Objects\Recipe;
use WP_Error;
use WP_REST_Request;

/**
 * Applies the same changes to many recipes at once.
 */
class Bulk_Edit {

	/**
	 * Bulk action key used in the recipe explorer dropdown.
	 *
	 * @var string
	 */
	const ACTION = 'tasty_recipes_bulk_edit';

	/**
	 * Maximum number of recipes accepted in a single request.
	 *
	 * @var int
	 */
	const MAX_IDS_PER_REQUEST = 100;

	/**
	 * Number of recipes the explorer sends in each request.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 25;

	/**
	 * Query arg holding the edited recipe type after a bulk edit.
	 *
	 * @var string
	 */
	const TYPE_ARG = 'tasty_recipes_bulk_edit';

	/**
	 * Query arg holding the number of skipped recipes after a bulk edit.
	 *
	 * @var string
	 */
	const SKIPPED_ARG = 'tasty_recipes_bulk_skipped';

	/**
	 * Modes allowed for each field type.
	 *
	 * @var array
	 */
	private static $modes_by_type = array(
		'taxonomy' => array( 'add', 'remove', 'replace', 'clear' ),
		'list'     => array( 'add', 'remove', 'replace', 'clear' ),
		'options'  => array( 'add', 'remove', 'replace', 'clear' ),
		'text'     => array( 'replace', 'clear' ),
	);

	/**
	 * Explorer filter fields whose cached values depend on recipe taxonomies.
	 *
	 * @var array
	 */
	private static $cached_filter_fields = array( 'cuisine', 'method', 'category', 'diet' );

	/**
	 * Load admin hooks.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_filter( 'bulk_actions-edit-tasty_recipe', array( __CLASS__, 'filter_bulk_actions' ) );
		add_filter( 'tasty_recipes_explorer_page_data', array( __CLASS__, 'filter_explorer_page_data' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'filter_bulk_post_updated_messages' ), 10, 2 );
		add_filter( 'removable_query_args', array( __CLASS__, 'filter_removable_query_args' ) );
	}

	/**
	 * Whether bulk editing is available.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		/**
		 * Filters whether recipes can be bulk edited from the recipe explorer.
		 *
		 * @since x.x
		 *
		 * @param bool $enabled Whether bulk editing is enabled. Default false.
		 */
		return (bool) apply_filters( 'tasty_recipes_bulk_edit_enabled', false );
	}

	/**
	 * Add the bulk edit action to the recipe explorer.
	 *
	 * @since x.x
	 *
	 * @param array $actions The bulk actions.
	 *
	 * @return array
	 */
	public static function filter_bulk_actions( $actions ) {
		if ( ! self::is_enabled() ) {
			return $actions;
		}

		// First in the list, so it sits above the trash and delete actions.
		return array( self::ACTION => __( 'Bulk Edit', 'tasty-recipes-lite' ) ) + $actions;
	}

	/**
	 * Use the recipe type in the notice core shows after a bulk edit.
	 *
	 * @since x.x
	 *
	 * @param array $messages Bulk messages keyed by post type.
	 * @param array $counts   Item counts keyed by message.
	 *
	 * @return array
	 */
	public static function filter_bulk_post_updated_messages( $messages, $counts ) {
		$type    = Utils::get_param( self::TYPE_ARG, 'sanitize_key' );
		$types   = self::get_recipe_types();
		$updated = isset( $counts['updated'] ) ? (int) $counts['updated'] : 0;

		if ( ! self::is_enabled() || ! $updated || ! isset( $types[ $type ] ) ) {
			return $messages;
		}

		$skipped = (int) Utils::get_param( self::SKIPPED_ARG, 'absint', 0 );
		$message = sprintf(
			/* translators: 1: number of updated items, 2: item type, e.g. "recipes". */
			__( '%1$s %2$s updated.', 'tasty-recipes-lite' ),
			number_format_i18n( $updated ),
			1 === $updated ? $types[ $type ]['singular'] : $types[ $type ]['plural']
		);

		if ( $skipped ) {
			$message .= ' ' . sprintf(
				/* translators: 1: number of skipped items, 2: item type, e.g. "recipes". */
				__( '%1$s %2$s could not be updated.', 'tasty-recipes-lite' ),
				number_format_i18n( $skipped ),
				1 === $skipped ? $types[ $type ]['singular'] : $types[ $type ]['plural']
			);
		}

		// Core passes the message through sprintf() with the count.
		$messages['tasty_recipe']['updated'] = str_replace( '%', '%%', $message );

		return $messages;
	}

	/**
	 * Remove the bulk edit notice args from the address bar after the notice shows.
	 *
	 * @since x.x
	 *
	 * @param string[] $args Removable query args.
	 *
	 * @return string[]
	 */
	public static function filter_removable_query_args( $args ) {
		$args[] = self::TYPE_ARG;
		$args[] = self::SKIPPED_ARG;

		return $args;
	}

	/**
	 * Pass the bulk edit settings to the recipe explorer script.
	 *
	 * @since x.x
	 *
	 * @param array $data The explorer page data.
	 *
	 * @return array
	 */
	public static function filter_explorer_page_data( $data ) {
		if ( ! self::is_enabled() ) {
			return $data;
		}

		$types = array();
		foreach ( self::get_recipe_types() as $type => $labels ) {
			$types[ $type ] = array_merge(
				$labels,
				array( 'fields' => self::get_fields_for_script( $type ) )
			);
		}

		$data['bulkEdit'] = array(
			'action'      => self::ACTION,
			'batchSize'   => self::BATCH_SIZE,
			'types'       => $types,
			'recipeTypes' => self::get_current_page_recipe_types(),
			'typeArg'     => self::TYPE_ARG,
			'skippedArg'  => self::SKIPPED_ARG,
		);

		return $data;
	}

	/**
	 * Register the REST route.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function action_rest_api_init() {
		if ( ! self::is_enabled() ) {
			return;
		}

		register_rest_route(
			'tasty-recipes/v1',
			'/recipe-explorer/bulk-edit',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'bulk_edit_endpoint' ),
				'permission_callback' => array( 'Tasty_Recipes\Recipe_Explorer', 'api_check_permission' ),
				'args'                => array(
					'recipe_type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'ids'         => array(
						'required' => true,
						'type'     => 'array',
						'items'    => array( 'type' => 'integer' ),
					),
					'changes'     => array(
						'required' => true,
						'type'     => 'object',
					),
				),
			)
		);
	}

	/**
	 * Get the recipe types that can be bulk edited.
	 *
	 * Recipes of different types are never edited in the same request.
	 *
	 * @since x.x
	 *
	 * @return array Labels keyed by type.
	 */
	public static function get_recipe_types() {
		$types = array(
			'recipe' => array(
				'singular' => __( 'recipe', 'tasty-recipes-lite' ),
				'plural'   => __( 'recipes', 'tasty-recipes-lite' ),
			),
		);

		/**
		 * Filters the recipe types that can be bulk edited.
		 *
		 * @since x.x
		 *
		 * @param array $types Arrays with 'singular' and 'plural' labels keyed by type.
		 */
		return apply_filters( 'tasty_recipes_bulk_edit_recipe_types', $types );
	}

	/**
	 * Get the bulk edit type of a recipe.
	 *
	 * @since x.x
	 *
	 * @param int $recipe_id The recipe ID.
	 *
	 * @return string
	 */
	public static function get_recipe_type( $recipe_id ) {
		/**
		 * Filters the bulk edit type of a recipe.
		 *
		 * @since x.x
		 *
		 * @param string $type      The recipe type. Default 'recipe'.
		 * @param int    $recipe_id The recipe ID.
		 */
		return (string) apply_filters( 'tasty_recipes_bulk_edit_recipe_type', 'recipe', $recipe_id );
	}

	/**
	 * Get the fields that can be bulk edited for a recipe type.
	 *
	 * @since x.x
	 *
	 * @param string $recipe_type The recipe type.
	 *
	 * @return array Field definitions keyed by recipe attribute.
	 */
	public static function get_fields( $recipe_type ) {
		$attributes = Recipe::get_attributes();
		$fields     = array();

		foreach ( array( 'category', 'cuisine', 'method', 'diet' ) as $key ) {
			$fields[ $key ] = array(
				'label'    => $attributes[ $key ]['label'],
				'type'     => 'taxonomy',
				'taxonomy' => $attributes[ $key ]['taxonomy'],
			);
		}

		$fields['keywords'] = array(
			'label' => $attributes['keywords']['label'],
			'type'  => 'list',
		);

		$fields['author_name'] = array(
			'label' => $attributes['author_name']['label'],
			'type'  => 'text',
		);

		/**
		 * Filters the fields that can be bulk edited.
		 *
		 * @since x.x
		 *
		 * @param array  $fields      Field definitions keyed by recipe attribute.
		 * @param string $recipe_type The recipe type being edited.
		 */
		$fields = apply_filters( 'tasty_recipes_bulk_edit_fields', $fields, $recipe_type );

		return array_filter( $fields, array( __CLASS__, 'is_supported_field' ), ARRAY_FILTER_USE_BOTH );
	}

	/**
	 * Handle a bulk edit request.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_Error|\WP_REST_Response
	 */
	public static function bulk_edit_endpoint( $request ) {
		$recipe_type = $request->get_param( 'recipe_type' );
		$types       = self::get_recipe_types();

		if ( ! isset( $types[ $recipe_type ] ) ) {
			return new WP_Error( 'invalid_recipe_type', __( 'Invalid recipe type.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $request->get_param( 'ids' ) ) ) ) );

		if ( empty( $ids ) || count( $ids ) > self::MAX_IDS_PER_REQUEST ) {
			return new WP_Error( 'invalid_ids', __( 'Invalid recipe selection.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		$changes = self::sanitize_changes( (array) $request->get_param( 'changes' ), $recipe_type );

		if ( is_wp_error( $changes ) ) {
			return $changes;
		}

		$fields  = self::get_fields( $recipe_type );
		$updated = array();
		$skipped = array();

		foreach ( $ids as $recipe_id ) {
			$error = self::update_recipe( $recipe_id, $changes, $recipe_type, $fields );

			if ( '' === $error ) {
				$updated[] = $recipe_id;
				continue;
			}

			$skipped[] = array(
				'id'     => $recipe_id,
				'title'  => self::get_recipe_title( $recipe_id ),
				'reason' => $error,
			);
		}

		if ( $updated ) {
			self::clear_filter_cache();

			/**
			 * Fires after a bulk edit request has been processed.
			 *
			 * @since x.x
			 *
			 * @param array  $updated     IDs of the recipes that were updated.
			 * @param array  $changes     The applied changes keyed by field.
			 * @param string $recipe_type The recipe type that was edited.
			 */
			do_action( 'tasty_recipes_after_bulk_edit', $updated, $changes, $recipe_type );
		}

		return rest_ensure_response(
			array(
				'updated' => $updated,
				'skipped' => $skipped,
			)
		);
	}

	/**
	 * Validate the requested changes against the fields of a recipe type.
	 *
	 * @since x.x
	 *
	 * @param array  $changes     Raw changes keyed by field.
	 * @param string $recipe_type The recipe type.
	 *
	 * @return array|WP_Error Changes with 'mode' and 'values' keys, keyed by field.
	 */
	public static function sanitize_changes( $changes, $recipe_type ) {
		$fields    = self::get_fields( $recipe_type );
		$sanitized = array();

		foreach ( $changes as $field => $change ) {
			$field = (string) $field;

			if ( ! isset( $fields[ $field ] ) || ! is_array( $change ) ) {
				return new WP_Error( 'invalid_field', __( 'Invalid field.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
			}

			$type = $fields[ $field ]['type'];
			$mode = isset( $change['mode'] ) ? sanitize_key( $change['mode'] ) : '';

			if ( ! in_array( $mode, self::$modes_by_type[ $type ], true ) ) {
				return new WP_Error( 'invalid_mode', __( 'Invalid change.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
			}

			$values = array();
			if ( 'clear' !== $mode ) {
				$value  = isset( $change['value'] ) && is_scalar( $change['value'] ) ? (string) $change['value'] : '';
				$values = 'text' === $type ? array_filter( array( trim( sanitize_text_field( $value ) ) ) ) : self::split_list( $value );
			}

			if ( 'clear' !== $mode && empty( $values ) ) {
				return new WP_Error( 'missing_value', __( 'Enter a value for each field you change.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
			}

			if ( 'options' === $type && array_diff( $values, self::get_option_values( $fields[ $field ] ) ) ) {
				return new WP_Error( 'invalid_value', __( 'Invalid value.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
			}

			$sanitized[ $field ] = array(
				'mode'   => $mode,
				'values' => array_values( $values ),
			);
		}

		if ( empty( $sanitized ) ) {
			return new WP_Error( 'no_changes', __( 'No changes to apply.', 'tasty-recipes-lite' ), array( 'status' => 400 ) );
		}

		return $sanitized;
	}

	/**
	 * Apply a change to the current value of a field.
	 *
	 * @since x.x
	 *
	 * @param string $current The current value.
	 * @param array  $change  The change with 'mode' and 'values' keys.
	 *
	 * @return string The new value.
	 */
	public static function apply_change( $current, $change ) {
		$values = $change['values'];

		switch ( $change['mode'] ) {
			case 'clear':
				return '';

			case 'add':
				$items = self::split_list( $current );
				$known = array_map( array( __CLASS__, 'normalize_item' ), $items );

				foreach ( $values as $value ) {
					if ( ! in_array( self::normalize_item( $value ), $known, true ) ) {
						$items[] = $value;
						$known[] = self::normalize_item( $value );
					}
				}

				return implode( ', ', $items );

			case 'remove':
				$remove = array_map( array( __CLASS__, 'normalize_item' ), $values );
				$items  = array_filter(
					self::split_list( $current ),
					function ( $item ) use ( $remove ) {
						return ! in_array( self::normalize_item( $item ), $remove, true );
					}
				);

				return implode( ', ', $items );

			default:
				return implode( ', ', $values );
		}
	}

	/**
	 * Apply the changes to a single recipe.
	 *
	 * @since x.x
	 *
	 * @param int    $recipe_id   The recipe ID.
	 * @param array  $changes     Sanitized changes keyed by field.
	 * @param string $recipe_type The recipe type being edited.
	 * @param array  $fields      The fields of the recipe type, keyed by attribute.
	 *
	 * @return string Empty on success, otherwise the reason the recipe was skipped.
	 */
	private static function update_recipe( $recipe_id, $changes, $recipe_type, $fields ) {
		$post = get_post( $recipe_id );

		if ( ! $post || 'tasty_recipe' !== $post->post_type ) {
			return __( 'Recipe not found.', 'tasty-recipes-lite' );
		}

		if ( ! current_user_can( 'edit_post', $recipe_id ) ) {
			return __( 'You are not allowed to edit this recipe.', 'tasty-recipes-lite' );
		}

		$actual_type = self::get_recipe_type( $recipe_id );
		if ( $actual_type !== $recipe_type ) {
			$types = self::get_recipe_types();

			return sprintf(
				/* translators: %s: the recipe type, e.g. "How To". */
				__( 'Skipped because it is a %s.', 'tasty-recipes-lite' ),
				isset( $types[ $actual_type ] ) ? $types[ $actual_type ]['singular'] : $actual_type
			);
		}

		$recipe = Recipe::get_by_id( $recipe_id );
		if ( ! $recipe ) {
			return __( 'Recipe not found.', 'tasty-recipes-lite' );
		}

		$attributes = Recipe::get_attributes();

		foreach ( $changes as $field => $change ) {
			$has_callbacks = self::has_callbacks( $fields[ $field ] );
			$current       = $has_callbacks ? call_user_func( $fields[ $field ]['get_callback'], $recipe ) : $recipe->{"get_{$field}"}();
			$current       = is_array( $current ) ? implode( ', ', $current ) : (string) $current;
			$value         = self::apply_change( $current, $change );

			if ( $value === $current ) {
				continue;
			}

			if ( $has_callbacks ) {
				call_user_func( $fields[ $field ]['update_callback'], $recipe, $value );
				continue;
			}

			$setter            = "set_{$field}";
			$sanitize_callback = ! empty( $attributes[ $field ]['sanitize_callback'] ) ? $attributes[ $field ]['sanitize_callback'] : 'sanitize_text_field';

			/** This filter is documented in inc/class-editor.php */
			$value = apply_filters( 'tasty_recipes_pre_save_editor_data', $sanitize_callback( $value ), $field );

			$recipe->$setter( $value );
		}

		/**
		 * Fires after bulk edit changes have been applied to a recipe.
		 *
		 * @since x.x
		 *
		 * @param Recipe $recipe      The recipe.
		 * @param array  $changes     The applied changes keyed by field.
		 * @param string $recipe_type The recipe type that was edited.
		 */
		do_action( 'tasty_recipes_after_bulk_edit_recipe', $recipe, $changes, $recipe_type );

		return '';
	}

	/**
	 * Whether a field definition can be handled by bulk edit.
	 *
	 * @since x.x
	 *
	 * @param mixed  $field The field definition.
	 * @param string $key   The recipe attribute.
	 *
	 * @return bool
	 */
	private static function is_supported_field( $field, $key ) {
		if ( ! is_array( $field ) || ! isset( $field['type'], self::$modes_by_type[ $field['type'] ] ) ) {
			return false;
		}

		if ( 'options' === $field['type'] && ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) ) {
			return false;
		}

		return self::has_callbacks( $field )
			|| ( method_exists( Recipe::class, 'get_' . $key ) && method_exists( Recipe::class, 'set_' . $key ) );
	}

	/**
	 * Whether a field is read and saved through its own callbacks.
	 *
	 * @since x.x
	 *
	 * @param array $field The field definition.
	 *
	 * @return bool
	 */
	private static function has_callbacks( $field ) {
		return isset( $field['get_callback'], $field['update_callback'] )
			&& is_callable( $field['get_callback'] )
			&& is_callable( $field['update_callback'] );
	}

	/**
	 * Get the allowed values of an 'options' field as strings.
	 *
	 * @since x.x
	 *
	 * @param array $field The field definition.
	 *
	 * @return array
	 */
	private static function get_option_values( $field ) {
		return array_map( 'strval', wp_list_pluck( $field['options'], 'value' ) );
	}

	/**
	 * Get the field definitions in the shape the explorer script expects.
	 *
	 * @since x.x
	 *
	 * @param string $recipe_type The recipe type.
	 *
	 * @return array
	 */
	private static function get_fields_for_script( $recipe_type ) {
		$fields = array();

		foreach ( self::get_fields( $recipe_type ) as $key => $field ) {
			$fields[] = array(
				'key'      => $key,
				'label'    => $field['label'],
				'type'     => $field['type'],
				'taxonomy' => isset( $field['taxonomy'] ) ? $field['taxonomy'] : '',
				'options'  => 'options' === $field['type'] ? self::get_script_options( $field ) : array(),
				'modes'    => self::$modes_by_type[ $field['type'] ],
			);
		}

		return $fields;
	}

	/**
	 * Get the choices of an 'options' field in the shape the explorer script expects.
	 *
	 * @since x.x
	 *
	 * @param array $field The field definition.
	 *
	 * @return array
	 */
	private static function get_script_options( $field ) {
		$options = array();

		foreach ( $field['options'] as $option ) {
			$options[] = array(
				'value' => (string) $option['value'],
				'label' => (string) $option['label'],
			);
		}

		return $options;
	}

	/**
	 * Get the bulk edit type of each recipe listed on the current explorer page.
	 *
	 * @since x.x
	 *
	 * @return array Types keyed by recipe ID.
	 */
	private static function get_current_page_recipe_types() {
		global $wp_query;

		$types = array();

		if ( empty( $wp_query->posts ) ) {
			return $types;
		}

		foreach ( $wp_query->posts as $post ) {
			$types[ $post->ID ] = self::get_recipe_type( $post->ID );
		}

		return $types;
	}

	/**
	 * Get a recipe title for the results list.
	 *
	 * @since x.x
	 *
	 * @param int $recipe_id The recipe ID.
	 *
	 * @return string
	 */
	private static function get_recipe_title( $recipe_id ) {
		$title = get_the_title( $recipe_id );

		return '' !== $title ? html_entity_decode( wp_strip_all_tags( $title ), ENT_QUOTES, get_bloginfo( 'charset' ) ) : __( '(no title)', 'tasty-recipes-lite' );
	}

	/**
	 * Split a comma-separated value into trimmed, non-empty items.
	 *
	 * @since x.x
	 *
	 * @param string $value The value.
	 *
	 * @return array
	 */
	private static function split_list( $value ) {
		$items = array_map( 'trim', explode( ',', (string) $value ) );

		return array_values(
			array_filter(
				$items,
				function ( $item ) {
					return '' !== $item;
				}
			)
		);
	}

	/**
	 * Normalize a list item for case-insensitive comparison.
	 *
	 * @since x.x
	 *
	 * @param string $item The item.
	 *
	 * @return string
	 */
	private static function normalize_item( $item ) {
		$item = Utils::decode_term_name( trim( $item ) );

		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $item ) : strtolower( $item );
	}

	/**
	 * Clear the cached explorer filter values.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function clear_filter_cache() {
		foreach ( self::$cached_filter_fields as $field ) {
			wp_cache_delete( 'tasty_recipes_unique_filter_values_' . $field, 'tasty_recipes' );
		}
	}
}

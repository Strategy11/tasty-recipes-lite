<?php
/**
 * Tests behavior of the Bulk_Edit class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Bulk_Edit;
use Tasty_Recipes\Objects\Recipe;

/**
 * Tests behavior of the Bulk_Edit class.
 */
class Test_Bulk_Edit extends Tasty_Recipes_Testcase {

	/**
	 * Route under test.
	 *
	 * @var string
	 */
	const ROUTE = '/tasty-recipes/v1/recipe-explorer/bulk-edit';

	/**
	 * Enable bulk editing and log in as an editor.
	 */
	public function setUp(): void {
		parent::setUp();

		add_filter( 'tasty_recipes_bulk_edit_enabled', '__return_true' );
		$this->reset_rest_server();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$_SERVER['HTTP_X_WP_NONCE'] = wp_create_nonce( 'wp_rest' );
	}

	/**
	 * Clean up request state.
	 */
	public function tearDown(): void {
		unset( $_SERVER['HTTP_X_WP_NONCE'] );
		$this->reset_rest_server();

		parent::tearDown();
	}

	/**
	 * The route and bulk action don't exist unless bulk editing is enabled.
	 */
	public function test_disabled_by_default() {
		remove_filter( 'tasty_recipes_bulk_edit_enabled', '__return_true' );
		$this->reset_rest_server();

		$recipe   = $this->create_recipe();
		$response = $this->dispatch( array( $recipe->get_id() ), array( 'author_name' => array( 'mode' => 'clear' ) ) );

		$this->assertSame( 404, $response->get_status() );
		$this->assertArrayNotHasKey( Bulk_Edit::ACTION, Bulk_Edit::filter_bulk_actions( array() ) );
		$this->assertArrayNotHasKey( 'bulkEdit', Bulk_Edit::filter_explorer_page_data( array() ) );
	}

	/**
	 * Bulk Edit is listed above the trash action.
	 */
	public function test_bulk_action_is_listed_first() {
		$actions = Bulk_Edit::filter_bulk_actions( array( 'trash' => 'Move to Trash' ) );

		$this->assertSame( array( Bulk_Edit::ACTION, 'trash' ), array_keys( $actions ) );
	}

	/**
	 * The core "updated" notice names the edited type and the skipped count.
	 */
	public function test_updated_notice_message() {
		$_GET[ Bulk_Edit::TYPE_ARG ]    = 'recipe';
		$_GET[ Bulk_Edit::SKIPPED_ARG ] = '1';

		$messages = Bulk_Edit::filter_bulk_post_updated_messages( array(), array( 'updated' => 2 ) );

		$this->assertSame( '2 recipes updated. 1 recipe could not be updated.', $messages['tasty_recipe']['updated'] );

		$_GET[ Bulk_Edit::TYPE_ARG ] = 'unknown';
		$this->assertSame( array(), Bulk_Edit::filter_bulk_post_updated_messages( array(), array( 'updated' => 2 ) ) );

		unset( $_GET[ Bulk_Edit::TYPE_ARG ], $_GET[ Bulk_Edit::SKIPPED_ARG ] );
	}

	/**
	 * Adding terms keeps existing ones and ignores duplicates regardless of case.
	 */
	public function test_add_terms() {
		$recipe = $this->create_recipe( array( 'category' => 'Dinner' ) );

		$response = $this->dispatch(
			array( $recipe->get_id() ),
			array(
				'category' => array(
					'mode'  => 'add',
					'value' => 'dinner, Quick',
				),
			)
		);

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( $recipe->get_id() ), $response->get_data()['updated'] );
		$this->assertSame( 'Dinner, Quick', Recipe::get_by_id( $recipe->get_id() )->get_category() );
	}

	/**
	 * Removing terms leaves the others in place.
	 */
	public function test_remove_terms() {
		$recipe = $this->create_recipe( array( 'cuisine' => 'Italian, French' ) );

		$this->dispatch(
			array( $recipe->get_id() ),
			array(
				'cuisine' => array(
					'mode'  => 'remove',
					'value' => 'italian',
				),
			)
		);

		$this->assertSame( 'French', Recipe::get_by_id( $recipe->get_id() )->get_cuisine() );
	}

	/**
	 * Replacing and clearing work across several recipes.
	 */
	public function test_replace_and_clear() {
		$first  = $this->create_recipe( array( 'method' => 'Baked', 'diet' => 'Vegan' ) );
		$second = $this->create_recipe( array( 'method' => 'Fried' ) );

		$this->dispatch(
			array( $first->get_id(), $second->get_id() ),
			array(
				'method' => array(
					'mode'  => 'replace',
					'value' => 'Grilled',
				),
				'diet'   => array( 'mode' => 'clear' ),
			)
		);

		foreach ( array( $first, $second ) as $recipe ) {
			$recipe = Recipe::get_by_id( $recipe->get_id() );
			$this->assertSame( 'Grilled', $recipe->get_method() );
			$this->assertSame( '', $recipe->get_diet() );
		}
	}

	/**
	 * Keywords and author name are updated without touching other fields.
	 */
	public function test_text_fields_and_untouched_fields() {
		$recipe = $this->create_recipe(
			array(
				'keywords'     => 'pasta, easy',
				'author_name'  => 'Jane',
				'ingredients'  => '<p>1 cup flour</p>',
				'instructions' => '<p>Mix.</p>',
				'category'     => 'Dinner',
			)
		);

		$this->dispatch(
			array( $recipe->get_id() ),
			array(
				'keywords'    => array(
					'mode'  => 'add',
					'value' => 'Easy, weeknight',
				),
				'author_name' => array(
					'mode'  => 'replace',
					'value' => 'QA Author',
				),
			)
		);

		$recipe = Recipe::get_by_id( $recipe->get_id() );
		$this->assertSame( 'pasta, easy, weeknight', $recipe->get_keywords() );
		$this->assertSame( 'QA Author', $recipe->get_author_name() );
		$this->assertSame( '<p>1 cup flour</p>', $recipe->get_ingredients() );
		$this->assertSame( '<p>Mix.</p>', $recipe->get_instructions() );
		$this->assertSame( 'Dinner', $recipe->get_category() );
	}

	/**
	 * Recipes of another type are skipped and left untouched.
	 */
	public function test_other_types_are_skipped() {
		$recipe = $this->create_recipe( array( 'author_name' => 'Jane' ) );
		$how_to = $this->create_recipe( array( 'author_name' => 'Jane' ) );

		$filter = function ( $type, $recipe_id ) use ( $how_to ) {
			return $how_to->get_id() === $recipe_id ? 'how-to' : $type;
		};
		add_filter( 'tasty_recipes_bulk_edit_recipe_type', $filter, 10, 2 );

		$response = $this->dispatch(
			array( $recipe->get_id(), $how_to->get_id() ),
			array( 'author_name' => array( 'mode' => 'clear' ) )
		);

		remove_filter( 'tasty_recipes_bulk_edit_recipe_type', $filter, 10 );

		$data = $response->get_data();
		$this->assertSame( array( $recipe->get_id() ), $data['updated'] );
		$this->assertCount( 1, $data['skipped'] );
		$this->assertSame( $how_to->get_id(), $data['skipped'][0]['id'] );
		$this->assertSame( 'Jane', Recipe::get_by_id( $how_to->get_id() )->get_author_name() );
	}

	/**
	 * Recipes the user can't edit are skipped.
	 */
	public function test_recipes_the_user_cannot_edit_are_skipped() {
		$recipe = $this->create_recipe( array( 'author_name' => 'Jane' ) );
		$contributor = self::factory()->user->create( array( 'role' => 'contributor' ) );

		wp_set_current_user( $contributor );
		$_SERVER['HTTP_X_WP_NONCE'] = wp_create_nonce( 'wp_rest' );

		$response = $this->dispatch( array( $recipe->get_id() ), array( 'author_name' => array( 'mode' => 'clear' ) ) );

		$this->assertSame( array(), $response->get_data()['updated'] );
		$this->assertSame( 'Jane', Recipe::get_by_id( $recipe->get_id() )->get_author_name() );
	}

	/**
	 * Invalid requests are rejected without changing anything.
	 *
	 * @dataProvider data_invalid_changes
	 *
	 * @param array  $changes The requested changes.
	 * @param string $code    The expected error code.
	 */
	public function test_invalid_changes_are_rejected( $changes, $code ) {
		$recipe = $this->create_recipe( array( 'author_name' => 'Jane' ) );

		$response = $this->dispatch( array( $recipe->get_id() ), $changes );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( $code, $response->get_data()['code'] );
		$this->assertSame( 'Jane', Recipe::get_by_id( $recipe->get_id() )->get_author_name() );
	}

	/**
	 * Invalid change sets.
	 *
	 * @return array
	 */
	public function data_invalid_changes() {
		return array(
			'unknown field'     => array( array( 'ingredients' => array( 'mode' => 'clear' ) ), 'invalid_field' ),
			'unsupported mode'  => array(
				array(
					'author_name' => array(
						'mode'  => 'add',
						'value' => 'x',
					),
				),
				'invalid_mode',
			),
			'missing value'     => array( array( 'author_name' => array( 'mode' => 'replace' ) ), 'missing_value' ),
			'nothing to change' => array( array(), 'no_changes' ),
		);
	}

	/**
	 * An unknown recipe type is rejected.
	 */
	public function test_unknown_recipe_type_is_rejected() {
		$recipe   = $this->create_recipe();
		$response = $this->dispatch( array( $recipe->get_id() ), array( 'author_name' => array( 'mode' => 'clear' ) ), 'how-to' );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_recipe_type', $response->get_data()['code'] );
	}

	/**
	 * Bulk edits never fire the editor save action, which expects a full recipe.
	 */
	public function test_editor_save_action_is_not_fired() {
		$recipe      = $this->create_recipe();
		$editor_save = did_action( 'tasty_recipes_after_save_editor_data' );
		$bulk_edit   = did_action( 'tasty_recipes_after_bulk_edit' );

		$this->dispatch( array( $recipe->get_id() ), array( 'author_name' => array( 'mode' => 'clear' ) ) );

		$this->assertSame( $editor_save, did_action( 'tasty_recipes_after_save_editor_data' ) );
		$this->assertSame( $bulk_edit + 1, did_action( 'tasty_recipes_after_bulk_edit' ) );
	}

	/**
	 * Fields added by other plugins are read and saved through their own callbacks.
	 */
	public function test_options_field_with_callbacks() {
		$recipe = $this->create_recipe();
		update_post_meta( $recipe->get_id(), 'test_tools', array( 2 ) );
		add_filter( 'tasty_recipes_bulk_edit_fields', array( $this, 'add_tools_field' ) );

		$response = $this->dispatch(
			array( $recipe->get_id() ),
			array(
				'tools' => array(
					'mode'  => 'add',
					'value' => '1,2',
				),
			)
		);

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( '2, 1', get_post_meta( $recipe->get_id(), 'test_tools', true ) );

		$this->dispatch(
			array( $recipe->get_id() ),
			array(
				'tools' => array(
					'mode'  => 'remove',
					'value' => '2',
				),
			)
		);

		$this->assertSame( '1', get_post_meta( $recipe->get_id(), 'test_tools', true ) );

		$response = $this->dispatch(
			array( $recipe->get_id() ),
			array(
				'tools' => array(
					'mode'  => 'add',
					'value' => '3',
				),
			)
		);

		remove_filter( 'tasty_recipes_bulk_edit_fields', array( $this, 'add_tools_field' ) );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_value', $response->get_data()['code'] );
		$this->assertSame( '1', get_post_meta( $recipe->get_id(), 'test_tools', true ) );
	}

	/**
	 * Fields that can't be read or saved are left out.
	 */
	public function test_unsupported_fields_are_left_out() {
		$filter = function ( $fields ) {
			$fields['no_setter'] = array( 'type' => 'text' );
			$fields['no_options'] = array(
				'type'            => 'options',
				'options'         => array(),
				'get_callback'    => '__return_empty_string',
				'update_callback' => '__return_null',
			);

			return $fields;
		};
		add_filter( 'tasty_recipes_bulk_edit_fields', $filter );

		$fields = Bulk_Edit::get_fields( 'recipe' );

		remove_filter( 'tasty_recipes_bulk_edit_fields', $filter );

		$this->assertArrayNotHasKey( 'no_setter', $fields );
		$this->assertArrayNotHasKey( 'no_options', $fields );
		$this->assertArrayHasKey( 'category', $fields );
	}

	/**
	 * Adds a callback-based options field for tests.
	 *
	 * @param array $fields Bulk edit fields.
	 *
	 * @return array
	 */
	public function add_tools_field( $fields ) {
		$fields['tools'] = array(
			'label'           => 'Tools',
			'type'            => 'options',
			'options'         => array(
				array(
					'value' => 1,
					'label' => 'Pan',
				),
				array(
					'value' => 2,
					'label' => 'Pot',
				),
			),
			'get_callback'    => function ( $recipe ) {
				return get_post_meta( $recipe->get_id(), 'test_tools', true );
			},
			'update_callback' => function ( $recipe, $value ) {
				update_post_meta( $recipe->get_id(), 'test_tools', $value );
			},
		);

		return $fields;
	}

	/**
	 * Create a recipe with the given attributes.
	 *
	 * @param array $attributes Attribute values keyed by name.
	 *
	 * @return Recipe
	 */
	private function create_recipe( $attributes = array() ) {
		$recipe = Recipe::create();

		foreach ( $attributes as $key => $value ) {
			$setter = "set_{$key}";
			$recipe->$setter( $value );
		}

		return $recipe;
	}

	/**
	 * Dispatch a bulk edit request.
	 *
	 * @param array  $ids         Recipe IDs.
	 * @param array  $changes     Changes keyed by field.
	 * @param string $recipe_type The recipe type.
	 *
	 * @return WP_REST_Response
	 */
	private function dispatch( $ids, $changes, $recipe_type = 'recipe' ) {
		$request = new WP_REST_Request( 'POST', self::ROUTE );
		$request->set_param( 'recipe_type', $recipe_type );
		$request->set_param( 'ids', $ids );
		$request->set_param( 'changes', $changes );

		return rest_do_request( $request );
	}

	/**
	 * Force the REST server to register routes again.
	 *
	 * @return void
	 */
	private function reset_rest_server() {
		global $wp_rest_server;

		$wp_rest_server = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}

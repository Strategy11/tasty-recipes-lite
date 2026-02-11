describe( 'WP Tasty recipe', () => {
	before( () => {
		cy.loginCached();
		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		cy.viewport( 1280, 720 );
	} );

	it.skip( 'should create a new recipe', () => {
		cy.log( 'Click Create Recipe button and close modal' );
		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );
		cy.get( '.tasty-recipes-modal-close' ).click();

		cy.log( 'Create Recipe' );
		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( 'h1.tasty-recipes-state-creating' )
			.should( 'be.visible' )
			.and( 'have.text', 'Create Recipe' );

		cy.contains( '.tasty-recipes-modal', 'Create Recipe' ).should(
			'exist'
		);

		cy.log( 'Close modal' );
		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );

		cy.log( 'Reopen modal' );
		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

		cy.log( 'Fill recipe title and verify author' );

		cy.get( '.tasty-recipes-frame-content' )
			.find( 'input#title' )
			.type( 'Test Recipe' );

		cy.get( '#author_name' )
			.should( 'be.visible' )
			.and( 'have.value', 'admin' );

		cy.log( 'Validate recipe image placeholder' );

		cy.get( '.tasty-recipes-image-placeholder' )
			.should( 'be.visible' )
			.within( () => {
				cy.contains( 'Select image for your recipe' ).should( 'exist' );
				cy.contains( 'Maybe a short description here' ).should(
					'exist'
				);
				cy.contains( 'button', 'Select Image' ).should( 'be.visible' );
			} );

		cy.log( 'Upload recipe image' );
		cy.contains( 'button', 'Select Image' ).click();
		cy.get( 'input[type="file"]', { timeout: 10000 } )
			.should( 'exist' )
			.selectFile( 'tests/cypress/fixtures/wp-tasty.png', {
				force: true,
			} );

		cy.wait( 2000 ); // Wait for image to upload

		cy.log( 'Pinterest title' );
		cy.get( 'input[data-tasty-pins-character-limit="100"]' ).type(
			'Easy Homemade Recipe',
			{ force: true }
		);

		cy.log( 'Add Pinterest description' );
		cy.get( 'textarea[data-tasty-pins-character-limit="500"]' ).type(
			'A step-by-step homemade recipe perfect for quick dinners.',
			{ force: true }
		);

		cy.log( 'Pinterest Repin ID' );
		cy.get( '[data-setting="tp_pinterest_repin_id"] input' ).type(
			'1234567890',
			{ force: true }
		);

		cy.log( 'Alt text' );
		cy.get( '#attachment-details-alt-text' ).type(
			'Delicious homemade recipe image',
			{ force: true }
		);

		cy.log( 'Check image title' );
		cy.get( '#attachment-details-title' ).should(
			'have.value',
			'wp-tasty'
		);

		cy.log( 'Image caption' );
		cy.get( '#attachment-details-caption' ).type( 'Recipe preview image', {
			force: true,
		} );

		cy.log( 'Image description' );
		cy.get( '#attachment-details-description' ).type(
			'Image used for WP Tasty recipe card',
			{ force: true }
		);

		cy.log( 'Copy image URL' );
		cy.contains( 'button', 'Copy URL to clipboard' ).click();
		cy.contains( 'Copied!' ).should( 'be.visible' );

		cy.log( 'Validate and click Select Image button' );
		cy.get( 'button.media-button-select' )
			.should( 'be.visible' )
			.and( 'have.text', 'Select Image' )
			.click();

		cy.log( 'Write recipe description' );

		cy.get( 'iframe#tasty-recipes-recipe-description_ifr' )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'This is a test recipe description.', { delay: 0 } );
	} );
} );

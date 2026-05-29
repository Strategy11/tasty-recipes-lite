const waitForRecipeExplorerReady = async page => {
	await page
		.locator( '.row-title.tasty-recipes-edit-recipe, .tasty-recipes-empty-state' )
		.first()
		.waitFor( { state: 'attached', timeout: 10000 } );
};

const openCreateRecipeModal = async page => {
	await waitForRecipeExplorerReady( page );
	await page
		.locator(
			'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary'
		)
		.first()
		.click();
	await expectVisible( page.locator( '.tasty-recipes-modal' ) );
};

const expectVisible = async locator => {
	await locator.waitFor( { state: 'visible', timeout: 10000 } );
};

const fillTinyMceFrame = async ( page, selector, text ) => {
	const body = page.frameLocator( selector ).locator( 'body' );
	await body.waitFor( { state: 'visible', timeout: 10000 } );
	await body.click();
	await body.fill( text );
};

const getEditorCanvas = ( editor, page ) => {
	if ( editor && editor.canvas ) {
		return editor.canvas;
	}

	return page.locator( '.editor-styles-wrapper' );
};

const closeWelcomeGuide = async page => {
	const modal = page.locator( '.components-modal__frame' );

	if ( await modal.isVisible( { timeout: 2000 } ).catch( () => false ) ) {
		await page
			.locator(
				'.components-guide__finish-button, .components-modal__header button[aria-label="Close"]'
			)
			.first()
			.click()
			.catch( () => {} );
	}
};

const openBlockSettingsSidebar = async page => {
	const settingsButton = page
		.getByRole( 'button', { name: /Settings/ } )
		.first();

	if (
		( await settingsButton.isVisible( { timeout: 2000 } ).catch( () => false ) ) &&
		( await settingsButton.getAttribute( 'aria-pressed' ) ) === 'false'
	) {
		await settingsButton.click();
	}

	const blockTab = page.getByRole( 'tab', { name: 'Block' } );
	if ( await blockTab.isVisible( { timeout: 5000 } ).catch( () => false ) ) {
		await blockTab.click();
	}
};

const selectEmbeddedRecipeInBlock = async ( editor, page ) => {
	const canvas = getEditorCanvas( editor, page );

	await canvas
		.locator( '.tasty-recipes-block-placeholder-buttons' )
		.getByRole( 'button', { name: 'Embed existing recipe' } )
		.click();
	await canvas.locator( '.tasty-recipes-select__control' ).click();
	await canvas
		.locator( '.tasty-recipes-select__menu' )
		.waitFor( { state: 'visible', timeout: 5000 } );
	await canvas.locator( '.tasty-recipes-select__option' ).first().click();
};

module.exports = {
	closeWelcomeGuide,
	expectVisible,
	fillTinyMceFrame,
	getEditorCanvas,
	openBlockSettingsSidebar,
	openCreateRecipeModal,
	selectEmbeddedRecipeInBlock,
	waitForRecipeExplorerReady,
};

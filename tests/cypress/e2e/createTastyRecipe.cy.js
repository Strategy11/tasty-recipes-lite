describe('WP Tasty recipe', () => {
	before(() => {
		cy.login();
		cy.visit('/wp-admin/edit.php?post_type=tasty_recipe');
		cy.viewport(1280, 720);
	});

	it('should create a new recipe', () => {

	});
});

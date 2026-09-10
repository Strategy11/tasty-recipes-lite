import { applyFilters } from '@wordpress/hooks';

export const filterEditorActiveCommands = ( commands, range, container ) => {
	/**
	 * Filters recipe editor toolbar command active state.
	 *
	 * @since x.x
	 *
	 * @param {Object}  commands          Detected command state.
	 * @param {Object}  context           Selection context.
	 * @param {Range}   context.range     Active selection range.
	 * @param {Element} context.container Toolbar surface element.
	 */
	return applyFilters( 'tasty_recipes_editor_active_commands', commands, {
		range,
		container,
	} );
};

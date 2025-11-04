/* eslint-disable prettier/prettier */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { columnsIcon } from './icons';
import '../../scss/recipe-explorer/index.scss';
import { registerEvents } from './listeners';
import { EmptyState } from './components/EmptyState';
import { createRootCompat } from '../utils/reactCompat';

domReady( () => {
	fixTitles();
	cleanElements();
	removeColumnsScreenOption();
	rearrangeTopNav();
	addColumnsButton();
	registerEvents();
	showEmptyStateIfNeeded();

	const isPro = applyFilters( 'tasty_recipes_is_pro', false );

	if ( isPro ) {
		return;
	}

	maybeFixBulkActions();
} );

const fixTitles = () => {
	const titles = document.querySelectorAll( '.row-title' );

	titles.forEach( ( title ) => {
		const url = new URL( title.href );
		const postId = url.searchParams.get( 'post' );

		title.href = '#';
		title.classList.add( 'tasty-recipes-edit-recipe' );
		title.dataset.recipeId = postId;
	} );
};

const cleanElements = () => {
	const removable = [
		'.tasty-tab-content .wrap .tablenav.top .tablenav-pages',
		'.page-title-action',
	];

	removable.forEach( ( selector ) => {
		const element = document.querySelector( selector );

		if ( element ) {
			element.remove();
		}
	} );
};

const removeColumnsScreenOption = () => {
	const columnsCheckbox = document.querySelector('#screen-options-wrap input[name="columns-hide"]');
	if (columnsCheckbox) {
		columnsCheckbox.closest('label').style.display = 'none';
	}
	
	const screenOptionsLabels = document.querySelectorAll('#screen-options-wrap .metabox-prefs label');
	screenOptionsLabels.forEach(label => {
		if (label.textContent.includes('Columns')) {
			label.style.display = 'none';
		}
	});
}

const rearrangeTopNav = () => {
	const tableNav = document.querySelector( '.tablenav.top' );
	const actions = document.querySelectorAll( '.tablenav.top .actions' );
	const searchBox = document.querySelector( '.search-box' );

	if ( ! tableNav || ! actions.length || ! searchBox ) {
		return;
	}

	const newSearchBox = document.createElement( 'div' );

	newSearchBox.className = searchBox.className;
	newSearchBox.innerHTML = searchBox.innerHTML;

	const newContainer = document.createElement( 'div' );
	newContainer.classList.add( 'tasty-tablenav-container' );
	tableNav.appendChild( newContainer );

	const actionsContainer = document.createElement( 'div' );
	actionsContainer.classList.add( 'tasty-actions-container' );
	newContainer.appendChild( actionsContainer );

	actions.forEach( ( action ) => {
		actionsContainer.appendChild( action );
	} );

	newContainer.appendChild( newSearchBox );
	searchBox.style.display = 'none';

	formatSearchBox( newSearchBox );
};

const formatSearchBox = ( searchBox ) => {
	const searchInput = searchBox.querySelector( '#post-search-input' );
	const searchSubmit = searchBox.querySelector( '#search-submit' );

	const newSearchButton = document.createElement( 'button' );
	newSearchButton.id = 'search-submit';
	newSearchButton.type = 'submit';
	newSearchButton.classList.add( 'dashicons', 'dashicons-search' );

	searchSubmit.parentNode.replaceChild( newSearchButton, searchSubmit );
	searchInput.placeholder = __( 'Search recipes', 'tasty-recipes-lite' );
};

const addColumnsButton = () => {
	const columnsElements = document.querySelectorAll( 'th.column-columns' );

	columnsElements.forEach( ( element ) => {
		element.innerHTML = '';

		const columnsInnerWrapper = document.createElement( 'div' );

		const columnsText = document.createElement( 'span' );
		columnsText.innerHTML = __( 'Columns', 'tasty-recipes-lite' );
		columnsText.classList.add( 'tasty-columns-text' );
		columnsInnerWrapper.appendChild( columnsText );

		const icon = document.createElement( 'button' );
		icon.innerHTML = columnsIcon;
		icon.classList.add( 'tasty-columns-icon' );
		columnsInnerWrapper.appendChild( icon );

		element.appendChild( columnsInnerWrapper );
	} );
};

const maybeFixBulkActions = () => {
	const bulkActions = document.querySelectorAll( '.bulkactions' );

	bulkActions.forEach( ( element ) => {
		const button = element.querySelector( 'input[type="submit"]' );
		const select = element.querySelector( 'select' );

		if ( button ) {
			button.setAttribute( 'disabled', '' );
		}

		if ( select ) {
			select.setAttribute( 'disabled', '' );

			const wrapper = document.createElement( 'div' );
			wrapper.className = 'tasty-recipes-tooltip-wrapper';
			select.parentNode.insertBefore( wrapper, select );
			wrapper.appendChild( select );

			const tooltip = document.createElement( 'span' );
			tooltip.className = 'tasty-recipes-tooltip';
			tooltip.innerText = __(
				'Not available on Lite plan',
				'tasty-recipes-lite'
			);
			wrapper.appendChild( tooltip );

			wrapper.addEventListener( 'mouseenter', () => {
				tooltip.style.display = 'block';
			} );
			wrapper.addEventListener( 'mouseleave', () => {
				tooltip.style.display = 'none';
			} );
		}
	} );
};

const showEmptyStateIfNeeded = () => {
	const table = document.querySelector( '.wp-list-table' );

	if ( ! table ) {
		return;
	}

	const tbody = table.querySelector( 'tbody' );
	const hasRows = !! tbody.querySelector( 'tr:not(.no-items)' );
	const noItemsRow = tbody.querySelector( '.no-items' );
	const filtersButton = document.getElementById( 'tasty-recipes-filters-button' );
	const filters = [
		'rating',
		'cuisine',
		'method',
		'category',
		'author',
	];
	
	const currentParams = new URLSearchParams( window.location.search );
	const hasActiveFilters = filters.some( filter => currentParams.has( filter ) );

	if ( ! hasRows && noItemsRow ) {
		filtersButton.style.marginTop = '20px';
	}

	if ( ! hasRows && noItemsRow && ! hasActiveFilters ) {
		filtersButton.style.display = 'none';
		noItemsRow.style.display = 'none';
		table.style.display = 'none';

		const emptyDiv = document.createElement( 'div' );
		table.parentNode.insertBefore( emptyDiv, table.nextSibling );

		createRootCompat( emptyDiv ).render( <EmptyState /> );
	}
};

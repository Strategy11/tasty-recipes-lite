import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';
import { columnsIcon } from './icons';
import '../../scss/recipe-explorer.scss';
import { registerEvents } from './listeners';

domReady( () => {
	cleanElements();
	rearrangeTopNav();
	addColumnsButton();
	registerEvents();
} );

const cleanElements = () => {
	const removable = [
		'.tasty-tab-content .wrap .tablenav.top .tablenav-pages',
		'.page-title-action',
	];

	removable.forEach( ( selector ) => {
		if ( document.querySelector( selector ) ) {
			document.querySelector( selector ).remove();
		}
	} );
};

const rearrangeTopNav = () => {
	const tableNav = document.querySelector( '.tablenav.top' );
	const actions = document.querySelectorAll( '.tablenav.top .actions' );
	const searchBox = document.querySelector( '.search-box' );
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

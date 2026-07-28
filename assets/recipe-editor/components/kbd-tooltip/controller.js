import { TOOLTIP_SHOW_DELAY_MS } from './constants';

let tipEl = null;
let showTimer = null;
let activeAnchor = null;

const clearShowTimer = () => {
	if ( showTimer ) {
		window.clearTimeout( showTimer );
		showTimer = null;
	}
};

const ensureTipElement = () => {
	if ( tipEl ) {
		return tipEl;
	}

	tipEl = document.createElement( 'div' );
	tipEl.className = 'tasty-recipes-kbd-tooltip';
	tipEl.setAttribute( 'role', 'tooltip' );
	document.body.append( tipEl );
	return tipEl;
};

const renderContent = ( label, shortcut ) => {
	const tip = ensureTipElement();
	tip.replaceChildren();

	const labelEl = document.createElement( 'span' );
	labelEl.className = 'tasty-recipes-kbd-tooltip__label';
	labelEl.textContent = label;
	tip.append( labelEl );

	if ( shortcut.length ) {
		const keysEl = document.createElement( 'span' );
		keysEl.className = 'tasty-recipes-kbd-tooltip__keys';
		shortcut.forEach( key => {
			const keyEl = document.createElement( 'kbd' );
			keyEl.className = 'tasty-recipes-kbd-tooltip__key';
			keyEl.textContent = key;
			keysEl.append( keyEl );
		} );
		tip.append( keysEl );
	}

	return tip;
};

const positionTip = anchor => {
	if ( ! tipEl || ! anchor ) {
		return;
	}

	const buttonRect = anchor.getBoundingClientRect();
	const tipRect = tipEl.getBoundingClientRect();
	const left = Math.max(
		8,
		Math.min(
			buttonRect.left + ( buttonRect.width / 2 ) - ( tipRect.width / 2 ),
			window.innerWidth - tipRect.width - 8
		)
	);
	const top = Math.max( 8, buttonRect.top - tipRect.height - 6 );

	tipEl.style.top = `${ top }px`;
	tipEl.style.left = `${ left }px`;
};

export const hideKbdTooltip = () => {
	clearShowTimer();
	activeAnchor = null;
	if ( tipEl ) {
		tipEl.remove();
		tipEl = null;
	}
};

export const scheduleShowKbdTooltip = ( {
	label,
	shortcut = [],
	anchor,
} ) => {
	if ( ! label || ! anchor ) {
		return;
	}

	clearShowTimer();
	activeAnchor = anchor;

	showTimer = window.setTimeout( () => {
		showTimer = null;
		if ( ! activeAnchor || ! document.body.contains( activeAnchor ) ) {
			return;
		}

		renderContent( label, shortcut );
		positionTip( activeAnchor );
	}, TOOLTIP_SHOW_DELAY_MS );
};

export const scheduleKbdTooltipFromAnchor = anchor => {
	if ( ! anchor ) {
		return;
	}

	scheduleShowKbdTooltip( {
		label: anchor.dataset.tastyHelperLabel,
		shortcut: ( anchor.dataset.tastyHelperKeys || '' )
			.split( '\u0001' )
			.filter( Boolean ),
		anchor,
	} );
};

export const repositionKbdTooltip = () => {
	if ( tipEl && activeAnchor && document.body.contains( activeAnchor ) ) {
		positionTip( activeAnchor );
	}
};

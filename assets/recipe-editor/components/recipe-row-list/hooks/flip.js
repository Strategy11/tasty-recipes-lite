import { useLayoutEffect } from '@wordpress/element';
import { REORDER_DURATION_MS, REORDER_EASING } from '../lib/constants';
import { ensureRowVisible } from '../lib/rows';

export const captureRowTops = list => {
	if ( ! list ) {
		return null;
	}

	return new Map(
		Array.from( list.querySelectorAll( '.tasty-recipes-item-row' ) ).map(
			el => [ el.dataset.rowId, el.getBoundingClientRect().top ]
		)
	);
};

export const useRowListFlip = (
	rows,
	listRef,
	pendingFlipRef,
	pendingScrollRowIdRef
) => {
	useLayoutEffect( () => {
		const firstTops = pendingFlipRef.current;
		const list = listRef.current;
		const scrollRowId = pendingScrollRowIdRef.current;
		pendingScrollRowIdRef.current = null;

		if ( scrollRowId ) {
			ensureRowVisible( list, scrollRowId );
			window.requestAnimationFrame( () => {
				ensureRowVisible( list, scrollRowId );
			} );
		}

		if ( ! firstTops || ! list ) {
			return;
		}

		pendingFlipRef.current = null;

		const items = Array.from(
			list.querySelectorAll( '.tasty-recipes-item-row' )
		);

		items.forEach( el => {
			const prevTop = firstTops.get( el.dataset.rowId );
			if ( undefined === prevTop ) {
				return;
			}

			const dy = prevTop - el.getBoundingClientRect().top;
			if ( ! dy ) {
				return;
			}

			el.style.transition = 'none';
			el.style.transform = `translateY(${ dy }px)`;
			// Force layout so the invert is applied before animating back.
			el.getBoundingClientRect();
			el.style.transition = `transform ${ REORDER_DURATION_MS }ms ${ REORDER_EASING }`;
			el.style.transform = '';

			const clearStyles = () => {
				el.style.transition = '';
				el.style.transform = '';
			};
			el.addEventListener( 'transitionend', clearStyles, { once: true } );
			window.setTimeout( clearStyles, REORDER_DURATION_MS + 80 );
		} );

		if ( scrollRowId ) {
			window.setTimeout( () => {
				ensureRowVisible( list, scrollRowId );
			}, REORDER_DURATION_MS + 20 );
		}
		// Refs stay stable; flip only after row commits.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ rows ] );
};

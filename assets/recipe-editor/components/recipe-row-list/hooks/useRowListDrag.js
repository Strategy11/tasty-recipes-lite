import { useEffect, useRef, useState } from '@wordpress/element';
import { getRecipeEditorScrollContainer } from '../../dom';
import { moveRow } from '../lib/rows';

export const useRowListDrag = ( {
	listRef,
	rowsRef,
	commitRows,
	pushHistory,
} ) => {
	const [ draggingId, setDraggingId ] = useState( null );
	const dragRef = useRef( null );
	const endDragRef = useRef( null );

	useEffect( () => () => endDragRef.current?.(), [] );

	const handleGripPointerDown = ( event, rowId ) => {
		if ( 0 !== event.button || dragRef.current ) {
			return;
		}

		const list = listRef.current;
		if ( ! list || rowsRef.current.length < 2 ) {
			return;
		}

		event.preventDefault();

		const startIndex = rowsRef.current.findIndex(
			row => row.id === rowId
		);
		if ( startIndex < 0 ) {
			return;
		}

		dragRef.current = {
			rowId,
			index: startIndex,
			startY: event.clientY,
			lastY: event.clientY,
			raf: null,
		};
		setDraggingId( rowId );
		document.body.classList.add( 'tasty-recipes-is-dragging' );

		try {
			event.currentTarget.setPointerCapture( event.pointerId );
		} catch ( error ) {
			// Pointer capture is best-effort.
		}

		const getRowElement = id =>
			list.querySelector( `[data-row-id="${ id }"]` );

		const tickDrag = () => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			const rowEl = getRowElement( drag.rowId );
			if ( ! rowEl ) {
				return;
			}

			rowEl.style.transform = `translate3d(0, ${
				drag.lastY - drag.startY
			}px, 0)`;

			const others = Array.from(
				list.querySelectorAll( '.tasty-recipes-item-row' )
			).filter( el => el.dataset.rowId !== drag.rowId );
			const listTop = list.getBoundingClientRect().top;
			let targetIndex = others.length;

			for ( let i = 0; i < others.length; i++ ) {
				const mid =
					listTop +
					others[ i ].offsetTop +
					( others[ i ].offsetHeight / 2 );
				if ( drag.lastY < mid ) {
					targetIndex = i;
					break;
				}
			}

			if ( targetIndex !== drag.index ) {
				if ( ! drag.didMove ) {
					pushHistory();
					drag.didMove = true;
				}

				const before = rowEl.offsetTop;
				const nextRows = moveRow(
					rowsRef.current,
					drag.index,
					targetIndex
				);
				drag.index = targetIndex;
				commitRows( nextRows );

				window.requestAnimationFrame( () => {
					if ( ! dragRef.current ) {
						return;
					}
					const after = rowEl.offsetTop;
					dragRef.current.startY += after - before;
					rowEl.style.transform = `translate3d(0, ${
						dragRef.current.lastY - dragRef.current.startY
					}px, 0)`;
				} );
			}

			const scrollBody = getRecipeEditorScrollContainer();
			if ( scrollBody ) {
				const bounds = scrollBody.getBoundingClientRect();
				if ( drag.lastY < bounds.top + 48 ) {
					scrollBody.scrollTop -= 10;
				} else if ( drag.lastY > bounds.bottom - 48 ) {
					scrollBody.scrollTop += 10;
				}
			}
		};

		const onPointerMove = moveEvent => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			moveEvent.preventDefault();
			drag.lastY = moveEvent.clientY;
			if ( ! drag.raf ) {
				drag.raf = window.requestAnimationFrame( () => {
					if ( dragRef.current ) {
						dragRef.current.raf = null;
					}
					tickDrag();
				} );
			}
		};

		const endDrag = () => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			const rowEl = getRowElement( drag.rowId );
			if ( rowEl ) {
				rowEl.style.transition =
					'transform 0.22s cubic-bezier(0.34, 1.32, 0.64, 1)';
				rowEl.style.transform = '';
				const clearDragStyles = () => {
					rowEl.style.transition = '';
				};
				rowEl.addEventListener( 'transitionend', clearDragStyles, {
					once: true,
				} );
				window.setTimeout( clearDragStyles, 300 );
			}

			if ( drag.raf ) {
				window.cancelAnimationFrame( drag.raf );
			}

			dragRef.current = null;
			endDragRef.current = null;
			setDraggingId( null );
			document.body.classList.remove( 'tasty-recipes-is-dragging' );
			window.removeEventListener( 'pointermove', onPointerMove );
			window.removeEventListener( 'pointerup', onPointerUp );
			window.removeEventListener( 'pointercancel', onPointerUp );
			window.removeEventListener( 'keydown', onKeyDown );
		};

		endDragRef.current = endDrag;

		const onPointerUp = () => endDrag();
		const onKeyDown = keyEvent => {
			if ( 'Escape' === keyEvent.key ) {
				endDrag();
			}
		};

		window.addEventListener( 'pointermove', onPointerMove, {
			passive: false,
		} );
		window.addEventListener( 'pointerup', onPointerUp );
		window.addEventListener( 'pointercancel', onPointerUp );
		window.addEventListener( 'keydown', onKeyDown );
	};

	return { draggingId, handleGripPointerDown };
};

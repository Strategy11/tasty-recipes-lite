import { useEffect, useRef } from '@wordpress/element';
import { markRowListUndoActive, registerRowListUndo } from '../lib/history';
import { captureRowTops } from './flip';

const cloneRows = source =>
	source.map( row => ( {
		...row,
		image: row.image ? { ...row.image } : null,
		isNew: false,
	} ) );

export const useRowListHistory = ( {
	rowsRef,
	listRef,
	pendingFlipRef,
	commitRows,
} ) => {
	const pastRef = useRef( [] );
	const futureRef = useRef( [] );
	const lastChangeRef = useRef( 'text' );
	const undoApiRef = useRef( null );

	const captureFlip = () => {
		pendingFlipRef.current = captureRowTops( listRef.current );
	};

	const pushHistory = () => {
		pastRef.current = [
			...pastRef.current.slice( -49 ),
			cloneRows( rowsRef.current ),
		];
		futureRef.current = [];
		lastChangeRef.current = 'structure';
		markRowListUndoActive( undoApiRef.current );
	};

	if ( ! undoApiRef.current ) {
		undoApiRef.current = {
			canUndo: () => pastRef.current.length > 0,
			canRedo: () => futureRef.current.length > 0,
			shouldHandleUndo: () => 'structure' === lastChangeRef.current,
			undo: () => {
				if ( ! pastRef.current.length ) {
					return;
				}

				futureRef.current = [
					...futureRef.current,
					cloneRows( rowsRef.current ),
				];
				const prev = pastRef.current[ pastRef.current.length - 1 ];
				pastRef.current = pastRef.current.slice( 0, -1 );
				lastChangeRef.current = pastRef.current.length
					? 'structure'
					: 'text';
				captureFlip();
				commitRows( prev );
			},
			redo: () => {
				if ( ! futureRef.current.length ) {
					return;
				}

				pastRef.current = [
					...pastRef.current,
					cloneRows( rowsRef.current ),
				];
				const next = futureRef.current[ futureRef.current.length - 1 ];
				futureRef.current = futureRef.current.slice( 0, -1 );
				lastChangeRef.current = 'structure';
				captureFlip();
				commitRows( next );
			},
		};
	}

	useEffect( () => registerRowListUndo( undoApiRef.current ), [] );

	return { lastChangeRef, pushHistory };
};

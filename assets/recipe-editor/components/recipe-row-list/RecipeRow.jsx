import { useLayoutEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { DragGripIcon } from '../icons';
import { RowActions } from './RowActions';

export const RecipeRow = ( {
	row,
	placeholder,
	groupPlaceholder,
	canMoveUp,
	canMoveDown,
	canDelete,
	isDragging,
	onValueChange,
	onMoveUp,
	onMoveDown,
	onConvert,
	onDelete,
	onPaste = () => {},
	onGripPointerDown,
	mode = 'ingredients',
} ) => {
	const inputRef = useRef( null );
	const isGroup = 'group' === row.type;

	const handleRowKeyDown = ( event ) => {
		if ( ! event.altKey ) {
			return;
		}

		if ( 'ArrowUp' === event.key ) {
			event.preventDefault();
			if ( canMoveUp ) {
				onMoveUp();
			}
			return;
		}

		if ( 'ArrowDown' === event.key ) {
			event.preventDefault();
			if ( canMoveDown ) {
				onMoveDown();
			}
		}
	};

	// Re-sync after every render. React clears contentEditable DOM text when
	// parent re-renders (no React children), which wiped hydrated values.
	useLayoutEffect( () => {
		const el = inputRef.current;
		if ( ! el || isGroup ) {
			return;
		}

		if ( ( el.textContent || '' ) !== ( row.value || '' ) ) {
			el.textContent = row.value || '';
		}
	} );

	useLayoutEffect( () => {
		const el = inputRef.current;
		if ( ! el || ! row.isNew ) {
			return;
		}

		el.focus();
		if ( isGroup && 'function' === typeof el.setSelectionRange ) {
			const end = el.value.length;
			el.setSelectionRange( end, end );
			return;
		}

		if ( ! isGroup ) {
			const selection = window.getSelection();
			const range = document.createRange();
			range.selectNodeContents( el );
			range.collapse( false );
			selection.removeAllRanges();
			selection.addRange( range );
		}
	}, [ row.isNew, row.id, isGroup ] );

	return (
		<div
			className={ classNames( 'tasty-recipes-item-row', {
				'tasty-recipes-item-row--group': isGroup,
				'tasty-recipes-item-row--new': row.isNew,
				'is-dragging': isDragging,
			} ) }
			data-row-id={ row.id }
		>
			<button
				type="button"
				className="tasty-recipes-drag-grip"
				tabIndex={ -1 }
				aria-label={ __( 'Drag to reorder', 'tasty-recipes-lite' ) }
				onPointerDown={ ( event ) => onGripPointerDown( event, row.id ) }
			>
				{ DragGripIcon }
			</button>

			{ isGroup ? (
				<input
					key={ `${ row.id }-group` }
					ref={ inputRef }
					type="text"
					className="tasty-recipes-row-input"
					placeholder={ groupPlaceholder }
					value={ row.value }
					onChange={ ( event ) =>
						onValueChange( row.id, event.target.value )
					}
					onKeyDown={ handleRowKeyDown }
				/>
			) : (
				<div
					key={ `${ row.id }-item` }
					ref={ inputRef }
					className={ classNames( 'tasty-recipes-row-input', {
						'is-empty': ! row.value,
					} ) }
					contentEditable
					suppressContentEditableWarning
					role="textbox"
					aria-multiline="false"
					data-placeholder={ placeholder }
					onKeyDown={ handleRowKeyDown }
					onPaste={ ( event ) => onPaste( event, row.id ) }
					onInput={ ( event ) => {
						const el = event.currentTarget;
						if (
							'<br>' === el.innerHTML ||
							'<br/>' === el.innerHTML
						) {
							el.innerHTML = '';
						}
						onValueChange( row.id, el.textContent || '' );
					} }
				/>
			) }

			<RowActions
				type={ row.type }
				mode={ mode }
				canMoveUp={ canMoveUp }
				canMoveDown={ canMoveDown }
				canDelete={ canDelete }
				onMoveUp={ onMoveUp }
				onMoveDown={ onMoveDown }
				onConvert={ onConvert }
				onDelete={ onDelete }
			/>
		</div>
	);
};

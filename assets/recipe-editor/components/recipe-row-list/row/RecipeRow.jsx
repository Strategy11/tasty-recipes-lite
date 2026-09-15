import { useLayoutEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { CloseIcon, DragGripIcon } from '../../icons';
import { RowActions } from './RowActions';
import {
	deleteSoftBreakBeforeCaret,
	fromEditorHtml,
	getCaretSplitHtml,
	insertSoftBreak,
	stripHtml,
	toEditorHtml,
} from '../lib/utils';

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
	onSplit = () => {},
	onAddImage = null,
	onRemoveImage = null,
	onDelete,
	onPaste = () => {},
	onGripPointerDown,
	mode = 'ingredients',
} ) => {
	const inputRef = useRef( null );
	const isGroup = 'group' === row.type;
	const hasImage = !! row.image?.url;
	const isEmpty = ! stripHtml( row.value ) && ! hasImage;

	const handleRowKeyDown = event => {
		if (
			'Backspace' === event.key &&
			! event.altKey &&
			! event.metaKey &&
			! event.ctrlKey &&
			deleteSoftBreakBeforeCaret( event.currentTarget )
		) {
			event.preventDefault();
			return;
		}

		if (
			'Enter' === event.key &&
			! event.altKey &&
			! event.metaKey &&
			! event.ctrlKey
		) {
			event.preventDefault();

			if ( event.shiftKey ) {
				insertSoftBreak( event.currentTarget );
				return;
			}

			onSplit( row.id, getCaretSplitHtml( event.currentTarget ) );
			return;
		}

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

	// Re-sync after every render. React can clear contentEditable DOM when
	// parent re-renders (no React children), which wiped hydrated values.
	// Skip while focused so sanitizer rewrites do not jump the caret mid-type;
	// onInput already keeps row.value in sync while editing.
	useLayoutEffect( () => {
		const el = inputRef.current;

		if ( ! el ) {
			return;
		}

		const next = fromEditorHtml( row.value || '' );
		const isFocused = el === el.ownerDocument.activeElement;
		const current = fromEditorHtml( el.innerHTML );

		if ( isFocused ) {
			if ( ! el.innerHTML && next ) {
				el.innerHTML = toEditorHtml( next );
			}
			return;
		}

		if ( current !== next ) {
			el.innerHTML = toEditorHtml( next );
		}
	} );

	useLayoutEffect( () => {
		const el = inputRef.current;

		if ( ! el || ! row.isNew ) {
			return;
		}

		el.focus();

		const selection = el.ownerDocument.defaultView.getSelection();
		const range = el.ownerDocument.createRange();

		range.selectNodeContents( el );
		range.collapse( false );
		selection.removeAllRanges();
		selection.addRange( range );
	}, [ row.isNew, row.id ] );

	const rowInput = (
		<div
			key={ `${ row.id }-input` }
			ref={ inputRef }
			className={ classNames( 'tasty-recipes-row-input', {
				'is-empty': isEmpty,
			} ) }
			data-tasty-recipes-autolink=""
			contentEditable
			suppressContentEditableWarning
			role="textbox"
			tabIndex={ 0 }
			aria-multiline={ ! isGroup }
			data-placeholder={ isGroup ? groupPlaceholder : placeholder }
			onKeyDown={ handleRowKeyDown }
			onPaste={ event => onPaste( event, row.id ) }
			onInput={ event => {
				const el = event.currentTarget;

				if (
					'<br>' === el.innerHTML ||
					'<br/>' === el.innerHTML ||
					'\u200B' === el.textContent
				) {
					el.innerHTML = '';
				}
				onValueChange( row.id, fromEditorHtml( el.innerHTML || '' ) );
			} }
		/>
	);

	return (
		<div
			className={ classNames( 'tasty-recipes-item-row', {
				'tasty-recipes-item-row--group': isGroup,
				'tasty-recipes-item-row--with-image': hasImage,
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
				onPointerDown={ event => onGripPointerDown( event, row.id ) }
			>
				{ DragGripIcon }
			</button>

			{ hasImage ? (
				<div className="tasty-recipes-row-card">
					{ rowInput }
					<div className="tasty-recipes-row-image">
						<img
							src={ row.image.url }
							alt={ row.image.alt || '' }
							width={ row.image.width || undefined }
							height={ row.image.height || undefined }
						/>
						<button
							type="button"
							className="tasty-recipes-row-image__remove"
							aria-label={ __(
								'Remove image',
								'tasty-recipes-lite'
							) }
							onClick={ () => onRemoveImage?.( row.id ) }
						>
							{ CloseIcon }
						</button>
					</div>
					{ !! row.image.caption && (
						<p className="tasty-recipes-row-image__caption">
							{ row.image.caption }
						</p>
					) }
				</div>
			) : (
				rowInput
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
				onAddImage={
					onAddImage ? () => onAddImage( row.id ) : null
				}
				onDelete={ onDelete }
			/>
		</div>
	);
};

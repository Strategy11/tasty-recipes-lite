import { useLayoutEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { DragGripIcon } from '../icons';
import { RowActions } from './RowActions';
import { sanitizeInlineHtml, stripHtml } from './utils';

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
	const isEmpty = ! stripHtml( row.value );

	const handleRowKeyDown = event => {
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
		if ( ! el || isGroup ) {
			return;
		}

		const next = sanitizeInlineHtml( row.value || '' );
		const isFocused = el === el.ownerDocument.activeElement;

		if ( isFocused ) {
			if ( ! el.innerHTML && next ) {
				el.innerHTML = next;
			}
			return;
		}

		if ( sanitizeInlineHtml( el.innerHTML ) !== next ) {
			el.innerHTML = next;
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
			const selection = el.ownerDocument.defaultView.getSelection();
			const range = document.createRange();
			range.selectNodeContents( el );
			range.collapse( false );
			selection.removeAllRanges();
			selection.addRange( range );
		}
	}, [ row.isNew, row.id, isGroup ] );

	const captionInput = isGroup ? (
		<input
			key={ `${ row.id }-group` }
			ref={ inputRef }
			type="text"
			className="tasty-recipes-row-input"
			placeholder={ groupPlaceholder }
			value={ row.value }
			onChange={ event =>
				onValueChange( row.id, event.target.value )
			}
			onKeyDown={ handleRowKeyDown }
		/>
	) : (
		<div
			key={ `${ row.id }-item` }
			ref={ inputRef }
			className={ classNames( 'tasty-recipes-row-input', {
				'is-empty': isEmpty,
			} ) }
			contentEditable
			suppressContentEditableWarning
			role="textbox"
			tabIndex={ 0 }
			aria-multiline="false"
			data-placeholder={ placeholder }
			onKeyDown={ handleRowKeyDown }
			onPaste={ event => onPaste( event, row.id ) }
			onInput={ event => {
				const el = event.currentTarget;
				if (
					'<br>' === el.innerHTML ||
					'<br/>' === el.innerHTML
				) {
					el.innerHTML = '';
				}
				onValueChange(
					row.id,
					sanitizeInlineHtml( el.innerHTML || '' )
				);
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
					{ captionInput }
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
							<span aria-hidden="true">×</span>
						</button>
					</div>
				</div>
			) : (
				captionInput
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

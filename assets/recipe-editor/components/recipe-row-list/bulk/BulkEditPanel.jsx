import { useLayoutEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { BulkToolbar } from './BulkToolbar';
import { openImageDetailsModal } from '../../media-library';
import {
	decorateBulkImages,
	deleteSoftBreakBeforeCaret,
	escapeHtml,
	exitListItem,
	fromImageDetailsData,
	getClosestListItem,
	insertSoftBreak,
	isCaretAtListItemStart,
	isListItemEmpty,
	liftListItem,
	parseImageFromElement,
	parseRowsFromHtml,
	placeCaret,
	replaceBulkImage,
	serializeBulkText,
	splitListItemAtCaret,
	stripBulkImageChrome,
	toImageDetailsMetadata,
} from '../lib/utils';

export const BulkEditPanel = ( {
	name,
	field = 'ingredients',
	showImage = false,
	bulkText,
	setBulkText,
	onApply,
	onCancel,
} ) => {
	const bulkRef = useRef( null );
	const imageLabels = {
		editLabel: __( 'Edit image', 'tasty-recipes-lite' ),
		removeLabel: __( 'Remove image', 'tasty-recipes-lite' ),
	};

	useLayoutEffect( () => {
		const el = bulkRef.current;
		if ( ! el ) {
			return;
		}

		const { activeElement } = el.ownerDocument;
		const isFocused = el === activeElement;
		if ( ! isFocused && stripBulkImageChrome( el.innerHTML ) !== bulkText ) {
			el.innerHTML = bulkText;
		}

		decorateBulkImages( el, imageLabels );

		const isPanelFocused = !! el
			.closest( '.tasty-recipes-row-list-bulk-editor' )
			?.contains( activeElement );
		if ( ! isPanelFocused ) {
			el.focus();
		}
	}, [ bulkText ] );

	const syncBulkText = () => {
		const el = bulkRef.current;
		if ( ! el ) {
			setBulkText( '' );
			return;
		}

		decorateBulkImages( el, imageLabels );
		setBulkText( stripBulkImageChrome( el.innerHTML || '' ) );
	};

	const editBulkImage = wrap => {
		const image = parseImageFromElement( wrap?.querySelector( 'img' ) );
		if ( ! image ) {
			return;
		}

		openImageDetailsModal( {
			metadata: toImageDetailsMetadata( image ),
			onUpdate: imageData => {
				const nextImage = fromImageDetailsData( imageData, image );
				if ( ! nextImage.url || ! wrap.isConnected ) {
					return;
				}

				replaceBulkImage( wrap, nextImage );
				syncBulkText();
			},
		} );
	};

	const handleBulkImageClick = event => {
		const button = event.target.closest(
			'.tasty-recipes-row-image__edit, .tasty-recipes-row-image__remove'
		);
		if ( ! button || ! event.currentTarget.contains( button ) ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		const wrap = button.closest( '.tasty-recipes-bulk-image' );

		if ( button.classList.contains( 'tasty-recipes-row-image__remove' ) ) {
			wrap?.remove();
			syncBulkText();
			return;
		}

		if ( 'click' === event.type ) {
			editBulkImage( wrap );
		}
	};

	const handleBulkInput = event => {
		const el = event.currentTarget;
		if ( '<br>' === el.innerHTML || '<br/>' === el.innerHTML ) {
			el.innerHTML = '';
		}
		syncBulkText();
	};

	const handleListItemKeyDown = event => {
		if ( event.altKey || event.metaKey || event.ctrlKey ) {
			return false;
		}

		const editable = event.currentTarget;
		const selection = editable.ownerDocument.defaultView.getSelection();
		const range = selection?.rangeCount ? selection.getRangeAt( 0 ) : null;
		const item = range && getClosestListItem( editable, range.startContainer );
		if ( ! item ) {
			return false;
		}

		if ( 'Backspace' === event.key ) {
			if ( ! isCaretAtListItemStart( item, range ) ) {
				return false;
			}

			const [ first ] = liftListItem( item );
			if ( first ) {
				placeCaret( first, { before: true } );
			}
			syncBulkText();
			return true;
		}

		if ( 'Enter' !== event.key || event.shiftKey ) {
			return false;
		}

		if ( isListItemEmpty( item ) ) {
			placeCaret( exitListItem( item ), { before: true } );
		} else {
			splitListItemAtCaret( item, range );
		}
		syncBulkText();
		return true;
	};

	const handleBulkKeyDown = event => {
		if ( handleListItemKeyDown( event ) ) {
			event.preventDefault();
			return;
		}

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
			'Enter' !== event.key ||
			event.altKey ||
			event.metaKey ||
			event.ctrlKey
		) {
			return;
		}

		event.preventDefault();

		if ( event.shiftKey ) {
			insertSoftBreak( event.currentTarget, { marked: true } );
			return;
		}

		const selection = window.getSelection();
		const range = selection?.rangeCount ? selection.getRangeAt( 0 ) : null;
		const node = range?.commonAncestorContainer;
		const el =
			node && Node.ELEMENT_NODE === node.nodeType
				? node
				: node?.parentElement;
		const heading = el?.closest( 'h1,h2,h3,h4,h5,h6' );

		if ( heading && event.currentTarget.contains( heading ) ) {
			const doc = heading.ownerDocument;
			const br = doc.createElement( 'br' );
			heading.after( br );
			const after = doc.createRange();
			after.setStartAfter( br );
			after.collapse( true );
			selection.removeAllRanges();
			selection.addRange( after );
			syncBulkText();
			return;
		}

		if (
			'function' === typeof document.queryCommandSupported &&
			document.queryCommandSupported( 'insertText' )
		) {
			document.execCommand( 'insertText', false, '\n' );
		} else {
			document.execCommand( 'insertHTML', false, '<br>' );
		}
		syncBulkText();
	};

	const handleBulkPaste = event => {
		const html = event.clipboardData?.getData( 'text/html' ) || '';
		const text = event.clipboardData?.getData( 'text' ) || '';
		if ( ! text && ! html ) {
			return;
		}

		event.preventDefault();

		if ( html ) {
			document.execCommand(
				'insertHTML',
				false,
				serializeBulkText( parseRowsFromHtml( html ) )
			);
			syncBulkText();
			return;
		}

		if (
			'function' === typeof document.queryCommandSupported &&
			document.queryCommandSupported( 'insertText' )
		) {
			document.execCommand( 'insertText', false, text );
		} else {
			document.execCommand(
				'insertHTML',
				false,
				escapeHtml( text ).replaceAll( /\r?\n/g, '<br>' )
			);
		}
		syncBulkText();
	};

	return (
		<>
			<div className="tasty-recipes-row-list-bulk-editor">
				<BulkToolbar
					editorRef={ bulkRef }
					field={ field }
					showImage={ showImage }
					onChange={ syncBulkText }
				/>
				<div className="tasty-recipes-row-list-bulk-body">
					<div
						id={ `${ name }-bulk` }
						ref={ bulkRef }
						className="tasty-recipes-row-list-bulk"
						data-tasty-recipes-autolink=""
						contentEditable
						suppressContentEditableWarning
						role="textbox"
						tabIndex={ 0 }
						aria-multiline="true"
						onInput={ handleBulkInput }
						onKeyDown={ handleBulkKeyDown }
						onPaste={ handleBulkPaste }
						onMouseDown={ handleBulkImageClick }
						onClick={ handleBulkImageClick }
					/>
				</div>
			</div>
			<div className="tasty-recipes-row-list-bulk-actions">
				<button
					type="button"
					className="tasty-button"
					onClick={ onApply }
				>
					{ __( 'Done', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className="tasty-secondary-button"
					onClick={ onCancel }
				>
					{ __( 'Cancel', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</>
	);
};

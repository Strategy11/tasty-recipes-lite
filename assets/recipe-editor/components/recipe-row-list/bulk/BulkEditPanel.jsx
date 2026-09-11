import { useLayoutEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { BulkToolbar } from './BulkToolbar';
import {
	decorateBulkImages,
	deleteSoftBreakBeforeCaret,
	escapeHtml,
	insertSoftBreak,
	parseRowsFromHtml,
	serializeBulkText,
	stripBulkImageChrome,
} from '../lib/utils';

export const BulkEditPanel = ( {
	name,
	showImage = false,
	bulkText,
	setBulkText,
	onApply,
	onCancel,
} ) => {
	const bulkRef = useRef( null );

	useLayoutEffect( () => {
		const el = bulkRef.current;
		if ( ! el ) {
			return;
		}

		const isFocused = el === el.ownerDocument.activeElement;
		if ( ! isFocused && stripBulkImageChrome( el.innerHTML ) !== bulkText ) {
			el.innerHTML = bulkText;
		}

		decorateBulkImages(
			el,
			__( 'Remove image', 'tasty-recipes-lite' )
		);

		if ( ! isFocused ) {
			el.focus();
		}
	}, [ bulkText ] );

	const syncBulkText = () => {
		const el = bulkRef.current;
		if ( ! el ) {
			setBulkText( '' );
			return;
		}

		decorateBulkImages(
			el,
			__( 'Remove image', 'tasty-recipes-lite' )
		);
		setBulkText( stripBulkImageChrome( el.innerHTML || '' ) );
	};

	const handleBulkImageClick = event => {
		const button = event.target.closest( '.tasty-recipes-row-image__remove' );
		if ( ! button || ! event.currentTarget.contains( button ) ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		button.closest( '.tasty-recipes-bulk-image' )?.remove();
		syncBulkText();
	};

	const handleBulkInput = event => {
		const el = event.currentTarget;
		if ( '<br>' === el.innerHTML || '<br/>' === el.innerHTML ) {
			el.innerHTML = '';
		}
		syncBulkText();
	};

	const handleBulkKeyDown = event => {
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
					showImage={ showImage }
					onChange={ syncBulkText }
				/>
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

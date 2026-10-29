import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { buildParsedRecipe, parseRecipe, UNSORTED } from './lib/parse';
import { detectPasteSource } from './lib/lines';

export const PasteRecipePanel = ( {
	sections,
	detailFields,
	onCancel,
	onFill,
} ) => {
	const [ text, setText ] = useState( '' );
	const [ clipboard, setClipboard ] = useState( null );
	const [ isEmptyResult, setIsEmptyResult ] = useState( false );
	const textareaRef = useRef( null );

	useEffect( () => {
		textareaRef.current?.focus();
	}, [] );

	const handlePaste = event => {
		const html = event.clipboardData?.getData( 'text/html' ) || '';
		const plain = event.clipboardData?.getData( 'text/plain' ) || '';

		// A second paste appends as plain text; structure only comes from
		// pasting into an empty box.
		if ( text.trim() ) {
			setClipboard( null );
			return;
		}

		if ( ! plain ) {
			return;
		}

		// Keep the HTML for structure (headings, lists); show its plain text.
		event.preventDefault();
		setText( plain );
		setIsEmptyResult( false );
		setClipboard( {
			html,
			text: plain,
			source: detectPasteSource( html ),
		} );
	};

	const handleFill = () => {
		// Once the author edits the pasted text, the clipboard HTML is stale.
		const useHtml = clipboard?.html && clipboard.text === text;
		const parsed = parseRecipe(
			{ html: useHtml ? clipboard.html : '', text },
			{ sections, detailFields }
		);
		const values = buildParsedRecipe( parsed, { sections } );

		if ( ! Object.keys( values ).length ) {
			setIsEmptyResult( true );
			return;
		}

		onFill( values, {
			stats: {
				source: clipboard?.source || 'typed',
				fields_found: Object.keys( values ).length,
				unsorted_count: parsed.chunks.filter(
					chunk => UNSORTED === chunk.kind
				).length,
			},
		} );
	};

	return (
		<div className="tasty-recipes-paste-panel">
			<div className="tasty-recipes-paste-panel__header">
				<h3>{ __( 'Paste a recipe', 'tasty-recipes-lite' ) }</h3>
				<p>
					{ __(
						'Paste from Word, Google Docs, a PDF or a web page and we’ll fill in the matching fields. You can undo it afterwards.',
						'tasty-recipes-lite'
					) }
				</p>
			</div>
			<textarea
				ref={ textareaRef }
				className="tasty-recipes-paste-panel__input"
				aria-label={ __( 'Recipe text', 'tasty-recipes-lite' ) }
				placeholder={ __(
					'Paste your recipe here…',
					'tasty-recipes-lite'
				) }
				rows={ 14 }
				value={ text }
				onPaste={ handlePaste }
				onChange={ event => {
					setText( event.target.value );
					setIsEmptyResult( false );
				} }
			/>
			{ isEmptyResult && (
				<p className="tasty-recipes-paste-panel__error" role="alert">
					{ __(
						'We couldn’t find any recipe content in that text. Try pasting the full recipe, including the ingredients and instructions.',
						'tasty-recipes-lite'
					) }
				</p>
			) }
			<div className="tasty-recipes-paste-panel__actions">
				<button
					type="button"
					className="tasty-secondary-button"
					onClick={ onCancel }
				>
					{ __( 'Cancel', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className="tasty-button"
					disabled={ ! text.trim() }
					onClick={ handleFill }
				>
					{ __( 'Fill Recipe', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</div>
	);
};

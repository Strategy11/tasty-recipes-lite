import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';
import classNames from 'classnames';
import { MagicIcon } from '../icons';

const API = '/tasty-recipes/v1/ai';

// Only the fields the AI may change; the rest of the form state stays local.
const getRecipeContext = ( recipe, fields ) =>
	fields.reduce( ( context, key ) => {
		// Total Time keeps its typed value under `total_time_raw`.
		const value =
			'total_time' === key
				? recipe.total_time_raw || recipe.total_time
				: recipe[ key ];

		if ( 'string' === typeof value || 'number' === typeof value ) {
			context[ key ] = String( value );
		}

		return context;
	}, {} );

export const updateRecipe = ( request, recipe, fields, type ) =>
	apiFetch( {
		path: `${ API }/recipe-assist`,
		method: 'POST',
		data: {
			request,
			recipe: getRecipeContext( recipe, fields ),
			fields,
			type,
		},
	} ).then( response => response.fields );

export const AiAssistButton = ( { label, isOpen, className, onToggle } ) => (
	<button
		type="button"
		className={ classNames( 'tasty-recipes-ai-assist-toggle', className, {
			'is-active': isOpen,
		} ) }
		aria-label={ label }
		aria-expanded={ isOpen }
		onClick={ onToggle }
	>
		<Icon icon={ MagicIcon } width={ 16 } height={ 16 } />
	</button>
);

// `onGenerate` sends the request and applies the reply. On an error the panel
// shows it and stays open so the author can try again.
export const AiAssistPanel = ( {
	label,
	placeholder = '',
	maxLength = 500,
	className,
	onGenerate,
	onClose,
} ) => {
	const [ request, setRequest ] = useState( '' );
	const [ isWriting, setIsWriting ] = useState( false );
	const [ error, setError ] = useState( '' );
	const inputRef = useRef( null );

	useEffect( () => {
		inputRef.current?.focus();
	}, [] );

	// The recipe modal closes on Escape from a document listener that
	// React's synthetic events can't stop, so Escape is handled natively.
	useEffect( () => {
		const input = inputRef.current;
		const handleEscape = event => {
			if ( 'Escape' === event.key ) {
				event.stopPropagation();
				onClose();
			}
		};

		input?.addEventListener( 'keydown', handleEscape );

		return () => input?.removeEventListener( 'keydown', handleEscape );
	}, [ onClose ] );

	const canSubmit = request.trim() && ! isWriting;

	const handleSubmit = async () => {
		if ( ! canSubmit ) {
			return;
		}

		setIsWriting( true );
		setError( '' );

		try {
			await onGenerate( request.trim() );
		} catch ( err ) {
			setError(
				err?.message ||
					__(
						'The AI couldn’t write this right now. Please try again.',
						'tasty-recipes-lite'
					)
			);
			setIsWriting( false );
		}
	};

	return (
		<div
			className={ classNames( 'tasty-recipes-ai-assist', className ) }
			aria-busy={ isWriting }
		>
			<textarea
				ref={ inputRef }
				className="tasty-recipes-ai-assist__input"
				aria-label={ label }
				placeholder={ placeholder }
				rows={ 2 }
				maxLength={ maxLength }
				value={ request }
				disabled={ isWriting }
				onChange={ event => {
					setRequest( event.target.value );
					setError( '' );
				} }
				onKeyDown={ event => {
					if ( 'Enter' === event.key && ! event.shiftKey ) {
						event.preventDefault();
						handleSubmit();
					}
				} }
			/>
			{ error && (
				<p className="tasty-recipes-ai-assist__error" role="alert">
					{ error }
				</p>
			) }
			<div className="tasty-recipes-ai-assist__footer">
				<button
					type="button"
					className="tasty-secondary-button"
					disabled={ isWriting }
					onClick={ onClose }
				>
					{ __( 'Cancel', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className={ classNames( 'tasty-button', {
						'is-loading': isWriting,
					} ) }
					disabled={ ! canSubmit }
					onClick={ handleSubmit }
				>
					{ isWriting
						? __( 'Generating…', 'tasty-recipes-lite' )
						: __( 'Generate', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</div>
	);
};

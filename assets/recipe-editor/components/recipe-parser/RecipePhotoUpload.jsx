import { createInterpolateElement, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';
import classNames from 'classnames';
import { UploadIcon } from '../icons';

const ACCEPT = 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif';
const MAX_DIMENSION = 2000;

// Phone photos often exceed PHP's upload limit; shrink them in the browser
// first. Formats the browser can't draw (HEIC outside Safari) go up as-is and
// the server converts them.
const shrinkPhoto = async file => {
	try {
		const bitmap = await window.createImageBitmap( file );
		const scale = Math.min(
			1,
			MAX_DIMENSION / Math.max( bitmap.width, bitmap.height )
		);
		const canvas = document.createElement( 'canvas' );
		canvas.width = Math.round( bitmap.width * scale );
		canvas.height = Math.round( bitmap.height * scale );
		canvas.getContext( '2d' ).drawImage( bitmap, 0, 0, canvas.width, canvas.height );
		bitmap.close?.();

		const blob = await new Promise( resolve =>
			canvas.toBlob( resolve, 'image/jpeg', 0.85 )
		);

		return blob || file;
	} catch ( error ) {
		return file;
	}
};

export const RecipePhotoUpload = ( {
	ai,
	recipeType = 'recipe',
	labels,
	onText,
	onReadingChange = () => {},
} ) => {
	const [ isReading, setIsReadingState ] = useState( false );
	const [ isDragging, setIsDragging ] = useState( false );
	const [ preview, setPreview ] = useState( '' );
	const [ error, setError ] = useState( '' );
	const inputRef = useRef( null );

	const setIsReading = value => {
		setIsReadingState( value );
		onReadingChange( value );
	};
	const isReady = Boolean( ai?.ready );
	const isBlocked = ! isReady || isReading;

	const readPhoto = async file => {
		if ( ! file ) {
			return;
		}

		setError( '' );

		if ( ! ACCEPT.split( ',' ).includes( file.type ) ) {
			setError(
				__(
					'Upload a JPG, PNG, WebP or HEIC photo of the recipe.',
					'tasty-recipes-lite'
				)
			);
			return;
		}

		setIsReading( true );
		setPreview( URL.createObjectURL( file ) );

		try {
			const photo = await shrinkPhoto( file );
			const maxUploadSize = Number( ai?.maxUploadSize ) || 0;

			// PHP drops uploads over the site's limit, so say so up front.
			if ( maxUploadSize && photo.size > maxUploadSize ) {
				setError(
					sprintf(
						/* translators: %s: maximum upload size, such as "8 MB". */
						__(
							'That photo is larger than this site allows. Upload one under %s.',
							'tasty-recipes-lite'
						),
						ai.maxUploadLabel
					)
				);
				return;
			}

			const body = new window.FormData();
			body.append( 'image', photo, photo === file ? file.name : 'recipe.jpg' );
			body.append( 'type', recipeType );

			const response = await apiFetch( {
				path: '/tasty-recipes/v1/ai/recipe-from-image',
				method: 'POST',
				body,
			} );

			onText( response.text );
		} catch ( err ) {
			setError(
				err?.message ||
					__(
						'We couldn’t read that photo. Please try again.',
						'tasty-recipes-lite'
					)
			);
		} finally {
			setIsReading( false );
			setPreview( '' );
			if ( inputRef.current ) {
				inputRef.current.value = '';
			}
		}
	};

	const openPicker = () => {
		if ( ! isBlocked ) {
			inputRef.current?.click();
		}
	};

	const handleDrag = event => {
		event.preventDefault();
		if ( ! isBlocked ) {
			setIsDragging( 'dragleave' !== event.type );
		}
	};

	const handleDrop = event => {
		event.preventDefault();
		setIsDragging( false );
		if ( ! isBlocked ) {
			readPhoto( event.dataTransfer?.files?.[ 0 ] );
		}
	};

	let subtitle = __(
		'Handwritten or printed. JPG, PNG, WebP or HEIC.',
		'tasty-recipes-lite'
	);

	if ( ! isReady ) {
		subtitle = ai?.settingsUrl
			? createInterpolateElement(
				__(
					'Connect an AI provider in <a>Settings</a> to read recipe photos.',
					'tasty-recipes-lite'
				),
				{
					// eslint-disable-next-line jsx-a11y/anchor-has-content
					a: <a href={ ai.settingsUrl } target="_blank" rel="noreferrer noopener" />,
				}
			)
			: __(
				'Ask a site administrator to connect an AI provider to read recipe photos.',
				'tasty-recipes-lite'
			);
	} else if ( isReading ) {
		subtitle = sprintf( labels.readingWith, ai.model || ai.provider );
	}

	return (
		<div className="tasty-recipes-paste-photo">
			{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions */ }
			<div
				className={ classNames( 'tasty-recipes-editor-image-placeholder', {
					'is-dragging': isDragging,
					'is-disabled': ! isReady,
					'is-reading': isReading,
				} ) }
				aria-disabled={ isBlocked }
				aria-busy={ isReading }
				onClick={ openPicker }
				onDragOver={ handleDrag }
				onDragEnter={ handleDrag }
				onDragLeave={ handleDrag }
				onDrop={ handleDrop }
			>
				{ isReading && preview ? (
					<img className="tasty-recipes-paste-photo__preview" src={ preview } alt="" />
				) : (
					<Icon icon={ UploadIcon } width={ 24 } height={ 24 } />
				) }
				<div className="tasty-recipes-editor-image-placeholder-text">
					<p className="tasty-recipes-editor-image-placeholder-title">
						{ isReading ? labels.reading : labels.photoTitle }
					</p>
					<p className="tasty-recipes-editor-image-placeholder-subtitle">
						{ subtitle }
					</p>
				</div>
				{ isReading ? (
					<span className="tasty-recipes-paste-photo__spinner" aria-hidden="true" />
				) : (
					<button
						type="button"
						className="tasty-secondary-button"
						disabled={ isBlocked }
						onClick={ event => {
							event.stopPropagation();
							openPicker();
						} }
					>
						{ __( 'Choose Photo', 'tasty-recipes-lite' ) }
					</button>
				) }
				<input
					ref={ inputRef }
					type="file"
					accept={ ACCEPT }
					hidden
					disabled={ isBlocked }
					aria-label={ __( 'Recipe photo', 'tasty-recipes-lite' ) }
					onChange={ event => readPhoto( event.target.files?.[ 0 ] ) }
				/>
			</div>
			{ error && (
				<p className="tasty-recipes-paste-panel__error" role="alert">
					{ error }
				</p>
			) }
		</div>
	);
};

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import classNames from 'classnames';
import { UploadIcon, TrashIcon } from './icons';
import {
	getMediaImage,
	openImageMediaLibrary,
	uploadImageFile,
} from './media-library';

const getFileName = ( url ) =>
	url
		.split( '/' )
		.pop()
		.replace( /-\d+x\d+(?=\.[a-zA-Z0-9]+$)/, '' );

export const ImageField = ( { recipe, handleRecipeChange, labels = {} } ) => {
	const mergedLabels = {
		mediaTitle: __( 'Media Library', 'tasty-recipes-lite' ),
		mediaButton: __( 'Select Image', 'tasty-recipes-lite' ),
		placeholderTitle: __( 'Recipe Image', 'tasty-recipes-lite' ),
		placeholderSubtitle: __(
			'Drag images or select from your library',
			'tasty-recipes-lite'
		),
		placeholderButton: __( 'Media Library', 'tasty-recipes-lite' ),
		removeButton: __( 'Remove Image', 'tasty-recipes-lite' ),
		...labels,
	};
	const [ recipeThumbnail, setRecipeThumbnail ] = useState(
		recipe?.image_sizes?.thumbnail
	);
	const [ isDragging, setIsDragging ] = useState( false );

	const applyAttachment = attachment => {
		handleRecipeChange( 'image_id', attachment.id );
		const image = getMediaImage( attachment, [ 'thumbnail', 'full' ] );
		setRecipeThumbnail( image.url ? image : null );
	};

	const handleClickSelectImage = e => {
		e.preventDefault();

		openImageMediaLibrary( {
			title: mergedLabels.mediaTitle,
			buttonText: mergedLabels.mediaButton,
			selectedId: recipe.image_id,
			onSelect: applyAttachment,
		} );
	};

	const handleButtonClick = e => {
		e.stopPropagation();
		handleClickSelectImage( e );
	};

	const handleClickRemoveImage = () => {
		handleRecipeChange( 'image_id', 0 );
		setRecipeThumbnail( null );
	};

	const handleDragOver = e => {
		e.preventDefault();
		e.stopPropagation();
		setIsDragging( true );
	};

	const handleDragLeave = e => {
		e.preventDefault();
		if ( e.currentTarget.contains( e.relatedTarget ) ) {
			return;
		}
		setIsDragging( false );
	};

	const handleDrop = async e => {
		e.preventDefault();
		e.stopPropagation();
		setIsDragging( false );

		const file = [ ...( e.dataTransfer?.files || [] ) ].find( dropped =>
			dropped.type.startsWith( 'image/' )
		);
		if ( ! file ) {
			return;
		}

		const attachment = await uploadImageFile( file );
		if ( attachment ) {
			applyAttachment( attachment );
		}
	};

	return (
		<div
			className={ classNames( {
				'tasty-recipes-editor-image-placeholder': ! recipeThumbnail,
				'tasty-recipes-editor-image': recipeThumbnail,
				'is-dragging': isDragging && ! recipeThumbnail,
			} ) }
			onClick={ ! recipeThumbnail ? handleClickSelectImage : undefined }
			onDragOver={ ! recipeThumbnail ? handleDragOver : undefined }
			onDragEnter={ ! recipeThumbnail ? handleDragOver : undefined }
			onDragLeave={ ! recipeThumbnail ? handleDragLeave : undefined }
			onDrop={ ! recipeThumbnail ? handleDrop : undefined }
		>
			<input type="hidden" name="image_id" value={ recipe.image_id } />
			{ recipeThumbnail ? (
				<ImagePreview
					recipeThumbnail={ recipeThumbnail }
					handleClickRemoveImage={ handleClickRemoveImage }
					labels={ mergedLabels }
				/>
			) : (
				<Placeholder
					handleClickSelectImage={ handleButtonClick }
					labels={ mergedLabels }
				/>
			) }
		</div>
	);
};

const ImagePreview = ( {
	recipeThumbnail,
	handleClickRemoveImage,
	labels,
} ) => {
	return (
		<>
			<div className="tasty-image-preview">
				<img
					data-pin-nopin="true"
					src={ recipeThumbnail.url }
					alt=""
					width="40px"
					height="40px"
				/>
				<p> { getFileName( recipeThumbnail.url ) } </p>
			</div>
			<button
				type="button"
				className="tasty-secondary-button"
				onClick={ handleClickRemoveImage }
			>
				<Icon icon={ TrashIcon } width={ 24 } />
				<span>{ labels.removeButton }</span>
			</button>
		</>
	);
};

const Placeholder = ( { handleClickSelectImage, labels } ) => {
	return (
		<>
			<Icon icon={ UploadIcon } width={ 24 } height={ 24 } />
			<div className="tasty-recipes-editor-image-placeholder-text">
				<p className="tasty-recipes-editor-image-placeholder-title">
					{ labels.placeholderTitle }
				</p>
				<p className="tasty-recipes-editor-image-placeholder-subtitle">
					{ labels.placeholderSubtitle }
				</p>
			</div>
			<button
				type="button"
				className="tasty-secondary-button"
				onClick={ handleClickSelectImage }
			>
				<span>{ labels.placeholderButton }</span>
			</button>
		</>
	);
};

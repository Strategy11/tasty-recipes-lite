import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import classNames from 'classnames';
import { UploadIcon, TrashIcon } from './icons';
import { openImageMediaLibrary } from './media-library';

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

	const handleClickSelectImage = ( e ) => {
		e.preventDefault();

		openImageMediaLibrary( {
			title: mergedLabels.mediaTitle,
			buttonText: mergedLabels.mediaButton,
			selectedId: recipe.image_id,
			onSelect: attachment => {
				handleRecipeChange( 'image_id', attachment.id );
				setRecipeThumbnail(
					attachment.sizes.thumbnail ?? attachment.sizes.full
				);
			},
		} );
	};

	const handleClickRemoveImage = () => {
		handleRecipeChange( 'image_id', 0 );
		setRecipeThumbnail( null );
	};

	return (
		<div
			className={ classNames( {
				'tasty-recipes-image-placeholder': ! recipeThumbnail,
				'tasty-recipes-image': recipeThumbnail,
			} ) }
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
					handleClickSelectImage={ handleClickSelectImage }
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
			<div className="tasty-recipes-image-placeholder-text">
				<p className="tasty-recipes-image-placeholder-title">
					{ labels.placeholderTitle }
				</p>
				<p className="tasty-recipes-image-placeholder-subtitle">
					{ labels.placeholderSubtitle }
				</p>
			</div>
			<button
				type="button"
				className="tasty-secondary-button"
				onClick={ handleClickSelectImage }
			>
				{ labels.placeholderButton }
			</button>
		</>
	);
};

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import classNames from 'classnames';
import { UploadIcon, TrashIcon } from './icons';

const getFileName = ( url ) =>
	url
		.split( '/' )
		.pop()
		.replace( /-\d+x\d+(?=\.[a-zA-Z0-9]+$)/, '' );

export const ImageField = ( { recipe, handleRecipeChange } ) => {
	const [ recipeThumbnail, setRecipeThumbnail ] = useState(
		recipe?.image_sizes?.thumbnail
	);

	const handleClickSelectImage = ( e ) => {
		e.preventDefault();

		const frame = wp.media( {
			title: __( 'Media Library', 'tasty-recipes-lite' ),
			multiple: false,
			library: {
				type: 'image',
			},
			button: {
				text: __( 'Select Image', 'tasty-recipes-lite' ),
			},
		} );

		frame.on( 'open', () => {
			const selection = frame.state().get( 'selection' );
			const selected = recipe.image_id;

			if ( selected ) {
				const attachment = wp.media.model.Attachment.get( selected );
				attachment.fetch();
				selection.add( attachment );
			}
		} );

		frame.on( 'select', () => {
			const attachments = frame.state().get( 'selection' ).toJSON();

			if ( attachments.length ) {
				handleRecipeChange( 'image_id', attachments[ 0 ].id );
				setRecipeThumbnail(
					attachments[ 0 ].sizes.thumbnail ??
						attachments[ 0 ].sizes.full
				);
			}
		} );

		frame.open();
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
				/>
			) : (
				<Placeholder
					handleClickSelectImage={ handleClickSelectImage }
				/>
			) }
		</div>
	);
};

const ImagePreview = ( { recipeThumbnail, handleClickRemoveImage } ) => {
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
				className="tasty-secondary-button"
				onClick={ handleClickRemoveImage }
			>
				<Icon icon={ TrashIcon } width={ 24 } />
				<span>{ __( 'Remove Image', 'tasty-recipes-lite' ) }</span>
			</button>
		</>
	);
};

const Placeholder = ( { handleClickSelectImage } ) => {
	return (
		<>
			<Icon icon={ UploadIcon } width={ 20 } height={ 18 } />
			<p className="tasty-recipes-image-placeholder-title">
				{ __( 'Recipe Image', 'tasty-recipes-lite' ) }
			</p>
			<p className="tasty-recipes-image-placeholder-subtitle">
				{ __(
					'Select an image file from your library.',
					'tasty-recipes-lite'
				) }
			</p>
			<button
				className="tasty-secondary-button"
				onClick={ handleClickSelectImage }
			>
				{ __( 'Select Image', 'tasty-recipes-lite' ) }
			</button>
		</>
	);
};

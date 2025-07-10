import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Breadcrumbs } from '../Breadcrumbs';
import { EmbedStepFooter } from '../EmbedStepFooter';

export const CreatePage = ( { recipe, setPage } ) => {
	const [ postName, setPostName ] = useState( recipe.post_title );

	const embedClick = async () => {
		const response = await apiFetch( {
			path: '/tasty-recipes/v1/recipe-explorer/create',
			method: 'POST',
			data: {
				recipe_id: recipe.ID,
				title: postName,
			},
		} );

		if ( response.success ) {
			window.location.reload();
		}
	};

	return (
		<div>
			<Breadcrumbs step="embed" />
			<div className="tasty-recipes-modal-with-form-content">
				<label htmlFor="tasty-post-name">
					{ __( 'Enter title for new post', 'tasty-recipes-lite' ) }
				</label>
				<input
					type="text"
					id="tasty-post-name"
					value={ postName }
					onChange={ ( e ) => setPostName( e.target.value ) }
				/>
			</div>
			<EmbedStepFooter
				setPage={ setPage }
				embedClick={ embedClick }
				embedLabel={ __( 'Create & Embed', 'tasty-recipes-lite' ) }
			/>
		</div>
	);
};

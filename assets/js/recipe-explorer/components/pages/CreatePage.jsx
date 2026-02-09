import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Breadcrumbs } from '../partials/Breadcrumbs';
import { EmbedStepFooter } from '../partials/EmbedStepFooter';

export const CreatePage = ( { recipe, setPage, labels = {} } ) => {
	const [ postName, setPostName ] = useState( recipe.post_title );

	const embedClick = async () => {
		await apiFetch( {
			path: '/tasty-recipes/v1/recipe-explorer/create',
			method: 'POST',
			data: {
				recipe_id: recipe.ID,
				title: postName,
			},
		} );

		window.location.reload();
	};

	return (
		<div>
			<Breadcrumbs
				step="embed"
				firstStepLabel={ labels.createBreadcrumb }
			/>
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

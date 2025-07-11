import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
import { Breadcrumbs } from '../partials/Breadcrumbs';
import { EmbedStepFooter } from '../partials/EmbedStepFooter';
import { SearchComponent } from '../partials/SearchComponent';

export const EmbedPage = ( { setPage, recipe } ) => {
	const [ postId, setPostId ] = useState( null );
	const [ disabledContinue, setDisabledContinue ] = useState( true );

	useEffect( () => {
		setDisabledContinue( ! postId );
	}, [ postId ] );

	const embedClick = async () => {
		await apiFetch( {
			path: '/tasty-recipes/v1/recipe-explorer/embed',
			method: 'POST',
			data: {
				post_id: postId,
				recipe_id: recipe.ID,
			},
		} );

		window.location.reload();
	};

	return (
		<div>
			<Breadcrumbs step="embed" />
			<SearchComponent
				setPostId={ setPostId }
				setDisabledContinue={ setDisabledContinue }
			/>
			<EmbedStepFooter
				setPage={ setPage }
				embedClick={ embedClick }
				disabledContinue={ disabledContinue }
				embedLabel={ __( 'Create & Embed', 'tasty-recipes-lite' ) }
			/>
		</div>
	);
};

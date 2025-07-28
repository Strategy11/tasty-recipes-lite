import { __ } from '@wordpress/i18n';
import { doAction } from '@wordpress/hooks';
import { createInterpolateElement } from '@wordpress/element';
import { openEditorExtension } from '../modals/editorExtensionModal';

export const EmptyState = () => {
	const handleModalClose = ( response ) => {
		const recipe = response.recipe;

		doAction( 'tasty_recipes_orphan_recipe', recipe );
		openEditorExtension( recipe );
	};

	const addRecipeListener = ( e ) => {
		e.preventDefault();

		window.tastyRecipesEditorModal.open(
			{
				author_name:
					window.tastyRecipesEditorModalData.defaultAuthorName,
				title: '',
				diet: '',
			},
			handleModalClose
		);
	};
	return (
		<div className="tasty-recipes-empty-state">
			<img
				src={ window.tastyRecipesExplorerPage.emptyStateImage }
				alt=""
			/>
			<h2>{ __( 'No recipes yet', 'tasty-recipes-lite' ) }</h2>
			<p>
				{ __(
					'No recipes yet? Click "Create Recipe" to start your culinary journey!',
					'tasty-recipes-lite'
				) }
			</p>
			<p>
				{ createInterpolateElement(
					__(
						'Check out <link>our docs</link> to read more.',
						'tasty-recipes-lite'
					),
					{
						link: (
							<a
								href="https://www.wptasty.com/introducing-tasty-recipes-lite-wordpress"
								target="_blank"
								rel="noreferrer"
							>
								our docs
							</a>
						),
					}
				) }
			</p>
			<div className="tasty-recipes-empty-actions">
				<button
					className="tasty-button-primary"
					onClick={ addRecipeListener }
				>
					{ __( 'Create Recipe', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</div>
	);
};

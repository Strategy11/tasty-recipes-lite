import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import { createInterpolateElement } from '@wordpress/element';
import { Breadcrumbs } from '../partials/Breadcrumbs';
import { SelectOption } from '../partials/SelectOption';
import { embedPostIcon, newPostIcon } from '../../icons';

export const SelectPage = ( { setPage } ) => {
	const handleSkipStep = ( e ) => {
		e.preventDefault();

		window.location.reload();
	};

	return (
		<div>
			<Breadcrumbs step="select" />
			<div className="tasty-recipes-modal-select-options">
				<SelectOption
					icon={
						<Icon icon={ embedPostIcon } width="13" height="15" />
					}
					title={ __(
						'Embed in existing post',
						'tasty-recipes-lite'
					) }
					description={ __(
						'Embed your recipe in an existing post.',
						'tasty-recipes-lite'
					) }
					onClick={ () => setPage( 'embed' ) }
				/>
				<SelectOption
					icon={
						<Icon icon={ newPostIcon } width="12" height="15" />
					}
					title={ __( 'Create new post', 'tasty-recipes-lite' ) }
					description={ __(
						'Create a new post and embed your recipe.',
						'tasty-recipes-lite'
					) }
					onClick={ () => setPage( 'create' ) }
				/>
			</div>
			<p className="tasty-recipes-modal-select-skip">
				{ createInterpolateElement(
					__(
						'<link>Skip this step</link> and add the recipe later from inside a post or page.',
						'tasty-recipes-lite'
					),
					{
						link: (
							<button
								className="tasty-recipes-modal-select-skip-link"
								onClick={ handleSkipStep }
							>
								Skip this step
							</button>
						),
					}
				) }
			</p>
		</div>
	);
};

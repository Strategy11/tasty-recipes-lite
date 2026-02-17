import { Icon } from '@wordpress/icons';
import { createInterpolateElement } from '@wordpress/element';
import { Breadcrumbs } from '../partials/Breadcrumbs';
import { SelectOption } from '../partials/SelectOption';
import { embedPostIcon, newPostIcon } from '../../icons';

export const SelectPage = ( { setPage, labels } ) => {
	const handleSkipStep = ( e ) => {
		e.preventDefault();

		window.location.reload();
	};

	return (
		<div>
			<Breadcrumbs
				step="select"
				firstStepLabel={ labels.createBreadcrumb }
			/>
			<div className="tasty-recipes-modal-select-options">
				<SelectOption
					icon={
						<Icon icon={ embedPostIcon } width="13" height="15" />
					}
					title={ labels.embedInExisting }
					description={ labels.embedDescription }
					onClick={ () => setPage( 'embed' ) }
				/>
				<SelectOption
					icon={
						<Icon icon={ newPostIcon } width="12" height="15" />
					}
					title={ labels.createNewPost }
					description={ labels.createDescription }
					onClick={ () => setPage( 'create' ) }
				/>
			</div>
			<p className="tasty-recipes-modal-select-skip">
				{ createInterpolateElement( labels.skipStep, {
					link: (
						<button
							className="tasty-recipes-modal-select-skip-link"
							onClick={ handleSkipStep }
						>
							Skip this step
						</button>
					),
				} ) }
			</p>
		</div>
	);
};

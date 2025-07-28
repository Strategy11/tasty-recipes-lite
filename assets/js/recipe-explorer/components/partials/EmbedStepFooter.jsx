import { __ } from '@wordpress/i18n';

export const EmbedStepFooter = ( {
	setPage,
	embedClick,
	embedLabel,
	disabledContinue = false,
} ) => {
	return (
		<div className="tasty-recipes-modal-embed-step-footer">
			<button
				className="tasty-secondary-button"
				onClick={ () => setPage( 'select' ) }
			>
				{ __( 'Back', 'tasty-recipes-lite' ) }
			</button>
			<button
				className="tasty-button-primary"
				onClick={ embedClick }
				disabled={ disabledContinue }
			>
				{ embedLabel }
			</button>
		</div>
	);
};

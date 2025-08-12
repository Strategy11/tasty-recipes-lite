import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { Modal, useModal } from '@library';

export const UsageModal = ( { closeUsageModal } ) => {
	const { openModal, modalData, closeModal } = useModal();
	const [ isOpen, setIsOpen ] = useState( false );

	const updateUsageConsent = ( consent ) => {
		try {
			apiFetch( {
				path: '/tasty-recipes-lite/v1/usage-consent',
				method: 'POST',
				data: {
					consent,
				},
			} );
		} catch ( error ) {
			// Fail silently.
		}
	};

	useEffect( () => {
		if ( isOpen ) {
			return;
		}

		openModal( {
			title: __( 'Help Us to Improve WP Tasty', 'tasty-recipes-lite' ),
			xClickCallback: () => updateUsageConsent( 'no' ),
			closeCallback: () => closeUsageModal(),
		} );

		setIsOpen( true );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ isOpen, openModal ] );

	return (
		<Modal modalData={ modalData } closeModal={ closeModal }>
			<p>
				{ __(
					'Allow us to collect anonymized usage data to improve features and crush bugs. You can change this setting anytime',
					'tasty-recipes-lite'
				) }
			</p>
			<div className="tasty-recipes-modal-actions">
				<button
					className="tasty-button-primary"
					onClick={ () => {
						updateUsageConsent( 'yes' );
						closeModal();
					} }
				>
					{ __( 'Allow Tracking', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</Modal>
	);
};

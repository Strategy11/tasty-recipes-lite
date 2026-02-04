import { components } from 'react-select';
import { __, sprintf } from '@wordpress/i18n';

export const DeleteConfirmation = ( {
	optionProps,
	label,
	data,
	onCancelDelete,
	onConfirmDelete,
} ) => (
	<components.Option { ...optionProps }>
		<div
			className="tasty-taxonomy-delete-confirmation"
			onClick={ ( event ) => {
				event.preventDefault();
				event.stopPropagation();
			} }
			onMouseDown={ ( event ) => {
				event.stopPropagation();
				event.preventDefault();
			} }
			role="presentation"
		>
			<p className="tasty-taxonomy-delete-message">
				{ sprintf(
					/* translators: 1: Term name, 2: Number of recipes */
					__(
						'[%1$s] is linked to %2$d recipes. Delete?',
						'tasty-recipes-lite'
					),
					label,
					data.count || 0
				) }
			</p>
			<div className="tasty-taxonomy-delete-actions">
				<button
					type="button"
					className="tasty-taxonomy-delete-cancel"
					onClick={ ( event ) => {
						event.stopPropagation();
						onCancelDelete();
					} }
				>
					{ __( 'Cancel', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className="tasty-taxonomy-delete-confirm"
					onClick={ ( event ) => {
						event.stopPropagation();
						onConfirmDelete( data );
					} }
				>
					{ __( 'Delete', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</div>
	</components.Option>
);

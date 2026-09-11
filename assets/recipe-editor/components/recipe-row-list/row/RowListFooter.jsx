import { __ } from '@wordpress/i18n';
import { BulkEditIcon, PlusIcon, SectionTitleIcon } from '../../icons';

export const RowListFooter = ( {
	addItemLabel,
	onAddItem,
	onAddSectionTitle,
	onOpenBulkEdit,
} ) => (
	<div className="tasty-recipes-rich-textarea-actions">
		<div className="tasty-recipes-rich-textarea-actions-start">
			<button
				type="button"
				className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--add"
				onClick={ onAddItem }
			>
				<span
					className="tasty-recipes-rich-textarea-action-icon"
					aria-hidden="true"
				>
					{ PlusIcon }
				</span>
				{ addItemLabel }
			</button>
			<button
				type="button"
				className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--section"
				onClick={ onAddSectionTitle }
			>
				<span
					className="tasty-recipes-rich-textarea-action-icon"
					aria-hidden="true"
				>
					{ SectionTitleIcon }
				</span>
				{ __( 'Add Section Title', 'tasty-recipes-lite' ) }
			</button>
		</div>
		<button
			type="button"
			className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--bulk"
			onClick={ onOpenBulkEdit }
		>
			<span
				className="tasty-recipes-rich-textarea-action-icon"
				aria-hidden="true"
			>
				{ BulkEditIcon }
			</span>
			{ __( 'Bulk Edit', 'tasty-recipes-lite' ) }
		</button>
	</div>
);

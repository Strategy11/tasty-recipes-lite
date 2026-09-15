import { useId } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';

const LINK_PLACEHOLDER_URL = 'https://example.com';

const LinkRelOption = ( { id, label, checked, onChange } ) => (
	<label
		className={ classNames( 'tasty-recipes-link-rel-option', {
			'is-checked': checked,
		} ) }
		htmlFor={ id }
	>
		<input
			id={ id }
			type="checkbox"
			checked={ checked }
			onChange={ event => onChange( event.target.checked ) }
		/>
		<span
			className="tasty-recipes-link-rel-option__mark"
			aria-hidden="true"
		/>
		{ label }
	</label>
);

export const LinkPopoverFields = ( {
	inputRef,
	url,
	onUrlChange,
	onSubmit,
	onCancel,
	onRemove,
	hasAnchor,
	sponsored,
	nofollow,
	onSponsoredChange,
	onNofollowChange,
} ) => {
	const instanceId = useId();

	return (
		<>
			<div className="tasty-recipes-selection-link-popover__row">
				<input
					ref={ inputRef }
					type="text"
					inputMode="url"
					autoComplete="off"
					value={ url }
					placeholder={ LINK_PLACEHOLDER_URL }
					onChange={ event => onUrlChange( event.target.value ) }
					onKeyDown={ event => {
						if ( 'Enter' === event.key ) {
							event.preventDefault();
							onSubmit();
						}
						if ( 'Escape' === event.key ) {
							event.preventDefault();
							onCancel();
						}
					} }
				/>
				<button type="button" onClick={ onSubmit }>
					{ hasAnchor
						? __( 'Update', 'tasty-recipes-lite' )
						: __( 'Add', 'tasty-recipes-lite' ) }
				</button>
				{ hasAnchor && (
					<button
						type="button"
						className="tasty-recipes-selection-link-popover__remove"
						onClick={ onRemove }
					>
						{ __( 'Remove', 'tasty-recipes-lite' ) }
					</button>
				) }
			</div>
			<div
				className="tasty-recipes-selection-link-popover__options"
				role="group"
				aria-label={ __( 'Link attributes', 'tasty-recipes-lite' ) }
			>
				<LinkRelOption
					id={ `tasty-recipes-link-sponsored-${ instanceId }` }
					label={ __( 'Sponsored', 'tasty-recipes-lite' ) }
					checked={ sponsored }
					onChange={ onSponsoredChange }
				/>
				<LinkRelOption
					id={ `tasty-recipes-link-nofollow-${ instanceId }` }
					label={ __( 'Nofollow', 'tasty-recipes-lite' ) }
					checked={ nofollow }
					onChange={ onNofollowChange }
				/>
			</div>
		</>
	);
};

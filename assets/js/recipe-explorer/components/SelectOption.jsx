export const SelectOption = ( { icon, title, description, onClick } ) => {
	return (
		// eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/interactive-supports-focus
		<div
			className="tasty-recipes-modal-select-option"
			role="button"
			onClick={ onClick }
		>
			<div className="tasty-recipes-modal-select-option-icon">
				{ icon }
			</div>
			<h3 className="tasty-recipes-modal-select-option-title">
				{ title }
			</h3>
			<p className="tasty-recipes-modal-select-option-description">
				{ description }
			</p>
		</div>
	);
};

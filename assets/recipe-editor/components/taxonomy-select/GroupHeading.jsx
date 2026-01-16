export const GroupHeading = ( props ) => {
	const { children } = props;

	if ( ! children ) {
		return null;
	}

	return (
		<div className="tasty-taxonomy-group-heading">
			<span className="tasty-taxonomy-group-heading__text">
				{ children }
			</span>
		</div>
	);
};

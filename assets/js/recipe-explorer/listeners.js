export const columnsIconListener = ( e ) => {
	e.preventDefault();

	const screenMeta = document.getElementById( 'screen-meta' );
	const contextualHelpColumns = document.getElementById(
		'screen-options-wrap'
	);

	screenMeta.style.display =
		screenMeta.style.display === 'block' ? 'none' : 'block';
	contextualHelpColumns.classList.toggle( 'hidden' );
};

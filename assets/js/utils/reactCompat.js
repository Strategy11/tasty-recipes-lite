import { createRoot, render, unmountComponentAtNode } from '@wordpress/element';

export const createRootCompat = ( element ) => {
	if ( typeof createRoot === 'function' ) {
		const root = createRoot( element );
		return {
			render: ( component ) => root.render( component ),
			unmount: () => root.unmount(),
		};
	}
	return {
		render: ( component ) => render( component, element ),
		unmount: () => unmountComponentAtNode( element ),
	};
};

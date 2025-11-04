import { UsageModal } from './components/UsageModal';
import domReady from '@wordpress/dom-ready';
import '../../scss/usage-modal/index.scss';
import { createRootCompat } from '../utils/reactCompat';

domReady( () => {
	const modal = document.getElementById( 'tasty-recipes-usage-modal' );
	let root;
	if ( modal ) {
		const closeUsageModal = () => {
			if ( root ) {
				root.unmount();
				root = null;
			}
			modal.remove();
		};

		root = createRootCompat( modal );
		root.render( <UsageModal closeUsageModal={ closeUsageModal } /> );
	}
} );

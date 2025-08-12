import { createRoot } from '@wordpress/element';
import { UsageModal } from './components/UsageModal';
import '../../scss/usage-modal/index.scss';

const modal = document.getElementById( 'tasty-recipes-usage-modal' );

const closeUsageModal = () => {
	modal.remove();
};

if ( modal ) {
	createRoot( modal ).render(
		<UsageModal closeUsageModal={ closeUsageModal } />
	);
}

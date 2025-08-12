import { createRoot } from '@wordpress/element';
import { UsageModal } from './components/UsageModal';
import '../../scss/usage-modal/index.scss';

const modal = document.getElementById( 'tasty-recipes-usage-modal' );

if ( modal ) {
	createRoot( modal ).render(
		<UsageModal closeUsageModal={ () => modal.remove() } />
	);
}

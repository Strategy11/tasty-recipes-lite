import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import { checkMarkIcon, separatorIcon as rawSeparatorIcon } from '../../icons';
import classNames from 'classnames';

const completeIcon = <Icon icon={ checkMarkIcon } width="8" height="7" />;
const separatorIcon = (
	<Icon
		icon={ rawSeparatorIcon }
		className="tasty-recipes-modal-breadcrumbs-separator"
		width="5"
		height="8"
	/>
);

export const Breadcrumbs = ( {
	step,
	firstStepLabel = __( 'Create Recipe', 'tasty-recipes-lite' ),
} ) => {
	return (
		<div className="tasty-recipes-modal-breadcrumbs">
			<p className="tasty-recipes-modal-breadcrumbs-complete">
				<span>{ completeIcon }</span>
				{ firstStepLabel }
			</p>
			<span>{ separatorIcon }</span>
			<p
				className={ classNames( {
					'tasty-recipes-modal-breadcrumbs-complete':
						step === 'embed',
					'tasty-recipes-modal-breadcrumbs-active': step === 'select',
				} ) }
			>
				<span>2</span>
				{ __( 'Post Selection', 'tasty-recipes-lite' ) }
			</p>
			<span>{ separatorIcon }</span>
			<p
				className={ classNames( {
					'tasty-recipes-modal-breadcrumbs-active': step === 'embed',
				} ) }
			>
				<span>3</span>
				{ __( 'Embed', 'tasty-recipes-lite' ) }
			</p>
		</div>
	);
};

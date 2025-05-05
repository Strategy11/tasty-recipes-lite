import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

export const UpgradeBanner = ( { generateUpgradeUrl } ) => {
	const isPro = applyFilters( 'tasty_recipes_is_pro', false );

	if ( isPro ) {
		return null;
	}

	return (
		<div className="tasty-recipes-upgrade-banner">
			<div className="tasty-recipes-upgrade-banner-left">
				<h2>
					{ __(
						'Unlock Your Recipe’s Full Potential',
						'tasty-recipes-lite'
					) }
				</h2>
				<p>
					{ __(
						'The features below are exclusive to WP Tasty Recipes Pro. Upgrade now to power up your recipes and your site.',
						'tasty-recipes-lite'
					) }
				</p>
			</div>
			<div className="tasty-recipes-upgrade-banner-right">
				<a
					className="tasty-recipes-upgrade-banner-right-button tasty-upgrade-button"
					href={ generateUpgradeUrl( {
						medium: 'tasty-recipes',
						content: 'banner-button',
						campaign: 'upgrade-banner',
					} ) }
					target="_blank"
					rel="noreferrer"
				>
					{ __( 'Upgrade to PRO', 'tasty-recipes-lite' ) }
				</a>
			</div>
		</div>
	);
};

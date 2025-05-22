import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { TastyBanner } from '@library';

export const UpgradeBanner = ( { generateUpgradeUrl } ) => {
	const isPro = applyFilters( 'tasty_recipes_is_pro', false );

	if ( isPro ) {
		return null;
	}

	return (
		<TastyBanner
			heading={ __(
				'Unlock Your Recipe’s Full Potential',
				'tasty-recipes-lite'
			) }
			description={ __(
				'The features below are exclusive to WP Tasty Recipes Pro. Upgrade now to power up your recipes and your site.',
				'tasty-recipes-lite'
			) }
			buttonText={ __( 'Upgrade to PRO', 'tasty-recipes-lite' ) }
			buttonUrl={ generateUpgradeUrl( {
				medium: 'tasty-recipes',
				content: 'banner-button',
				campaign: 'upgrade-banner',
			} ) }
		/>
	);
};

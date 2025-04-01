import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useState } from '@wordpress/element';
import { Icon, check } from '@wordpress/icons';
import classNames from 'classnames';

const converters = window.tastyRecipesSettings.converters;
const converterNames = Object.keys( converters );
const defaultConverterState = {
	converter: '',
	status: 'idle',
	message: '',
};

export const Converters = () => {
	const [ doneConversions, setDoneConversions ] = useState( [] );
	const [ converterStatus, setConverterStatus ] = useState(
		defaultConverterState
	);

	const isConverterActive = ( name ) => {
		return converterStatus.converter === name;
	};

	const addDoneConversion = ( conversion ) => {
		setDoneConversions( ( prev ) => {
			return [ ...prev, conversion ];
		} );
	};

	const triggerConverter = () => {
		const type = converterStatus.converter;
		const converter = converters[ type ];

		if ( converter.count === 0 ) {
			return;
		}

		wp.ajax
			.send( 'tasty_recipes_convert', {
				data: {
					nonce: window.tastyRecipesSettings.nonce,
					type,
				},
				type: 'GET',
			} )
			.done( ( response ) => {
				if ( response.count ) {
					triggerConverter();
				} else {
					addDoneConversion( converterStatus.converter );

					setConverterStatus( ( prev ) => {
						return {
							...prev,
							status: 'finished',
						};
					} );
				}
			} )
			.fail( ( response ) => {
				setConverterStatus( ( prev ) => {
					return {
						...prev,
						status: 'idle',
						message: response.message,
					};
				} );
			} );
	};

	const isConverterDone =
		converterStatus.converter &&
		doneConversions.includes( converterStatus.converter );

	return (
		<>
			<div className="tasty-recipes-converter-header">
				<h4>
					{ __( 'Import Existing Recipes', 'tasty-recipes-lite' ) }
				</h4>
				<p>
					{ createInterpolateElement(
						/* translators: %s: Link to WordPress backup documentation */
						__(
							'Just in case, we recommend backing up your database before converting your recipes in bulk to Tasty Recipes. <a>Learn more about backing up your database.</a>',
							'tasty-recipes-lite'
						),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									href="https://www.wptasty.com/convert-all"
									target="_blank"
									rel="noopener noreferrer"
								/>
							),
						}
					) }
				</p>
			</div>
			<div className="tasty-recipes-converters">
				<p>
					{ __(
						'Select an option below to start converting recipes.',
						'tasty-recipes-lite'
					) }
				</p>
				<div className="tasty-recipes-converters-options">
					{ converterNames.map( ( converterName ) => {
						const converter = converters[ converterName ];

						return (
							<button
								key={ `converter-${ converterName }` }
								type="button"
								disabled={
									converter.count === 0 || isConverterDone
								}
								className={ classNames(
									'tasty-recipes-converter-option',
									'button',
									{
										'tasty-recipes-converter-active':
											isConverterActive( converterName ),
									}
								) }
								onClick={ () => {
									setConverterStatus( ( prev ) => {
										return {
											...prev,
											converter: converterName,
										};
									} );
								} }
							>
								{ converter.label }
								{ isConverterActive( converterName ) && (
									<Icon
										icon={ check }
										size={ 22 }
										style={
											isConverterDone
												? { fill: 'green' }
												: { fill: '#fff' }
										}
									/>
								) }
							</button>
						);
					} ) }
				</div>
			</div>
			{ converterStatus.converter && (
				<div className="tasty-recipes-converter-action">
					{ converterStatus.status === 'idle' && (
						<button
							className="button tasty-recipes-start-import"
							type="button"
							onClick={ () => {
								setConverterStatus( ( prev ) => {
									return {
										...prev,
										status: 'importing',
									};
								} );

								triggerConverter();
							} }
						>
							{ __( 'Start import', 'tasty-recipes-lite' ) }
						</button>
					) }
					{ converterStatus.status === 'importing' && (
						<p>
							{ __( 'Importing recipes…', 'tasty-recipes-lite' ) }
						</p>
					) }
					{ converterStatus.status === 'finished' && (
						<p>
							{ __( 'Import complete!', 'tasty-recipes-lite' ) }
						</p>
					) }
				</div>
			) }
		</>
	);
};

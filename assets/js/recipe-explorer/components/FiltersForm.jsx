import { __ } from '@wordpress/i18n';
import { useState, useRef, useEffect } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

const { filters } = window.tastyRecipesExplorerPage;

const defaultEmptyFormData = {
	rating: '',
	cuisine: '',
	method: '',
	category: '',
	author: '',
	diet: '',
};

const emptyFormData = applyFilters(
	'tasty_recipes_filters_empty_form_data',
	defaultEmptyFormData
);

export const FiltersForm = ( { closeFiltersForm } ) => {
	const formRef = useRef( null );
	const [ formData, setFormData ] = useState( () => {
		const currentParams = new URLSearchParams( window.location.search );
		const urlData = Object.fromEntries( currentParams.entries() );

		// Merge with empty form data and handle array fields.
		const mergedData = { ...emptyFormData };
		Object.entries( urlData ).forEach( ( [ key, value ] ) => {
			// Check if this field should be an array (based on emptyFormData).
			if ( Array.isArray( emptyFormData[ key ] ) ) {
				mergedData[ key ] = value ? value.split( ',' ) : [];
			} else {
				mergedData[ key ] = value;
			}
		} );

		return mergedData;
	} );

	useEffect( () => {
		const handleClickOutside = ( event ) => {
			if (
				formRef.current &&
				! formRef.current.contains( event.target )
			) {
				closeFiltersForm();
			}
		};

		document.addEventListener( 'mousedown', handleClickOutside );

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ closeFiltersForm ] );

	const handleSubmit = ( e ) => {
		e.preventDefault();

		const currentParams = new URLSearchParams( window.location.search );

		Object.entries( formData ).forEach( ( [ key, value ] ) => {
			if ( Array.isArray( value ) ) {
				if ( value.length > 0 ) {
					currentParams.set( key, value.join( ',' ) );
				} else {
					currentParams.delete( key );
				}
			} else if ( value ) {
				currentParams.set( key, value );
			} else {
				currentParams.delete( key );
			}
		} );

		setUrlParamsAndRefresh( currentParams );
	};

	const handleReset = ( e ) => {
		e.preventDefault();

		setFormData( emptyFormData );

		const currentParams = new URLSearchParams( window.location.search );

		Object.keys( emptyFormData ).forEach( ( key ) => {
			currentParams.delete( key );
		} );

		setUrlParamsAndRefresh( currentParams );
	};

	const setUrlParamsAndRefresh = ( currentParams ) => {
		window.history.pushState(
			{},
			'',
			`${ window.location.pathname }?${ currentParams.toString() }`
		);

		window.location.reload();
	};

	const handleChange = ( e ) => {
		setFormData( {
			...formData,
			[ e.target.name ]: e.target.value,
		} );
	};

	return (
		<form
			className="tasty-recipes-filters-form"
			onSubmit={ handleSubmit }
			ref={ formRef }
		>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-rating">
					{ __( 'Rating', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-rating"
					name="rating"
					onChange={ handleChange }
					value={ formData.rating }
				>
					<option value="" selected={ ! formData.rating }>
						{ __( 'Select rating', 'tasty-recipes-lite' ) }
					</option>
					<option value="5" selected={ formData.rating === '5' }>
						{ __( '5 stars', 'tasty-recipes-lite' ) }
					</option>
					<option value="4" selected={ formData.rating === '4' }>
						{ __( '4 stars', 'tasty-recipes-lite' ) }
					</option>
					<option value="3" selected={ formData.rating === '3' }>
						{ __( '3 stars', 'tasty-recipes-lite' ) }
					</option>
					<option value="2" selected={ formData.rating === '2' }>
						{ __( '2 stars', 'tasty-recipes-lite' ) }
					</option>
					<option value="1" selected={ formData.rating === '1' }>
						{ __( '1 star', 'tasty-recipes-lite' ) }
					</option>
				</select>
			</div>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-cuisine">
					{ __( 'Cuisine', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-cuisine"
					name="cuisine"
					onChange={ handleChange }
					value={ formData.cuisine }
				>
					<option value="" selected={ ! formData.cuisine }>
						{ __( 'Select cuisine', 'tasty-recipes-lite' ) }
					</option>
					{ filters.cuisine.map( ( cuisine ) => (
						<option
							value={ cuisine }
							key={ cuisine }
							selected={ formData.cuisine === cuisine }
						>
							{ cuisine }
						</option>
					) ) }
				</select>
			</div>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-cooking-method">
					{ __( 'Cooking method', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-cooking-method"
					name="method"
					onChange={ handleChange }
					value={ formData.method }
				>
					<option value="" selected={ ! formData.cookingMethod }>
						{ __( 'Select cooking method', 'tasty-recipes-lite' ) }
					</option>
					{ filters.method.map( ( method ) => (
						<option
							value={ method }
							key={ method }
							selected={ formData.method === method }
						>
							{ method }
						</option>
					) ) }
				</select>
			</div>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-category">
					{ __( 'Category', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-category"
					name="category"
					onChange={ handleChange }
					value={ formData.category }
				>
					<option value="" selected={ ! formData.category }>
						{ __( 'Select category', 'tasty-recipes-lite' ) }
					</option>
					{ filters.category.map( ( category ) => (
						<option
							value={ category }
							key={ category }
							selected={ formData.category === category }
						>
							{ category }
						</option>
					) ) }
				</select>
			</div>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-author">
					{ __( 'Author', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-author"
					name="author"
					onChange={ handleChange }
					value={ formData.author }
				>
					<option value="" selected={ ! formData.author }>
						{ __( 'Select author', 'tasty-recipes-lite' ) }
					</option>
					{ filters.author.map( ( author ) => (
						<option
							value={ author.id }
							key={ author.id }
							selected={ formData.author === author.id }
						>
							{ author.name }
						</option>
					) ) }
				</select>
			</div>
			<div className="tasty-recipes-filter-group">
				<label htmlFor="filter-diet">
					{ __( 'Diet', 'tasty-recipes-lite' ) }
				</label>
				<select
					id="filter-diet"
					name="diet"
					onChange={ handleChange }
					value={ formData.diet }
				>
					<option value="" selected={ ! formData.diet }>
						{ __( 'Select diet', 'tasty-recipes-lite' ) }
					</option>
					{ filters.diet.map( ( diet ) => (
						<option
							value={ diet }
							key={ diet }
							selected={ formData.diet === diet }
						>
							{ diet }
						</option>
					) ) }
				</select>
			</div>
			{ applyFilters(
				'tasty_recipes_filters_additional_groups',
				null,
				formData,
				setFormData
			) }
			<div className="tasty-recipes-filter-actions">
				<button
					type="reset"
					className="tasty-secondary-button"
					onClick={ handleReset }
				>
					{ __( 'Reset', 'tasty-recipes-lite' ) }
				</button>
				<button type="submit" className="tasty-button-primary">
					{ __( 'Apply Filters', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</form>
	);
};

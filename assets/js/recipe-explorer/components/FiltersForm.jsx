import { __ } from '@wordpress/i18n';
import { useState, useRef } from '@wordpress/element';

const { filters } = window.tastyRecipesExplorerPage;

const emptyFormData = {
	rating: '',
	cuisine: '',
	cookingMethod: '',
	category: '',
	author: '',
};

export const FiltersForm = () => {
	const formRef = useRef( null );
	const [ formData, setFormData ] = useState( () => {
		const currentParams = new URLSearchParams( window.location.search );

		return Object.fromEntries( currentParams.entries() );
	} );

	const handleSubmit = ( e ) => {
		e.preventDefault();

		const currentParams = new URLSearchParams( window.location.search );

		Object.entries( formData ).forEach( ( [ key, value ] ) =>
			value
				? currentParams.set( key, value )
				: currentParams.delete( key )
		);

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
					name="cookingMethod"
					onChange={ handleChange }
					value={ formData.cookingMethod }
				>
					<option value="" selected={ ! formData.cookingMethod }>
						{ __( 'Select cooking method', 'tasty-recipes-lite' ) }
					</option>
					{ filters.cooking_method.map( ( cookingMethod ) => (
						<option
							value={ cookingMethod }
							key={ cookingMethod }
							selected={
								formData.cookingMethod === cookingMethod
							}
						>
							{ cookingMethod }
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
							value={ author }
							key={ author }
							selected={ formData.author === author }
						>
							{ author }
						</option>
					) ) }
				</select>
			</div>
			<div className="tasty-recipes-filter-actions">
				<button
					type="reset"
					className="tasty-secondary-button"
					onClick={ () => setFormData( emptyFormData ) }
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

import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const SearchComponent = ( { setPostId } ) => {
	const [ innerValue, setInnerValue ] = useState( '' );
	const [ selectedValue, setSelectedValue ] = useState( null );
	const [ posts, setPosts ] = useState( [] );
	const debounceTimeoutRef = useRef( null );

	useEffect( () => {
		if ( debounceTimeoutRef.current ) {
			clearTimeout( debounceTimeoutRef.current );
		}

		if ( innerValue.length < 3 ) {
			setPosts( [] );
			return;
		}

		debounceTimeoutRef.current = setTimeout( async () => {
			try {
				const response = await apiFetch( {
					path: `/wp/v2/posts?search=${ encodeURIComponent(
						innerValue
					) }`,
					method: 'GET',
				} );

				setPosts( response );
			} catch ( error ) {
				setPosts( [] );
			}
		}, 300 );

		return () => {
			if ( debounceTimeoutRef.current ) {
				clearTimeout( debounceTimeoutRef.current );
			}
		};
	}, [ innerValue ] );

	const handlePostClick = ( post ) => {
		setPostId( post.id );
		setSelectedValue( post.title.rendered );
		setPosts( [] );
	};

	return (
		<div>
			<div className="tasty-recipes-modal-with-form-content">
				<label htmlFor="search">
					{ __(
						'Select post to embed recipe',
						'tasty-recipes-lite'
					) }
				</label>
				<input
					type="text"
					onChange={ ( e ) => {
						if ( selectedValue ) {
							setSelectedValue( null );
						}

						setInnerValue( e.target.value );
					} }
					value={ selectedValue || innerValue }
					placeholder={ __(
						'Start typing to search…',
						'tasty-recipes-lite'
					) }
				/>
			</div>
			{ posts.length > 0 && (
				<div className="tasty-recipes-modal-search">
					{ posts.map( ( post ) => {
						return (
							<div
								className="tasty-recipes-modal-search-result"
								key={ post.id }
							>
								<button
									onClick={ () => handlePostClick( post ) }
								>
									{ post.title.rendered } | ID: { post.id }
								</button>
							</div>
						);
					} ) }
				</div>
			) }
		</div>
	);
};

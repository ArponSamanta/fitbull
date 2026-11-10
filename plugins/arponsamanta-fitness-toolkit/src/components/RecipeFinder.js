import apiFetch from '@wordpress/api-fetch';
import { useEffect, useId, useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

export function RecipeCard( { recipe } ) {
	return (
		<li className="fb-rcard">
			<div className="fb-rcard__media" aria-hidden="true">
				{ recipe.image ? (
					<img src={ recipe.image } alt="" loading="lazy" />
				) : (
					<span className="fb-rcard__kcal">{ recipe.calories }</span>
				) }
			</div>
			<div className="fb-rcard__body">
				<h3 className="fb-rcard__title">
					<a href={ recipe.link }>{ recipe.title }</a>
				</h3>
				<p className="fb-rcard__meta">
					{ sprintf(
						/* translators: 1: kcal, 2: protein grams, 3: minutes */
						__(
							'%1$d kcal · %2$d g protein · %3$d min',
							'arponsamanta-fitness-toolkit'
						),
						recipe.calories,
						recipe.protein_g,
						recipe.prep_minutes
					) }
				</p>
				{ recipe.diets.length > 0 && (
					<span className="fb-badge">
						{ recipe.diets.join( ', ' ) }
					</span>
				) }
			</div>
		</li>
	);
}

export default function RecipeFinder( { perPage = 12, initial, filters } ) {
	const mealId = useId();
	const proteinId = useId();
	const [ diet, setDiet ] = useState( '' );
	const [ meal, setMeal ] = useState( '' );
	const [ minProtein, setMinProtein ] = useState( 0 );
	const [ data, setData ] = useState( initial );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( false );
	const first = useRef( true );

	useEffect( () => {
		if ( first.current ) {
			first.current = false;
			return;
		}
		const timer = setTimeout( () => {
			setLoading( true );
			setError( false );
			apiFetch( {
				path: addQueryArgs( '/fitbull/v1/recipes', {
					diet: diet || undefined,
					meal: meal || undefined,
					min_protein: minProtein,
					per_page: perPage,
				} ),
			} )
				.then( setData )
				.catch( () => setError( true ) )
				.finally( () => setLoading( false ) );
		}, 200 );
		return () => clearTimeout( timer );
	}, [ diet, meal, minProtein, perPage ] );

	return (
		<div className="fb-ui fb-recipes" aria-busy={ loading }>
			<div className="fb-panel">
				<div
					className="fb-lib__chips"
					role="group"
					aria-label={ __( 'Diet', 'arponsamanta-fitness-toolkit' ) }
				>
					<button
						type="button"
						className="fb-chip"
						aria-pressed={ ! diet }
						onClick={ () => setDiet( '' ) }
					>
						{ __( 'Any diet', 'arponsamanta-fitness-toolkit' ) }
					</button>
					{ filters.diets.map( ( term ) => (
						<button
							key={ term.slug }
							type="button"
							className="fb-chip"
							aria-pressed={ diet === term.slug }
							onClick={ () =>
								setDiet( diet === term.slug ? '' : term.slug )
							}
						>
							{ term.name }
						</button>
					) ) }
				</div>
				<div className="fb-row">
					<div className="fb-field">
						<label htmlFor={ mealId }>
							{ __( 'Meal', 'arponsamanta-fitness-toolkit' ) }
						</label>
						<select
							id={ mealId }
							value={ meal }
							onChange={ ( e ) => setMeal( e.target.value ) }
						>
							<option value="">
								{ __(
									'Any meal',
									'arponsamanta-fitness-toolkit'
								) }
							</option>
							{ filters.meals.map( ( term ) => (
								<option key={ term.slug } value={ term.slug }>
									{ term.name }
								</option>
							) ) }
						</select>
					</div>
					<div className="fb-field">
						<label htmlFor={ proteinId }>
							{ sprintf(
								/* translators: %d: grams of protein */
								__(
									'At least %d g protein',
									'arponsamanta-fitness-toolkit'
								),
								minProtein
							) }
						</label>
						<input
							id={ proteinId }
							type="range"
							min="0"
							max="40"
							step="5"
							value={ minProtein }
							onChange={ ( e ) =>
								setMinProtein( Number( e.target.value ) )
							}
						/>
					</div>
				</div>
				<p className="fb-muted">
					{ __(
						'Vegetarian includes vegan dishes; eggetarian adds egg dishes.',
						'arponsamanta-fitness-toolkit'
					) }
				</p>
			</div>

			<p className="fb-muted" aria-live="polite">
				{ sprintf(
					/* translators: %d: number of recipes */
					_n(
						'%d recipe',
						'%d recipes',
						data.total,
						'arponsamanta-fitness-toolkit'
					),
					data.total
				) }
			</p>
			{ error && (
				<p className="fb-notice">
					{ __(
						'Could not load recipes. Please try again.',
						'arponsamanta-fitness-toolkit'
					) }
				</p>
			) }
			{ data.items.length > 0 ? (
				<ul className="fb-recipes__grid">
					{ data.items.map( ( recipe ) => (
						<RecipeCard key={ recipe.id } recipe={ recipe } />
					) ) }
				</ul>
			) : (
				! loading && (
					<p className="fb-muted">
						{ __(
							'No recipes match. Try a lower protein minimum.',
							'arponsamanta-fitness-toolkit'
						) }
					</p>
				)
			) }
		</div>
	);
}

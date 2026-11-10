import apiFetch from '@wordpress/api-fetch';
import { useEffect, useId, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import { calculateMacros } from '../lib/macros';

const DIETS = [
	{ value: 'veg', label: __( 'Vegetarian', 'arponsamanta-fitness-toolkit' ) },
	{ value: 'vegan', label: __( 'Vegan', 'arponsamanta-fitness-toolkit' ) },
	{
		value: 'eggetarian',
		label: __( 'Eggetarian', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'non-veg',
		label: __( 'Non-veg', 'arponsamanta-fitness-toolkit' ),
	},
];

const MEALS = {
	breakfast: __( 'Breakfast', 'arponsamanta-fitness-toolkit' ),
	lunch: __( 'Lunch', 'arponsamanta-fitness-toolkit' ),
	dinner: __( 'Dinner', 'arponsamanta-fitness-toolkit' ),
	snack: __( 'Snack', 'arponsamanta-fitness-toolkit' ),
};

/** Targets saved by the macro calculator block, if the visitor used it. */
function savedTargets() {
	try {
		const saved = JSON.parse(
			window.localStorage.getItem( 'fitbull:macro-inputs' )
		);
		if ( saved ) {
			const result = calculateMacros( saved );
			return {
				calories: result.calories,
				protein: result.proteinG,
				fromCalculator: true,
			};
		}
	} catch ( e ) {
		// Storage unavailable; fall through to defaults.
	}
	return { calories: 2000, protein: 100, fromCalculator: false };
}

const servingsLabel = ( n ) =>
	n === 1
		? __( '1 serving', 'arponsamanta-fitness-toolkit' )
		: sprintf(
				/* translators: %s: number of servings, e.g. 1.5 */
				__( '%s servings', 'arponsamanta-fitness-toolkit' ),
				n
		  );

function Meter( { label, value, target, unit } ) {
	const share = target
		? Math.min( 100, Math.round( ( value * 100 ) / target ) )
		: 0;
	return (
		<div className="fb-meter">
			<div className="fb-meter__head">
				<span>{ label }</span>
				<span className="fb-muted fb-num">
					{ sprintf(
						/* translators: 1: planned amount, 2: target amount, 3: unit */
						__(
							'%1$s of %2$s %3$s',
							'arponsamanta-fitness-toolkit'
						),
						value.toLocaleString( 'en-IN' ),
						target.toLocaleString( 'en-IN' ),
						unit
					) }
				</span>
			</div>
			<div className="fb-meter__track">
				<div
					className="fb-meter__fill"
					style={ { width: `${ share }%` } }
				/>
			</div>
		</div>
	);
}

export default function MealPlanner() {
	const kcalId = useId();
	const proteinId = useId();
	const dietId = useId();
	const snackId = useId();
	const [ targets, setTargets ] = useState( {
		calories: 2000,
		protein: 100,
		fromCalculator: false,
	} );
	const [ diet, setDiet ] = useState( 'veg' );
	const [ snack, setSnack ] = useState( true );
	const [ variant, setVariant ] = useState( 0 );
	const [ plan, setPlan ] = useState( null );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( '' );

	useEffect( () => setTargets( savedTargets() ), [] );

	useEffect( () => {
		if ( targets.calories < 1000 || targets.calories > 5000 ) {
			setError(
				__(
					'Enter a calorie target between 1,000 and 5,000.',
					'arponsamanta-fitness-toolkit'
				)
			);
			return undefined;
		}
		const timer = setTimeout( () => {
			setLoading( true );
			setError( '' );
			apiFetch( {
				path: addQueryArgs( '/fitbull/v1/meal-plan', {
					calories: targets.calories,
					protein_g: Math.min( 300, Math.max( 0, targets.protein ) ),
					diet,
					include_snack: snack,
					variant,
				} ),
			} )
				.then( setPlan )
				.catch( ( e ) =>
					setError(
						e.message ||
							__(
								'Could not build a plan.',
								'arponsamanta-fitness-toolkit'
							)
					)
				)
				.finally( () => setLoading( false ) );
		}, 300 );
		return () => clearTimeout( timer );
	}, [ targets.calories, targets.protein, diet, snack, variant ] );

	const setNumber = ( key ) => ( event ) =>
		setTargets( ( t ) => ( {
			...t,
			[ key ]: Number( event.target.value ) || 0,
			fromCalculator: false,
		} ) );

	return (
		<div className="fb-ui fb-meals" aria-busy={ loading }>
			<div className="fb-panel">
				<p className="fb-muted">
					{ targets.fromCalculator
						? __(
								'Using the targets from your macro calculator. Change them here if you like.',
								'arponsamanta-fitness-toolkit'
						  )
						: __(
								'Tip: use the macro calculator on the Tools page first and your targets will appear here.',
								'arponsamanta-fitness-toolkit'
						  ) }
				</p>
				<div className="fb-row">
					<div className="fb-field">
						<label htmlFor={ kcalId }>
							{ __(
								'Calories per day',
								'arponsamanta-fitness-toolkit'
							) }
						</label>
						<input
							id={ kcalId }
							type="number"
							min="1000"
							max="5000"
							step="50"
							value={ targets.calories }
							onChange={ setNumber( 'calories' ) }
						/>
					</div>
					<div className="fb-field">
						<label htmlFor={ proteinId }>
							{ __(
								'Protein (g)',
								'arponsamanta-fitness-toolkit'
							) }
						</label>
						<input
							id={ proteinId }
							type="number"
							min="0"
							max="300"
							step="5"
							value={ targets.protein }
							onChange={ setNumber( 'protein' ) }
						/>
					</div>
					<div className="fb-field">
						<label htmlFor={ dietId }>
							{ __( 'Diet', 'arponsamanta-fitness-toolkit' ) }
						</label>
						<select
							id={ dietId }
							value={ diet }
							onChange={ ( e ) => setDiet( e.target.value ) }
						>
							{ DIETS.map( ( d ) => (
								<option key={ d.value } value={ d.value }>
									{ d.label }
								</option>
							) ) }
						</select>
					</div>
				</div>
				<div className="fb-row">
					<label className="fb-check" htmlFor={ snackId }>
						<input
							id={ snackId }
							type="checkbox"
							checked={ snack }
							onChange={ ( e ) => setSnack( e.target.checked ) }
						/>
						{ __(
							'Include a snack',
							'arponsamanta-fitness-toolkit'
						) }
					</label>
					<button
						type="button"
						className="fb-btn fb-btn--ghost"
						disabled={ ! plan || plan.variants < 2 }
						onClick={ () =>
							setVariant(
								( v ) =>
									( v + 1 ) %
									Math.max( 1, plan?.variants || 1 )
							)
						}
					>
						{ __(
							'Shuffle meals',
							'arponsamanta-fitness-toolkit'
						) }
					</button>
				</div>
			</div>

			{ error && <p className="fb-notice">{ error }</p> }

			{ plan && ! error && (
				<div className="fb-meals__result" aria-live="polite">
					{ plan.missing.length > 0 && (
						<p className="fb-notice">
							{ sprintf(
								/* translators: %s: list of meals */
								__(
									'No recipes yet for: %s. Add some, or try another diet.',
									'arponsamanta-fitness-toolkit'
								),
								plan.missing
									.map( ( m ) => MEALS[ m ] )
									.join( ', ' )
							) }
						</p>
					) }
					{ plan.meals.length > 0 && (
						<>
							<div className="fb-panel">
								<Meter
									label={ __(
										'Calories',
										'arponsamanta-fitness-toolkit'
									) }
									value={ plan.totals.calories }
									target={ plan.target.calories }
									unit="kcal"
								/>
								<Meter
									label={ __(
										'Protein',
										'arponsamanta-fitness-toolkit'
									) }
									value={ plan.totals.protein_g }
									target={ plan.target.protein_g }
									unit="g"
								/>
								<p className="fb-muted">
									{ sprintf(
										/* translators: 1: carbs grams, 2: fat grams */
										__(
											'Also %1$d g carbs and %2$d g fat.',
											'arponsamanta-fitness-toolkit'
										),
										plan.totals.carbs_g,
										plan.totals.fat_g
									) }
								</p>
							</div>
							<ol className="fb-meals__list">
								{ plan.meals.map( ( m ) => (
									<li
										key={ m.meal }
										className="fb-panel fb-meals__meal"
									>
										<span className="fb-plan__weekday">
											{ MEALS[ m.meal ] }
										</span>
										<h3 className="fb-meals__title">
											<a href={ m.recipe.link }>
												{ m.recipe.title }
											</a>
										</h3>
										<p className="fb-muted">
											{ servingsLabel( m.servings ) } ·{ ' ' }
											{ sprintf(
												/* translators: 1: kcal, 2: protein grams */
												__(
													'%1$d kcal · %2$d g protein',
													'arponsamanta-fitness-toolkit'
												),
												m.calories,
												m.protein_g
											) }
										</p>
									</li>
								) ) }
							</ol>
							<p className="fb-muted">
								{ __(
									'Nutrition values are approximate. If you have a medical condition, check your plan with a doctor or registered dietitian.',
									'arponsamanta-fitness-toolkit'
								) }
							</p>
						</>
					) }
				</div>
			) }
		</div>
	);
}

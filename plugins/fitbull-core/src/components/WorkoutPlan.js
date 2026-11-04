import apiFetch from '@wordpress/api-fetch';
import { useId, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

const GOALS = [
	{ value: 'muscle', label: __( 'Build muscle', 'fitbull-core' ) },
	{ value: 'strength', label: __( 'Get stronger', 'fitbull-core' ) },
	{ value: 'fat_loss', label: __( 'Lose fat', 'fitbull-core' ) },
];

const LEVELS = [
	{ value: 'beginner', label: __( 'Beginner', 'fitbull-core' ) },
	{ value: 'intermediate', label: __( 'Intermediate', 'fitbull-core' ) },
];

const SESSIONS = {
	full_a: __( 'Full body A', 'fitbull-core' ),
	full_b: __( 'Full body B', 'fitbull-core' ),
	upper: __( 'Upper body', 'fitbull-core' ),
	lower: __( 'Lower body', 'fitbull-core' ),
	push: __( 'Push', 'fitbull-core' ),
	pull: __( 'Pull', 'fitbull-core' ),
	legs: __( 'Legs', 'fitbull-core' ),
};

const PATTERNS = {
	squat: __( 'squat', 'fitbull-core' ),
	hinge: __( 'hip hinge', 'fitbull-core' ),
	lunge: __( 'lunge', 'fitbull-core' ),
	push_h: __( 'chest press', 'fitbull-core' ),
	push_v: __( 'overhead press', 'fitbull-core' ),
	pull_h: __( 'row', 'fitbull-core' ),
	pull_v: __( 'pulldown or pull-up', 'fitbull-core' ),
	core: __( 'core', 'fitbull-core' ),
	arms: __( 'arm', 'fitbull-core' ),
};

const TIPS = {
	strength: __(
		'Add 2.5 kg to a lift when you hit the top of the rep range on every set.',
		'fitbull-core'
	),
	muscle: __(
		'Add a rep each week. Once you reach the top of the range on every set, add weight.',
		'fitbull-core'
	),
	fat_loss: __(
		'Keep rests short, stay in a small calorie deficit and walk on rest days.',
		'fitbull-core'
	),
};

function Segmented( { label, options, value, onChange } ) {
	return (
		<fieldset className="fb-field">
			<legend>{ label }</legend>
			<div
				className={ `fb-seg fb-seg--${ options.length }` }
				style={ { '--fb-count': options.length } }
			>
				{ options.map( ( option ) => (
					<button
						key={ option.value }
						type="button"
						aria-pressed={ value === option.value }
						onClick={ () => onChange( option.value ) }
					>
						{ option.label }
					</button>
				) ) }
			</div>
		</fieldset>
	);
}

function ExerciseRow( { item, onSwap } ) {
	return (
		<li className="fb-plan__row">
			<div className="fb-plan__name">
				<a href={ item.link }>{ item.title }</a>
				<span className="fb-muted">
					{ sprintf(
						/* translators: 1: sets, 2: reps, 3: rest in seconds */
						__( '%1$d sets × %2$s · rest %3$ds', 'fitbull-core' ),
						item.sets,
						item.reps,
						item.rest_seconds
					) }
				</span>
			</div>
			{ item.alternatives.length > 0 && (
				<button
					type="button"
					className="fb-btn fb-btn--ghost fb-btn--small fb-no-print"
					onClick={ onSwap }
					aria-label={ sprintf(
						/* translators: %s: exercise name */
						__( 'Swap %s for another exercise', 'fitbull-core' ),
						item.title
					) }
				>
					{ __( 'Swap', 'fitbull-core' ) }
				</button>
			) }
		</li>
	);
}

export default function WorkoutPlan( { equipment = [] } ) {
	const baseId = useId();
	const [ goal, setGoal ] = useState( 'muscle' );
	const [ days, setDays ] = useState( 3 );
	const [ level, setLevel ] = useState( 'beginner' );
	const [ owned, setOwned ] = useState( [] );
	const [ plan, setPlan ] = useState( null );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( '' );

	const toggle = ( slug ) =>
		setOwned( ( current ) =>
			current.includes( slug )
				? current.filter( ( s ) => s !== slug )
				: [ ...current, slug ]
		);

	const build = ( event ) => {
		event.preventDefault();
		setLoading( true );
		setError( '' );
		apiFetch( {
			path: addQueryArgs( '/fitbull/v1/workout-plan', {
				goal,
				days,
				level,
				equipment: owned.join( ',' ),
			} ),
		} )
			.then( setPlan )
			.catch( ( e ) =>
				setError(
					e.message || __( 'Could not build a plan.', 'fitbull-core' )
				)
			)
			.finally( () => setLoading( false ) );
	};

	// Replace an exercise with its next alternative, everywhere it appears
	// this week, keeping the sets and reps.
	const swap = ( item ) => {
		const [ next, ...rest ] = item.alternatives;
		const replacement = {
			...item,
			id: next.id,
			title: next.title,
			link: next.link,
			alternatives: [
				...rest,
				{ id: item.id, title: item.title, link: item.link },
			],
		};
		setPlan( ( current ) => ( {
			...current,
			days: current.days.map( ( day ) => ( {
				...day,
				exercises: day.exercises.map( ( e ) =>
					e.id === item.id && e.slot === item.slot ? replacement : e
				),
			} ) ),
		} ) );
	};

	const thinDays = plan
		? plan.days.filter( ( d ) => d.exercises.length < 3 )
		: [];

	return (
		<div className="fb-ui fb-plan">
			<form className="fb-panel fb-no-print" onSubmit={ build }>
				<Segmented
					label={ __( 'Goal', 'fitbull-core' ) }
					options={ GOALS }
					value={ goal }
					onChange={ setGoal }
				/>
				<Segmented
					label={ __( 'Days per week', 'fitbull-core' ) }
					options={ [ 2, 3, 4, 5 ].map( ( n ) => ( {
						value: n,
						label: String( n ),
					} ) ) }
					value={ days }
					onChange={ setDays }
				/>
				<Segmented
					label={ __( 'Level', 'fitbull-core' ) }
					options={ LEVELS }
					value={ level }
					onChange={ setLevel }
				/>
				<fieldset className="fb-field">
					<legend>
						{ __(
							'Equipment you have (bodyweight is always included)',
							'fitbull-core'
						) }
					</legend>
					<div className="fb-checks">
						{ equipment.map( ( item ) => (
							<label
								key={ item.slug }
								className="fb-check"
								htmlFor={ `${ baseId }-${ item.slug }` }
							>
								<input
									id={ `${ baseId }-${ item.slug }` }
									type="checkbox"
									checked={ owned.includes( item.slug ) }
									onChange={ () => toggle( item.slug ) }
								/>
								{ item.name }
							</label>
						) ) }
					</div>
				</fieldset>
				<div className="fb-row">
					<button
						type="submit"
						className="fb-btn"
						disabled={ loading }
					>
						{ loading
							? __( 'Building…', 'fitbull-core' )
							: __( 'Build my plan', 'fitbull-core' ) }
					</button>
				</div>
			</form>

			{ error && <p className="fb-notice">{ error }</p> }

			{ plan && (
				<div className="fb-plan__result" aria-live="polite">
					<div className="fb-row fb-plan__head">
						<p className="fb-muted">
							{ TIPS[ plan.summary.goal ] }
						</p>
						<button
							type="button"
							className="fb-btn fb-btn--ghost fb-btn--small fb-no-print"
							onClick={ () => window.print() }
						>
							{ __( 'Print plan', 'fitbull-core' ) }
						</button>
					</div>

					{ plan.notes.length > 0 && (
						<p className="fb-notice">
							<strong>
								{ __( 'Gap in your plan:', 'fitbull-core' ) }
							</strong>
							{ sprintf(
								/* translators: %s: list of movement types */
								__(
									'no %s exercise matches your equipment and level. Resistance bands are an inexpensive way to add pulling exercises.',
									'fitbull-core'
								),
								plan.notes
									.map( ( n ) => PATTERNS[ n ] || n )
									.join( ', ' )
							) }
						</p>
					) }
					{ thinDays.length > 0 && (
						<p className="fb-notice">
							{ __(
								'Some sessions are short with this equipment. Try fewer days for full-body sessions, or tick more equipment.',
								'fitbull-core'
							) }
						</p>
					) }

					<ol className="fb-plan__days">
						{ plan.days.map( ( day ) => (
							<li
								key={ day.day }
								className="fb-panel fb-plan__day"
							>
								<div className="fb-plan__day-head">
									<span className="fb-plan__weekday">
										{ day.weekday }
									</span>
									<h3 className="fb-panel__title">
										{ SESSIONS[ day.session ] }
									</h3>
								</div>
								<ul className="fb-plan__list">
									{ day.exercises.map( ( item, i ) => (
										<ExerciseRow
											key={ `${ item.slot }-${ i }` }
											item={ item }
											onSwap={ () => swap( item ) }
										/>
									) ) }
								</ul>
							</li>
						) ) }
					</ol>
					<p className="fb-muted">
						{ __(
							'Warm up for 5 to 10 minutes first. Stop any exercise that causes sharp pain. This is general guidance, not medical advice.',
							'fitbull-core'
						) }
					</p>
				</div>
			) }
		</div>
	);
}

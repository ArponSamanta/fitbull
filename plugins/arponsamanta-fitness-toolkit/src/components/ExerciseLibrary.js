import apiFetch from '@wordpress/api-fetch';
import { useEffect, useRef, useState, useId } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

export const DIFFICULTY_LABELS = {
	beginner: __( 'Beginner', 'arponsamanta-fitness-toolkit' ),
	intermediate: __( 'Intermediate', 'arponsamanta-fitness-toolkit' ),
	advanced: __( 'Advanced', 'arponsamanta-fitness-toolkit' ),
};

export function ExerciseCard( { exercise } ) {
	const meta = [
		DIFFICULTY_LABELS[ exercise.difficulty ],
		exercise.equipment.join( ', ' ),
		exercise.muscles.join( ', ' ),
	]
		.filter( Boolean )
		.join( ' · ' );

	return (
		<li className="fb-ex-card">
			<div className="fb-ex-card__media" aria-hidden="true">
				{ exercise.image ? (
					<img src={ exercise.image } alt="" loading="lazy" />
				) : (
					<span className="fb-ex-card__initial">
						{ exercise.title.charAt( 0 ) }
					</span>
				) }
			</div>
			<div className="fb-ex-card__body">
				<h3 className="fb-ex-card__title">
					<a href={ exercise.link }>{ exercise.title }</a>
				</h3>
				<p className="fb-ex-card__meta">{ meta }</p>
			</div>
			<span className={ `fb-badge fb-badge--${ exercise.difficulty }` }>
				{ DIFFICULTY_LABELS[ exercise.difficulty ] }
			</span>
		</li>
	);
}

function useDebounced( value, delay ) {
	const [ debounced, setDebounced ] = useState( value );
	useEffect( () => {
		const timer = setTimeout( () => setDebounced( value ), delay );
		return () => clearTimeout( timer );
	}, [ value, delay ] );
	return debounced;
}

export default function ExerciseLibrary( {
	perPage = 12,
	initial,
	filters,
	showFilters = true,
} ) {
	const searchId = useId();
	const equipmentId = useId();
	const levelId = useId();

	const [ muscle, setMuscle ] = useState( '' );
	const [ equipment, setEquipment ] = useState( '' );
	const [ difficulty, setDifficulty ] = useState( '' );
	const [ search, setSearch ] = useState( '' );
	const [ data, setData ] = useState( initial );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( false );

	const query = useDebounced( search.trim(), 300 );
	const isFirst = useRef( true );

	const load = ( page, append ) => {
		setLoading( true );
		setError( false );
		return apiFetch( {
			path: addQueryArgs( '/fitbull/v1/exercises', {
				muscle: muscle || undefined,
				equipment: equipment || undefined,
				difficulty: difficulty || undefined,
				search: query || undefined,
				page,
				per_page: perPage,
			} ),
		} )
			.then( ( next ) =>
				setData( ( current ) =>
					append
						? {
								...next,
								items: [ ...current.items, ...next.items ],
						  }
						: next
				)
			)
			.catch( () => setError( true ) )
			.finally( () => setLoading( false ) );
	};

	useEffect( () => {
		// The server already rendered the unfiltered first page.
		if ( isFirst.current ) {
			isFirst.current = false;
			return;
		}
		load( 1, false );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ muscle, equipment, difficulty, query ] );

	const hasFilters = muscle || equipment || difficulty || query;
	const reset = () => {
		setMuscle( '' );
		setEquipment( '' );
		setDifficulty( '' );
		setSearch( '' );
	};

	return (
		<div className="fb-lib" aria-busy={ loading }>
			{ showFilters && (
				<div className="fb-lib__filters">
					<div
						className="fb-lib__chips"
						role="group"
						aria-label={ __( 'Muscle', 'arponsamanta-fitness-toolkit' ) }
					>
						<button
							type="button"
							className="fb-chip"
							aria-pressed={ ! muscle }
							onClick={ () => setMuscle( '' ) }
						>
							{ __( 'All muscles', 'arponsamanta-fitness-toolkit' ) }
						</button>
						{ filters.muscles.map( ( term ) => (
							<button
								key={ term.slug }
								type="button"
								className="fb-chip"
								aria-pressed={ muscle === term.slug }
								onClick={ () =>
									setMuscle(
										muscle === term.slug ? '' : term.slug
									)
								}
							>
								{ term.name }
							</button>
						) ) }
					</div>

					<div className="fb-lib__row">
						<div className="fb-lib__field fb-lib__field--grow">
							<label htmlFor={ searchId }>
								{ __( 'Search', 'arponsamanta-fitness-toolkit' ) }
							</label>
							<input
								id={ searchId }
								type="search"
								placeholder={ __(
									'Squat, push-up, row…',
									'arponsamanta-fitness-toolkit'
								) }
								value={ search }
								onChange={ ( event ) =>
									setSearch( event.target.value )
								}
							/>
						</div>
						<div className="fb-lib__field">
							<label htmlFor={ equipmentId }>
								{ __( 'Equipment', 'arponsamanta-fitness-toolkit' ) }
							</label>
							<select
								id={ equipmentId }
								value={ equipment }
								onChange={ ( event ) =>
									setEquipment( event.target.value )
								}
							>
								<option value="">
									{ __( 'Any', 'arponsamanta-fitness-toolkit' ) }
								</option>
								{ filters.equipment.map( ( term ) => (
									<option
										key={ term.slug }
										value={ term.slug }
									>
										{ term.name }
									</option>
								) ) }
							</select>
						</div>
						<div className="fb-lib__field">
							<label htmlFor={ levelId }>
								{ __( 'Level', 'arponsamanta-fitness-toolkit' ) }
							</label>
							<select
								id={ levelId }
								value={ difficulty }
								onChange={ ( event ) =>
									setDifficulty( event.target.value )
								}
							>
								<option value="">
									{ __( 'Any', 'arponsamanta-fitness-toolkit' ) }
								</option>
								{ filters.difficulties.map( ( level ) => (
									<option key={ level } value={ level }>
										{ DIFFICULTY_LABELS[ level ] }
									</option>
								) ) }
							</select>
						</div>
					</div>
				</div>
			) }

			<div className="fb-lib__status" aria-live="polite">
				<span>
					{ sprintf(
						/* translators: %d: number of exercises */
						_n(
							'%d exercise',
							'%d exercises',
							data.total,
							'arponsamanta-fitness-toolkit'
						),
						data.total
					) }
				</span>
				{ hasFilters && (
					<button
						type="button"
						className="fb-lib__reset"
						onClick={ reset }
					>
						{ __( 'Clear filters', 'arponsamanta-fitness-toolkit' ) }
					</button>
				) }
			</div>

			{ error && (
				<p className="fb-lib__error">
					{ __(
						'Could not load exercises. Please try again.',
						'arponsamanta-fitness-toolkit'
					) }
				</p>
			) }

			{ data.items.length > 0 ? (
				<ul className="fb-lib__grid">
					{ data.items.map( ( exercise ) => (
						<ExerciseCard
							key={ exercise.id }
							exercise={ exercise }
						/>
					) ) }
				</ul>
			) : (
				! loading && (
					<p className="fb-lib__empty">
						{ __(
							'No exercises match. Try fewer filters.',
							'arponsamanta-fitness-toolkit'
						) }
					</p>
				)
			) }

			{ data.page < data.pages && (
				<button
					type="button"
					className="fb-lib__more"
					disabled={ loading }
					onClick={ () => load( data.page + 1, true ) }
				>
					{ loading
						? __( 'Loading…', 'arponsamanta-fitness-toolkit' )
						: __( 'Show more', 'arponsamanta-fitness-toolkit' ) }
				</button>
			) }
		</div>
	);
}

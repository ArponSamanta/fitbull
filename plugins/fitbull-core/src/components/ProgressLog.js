import apiFetch from '@wordpress/api-fetch';
import { useEffect, useId, useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { estimateOneRepMax } from '../lib/oneRepMax';

const PATH = '/fitbull/v1/logs';

// Small accessible line chart of bodyweight over time.
function WeightChart( { entries } ) {
	const points = [ ...entries ]
		.sort( ( a, b ) => ( a.date < b.date ? -1 : 1 ) )
		.slice( -30 );
	if ( points.length < 2 ) {
		return (
			<p className="fb-muted">
				{ __(
					'Log your weight on two different days to see a trend.',
					'fitbull-core'
				) }
			</p>
		);
	}
	const w = 640;
	const h = 180;
	const pad = 28;
	const values = points.map( ( p ) => p.weight_kg );
	const min = Math.min( ...values );
	const max = Math.max( ...values );
	const span = max - min || 1;
	const x = ( i ) => pad + ( i * ( w - pad * 2 ) ) / ( points.length - 1 );
	const y = ( v ) => h - pad - ( ( v - min ) * ( h - pad * 2 ) ) / span;
	const line = points
		.map( ( p, i ) => `${ x( i ) },${ y( p.weight_kg ) }` )
		.join( ' ' );
	const first = points[ 0 ];
	const last = points[ points.length - 1 ];
	const change = Math.round( ( last.weight_kg - first.weight_kg ) * 10 ) / 10;

	return (
		<figure className="fb-log__chart">
			<svg
				viewBox={ `0 0 ${ w } ${ h }` }
				role="img"
				aria-label={ sprintf(
					/* translators: 1: start weight, 2: end weight, 3: start date, 4: end date */
					__(
						'Bodyweight went from %1$s kg to %2$s kg between %3$s and %4$s.',
						'fitbull-core'
					),
					first.weight_kg,
					last.weight_kg,
					first.date,
					last.date
				) }
			>
				<line
					x1={ pad }
					x2={ w - pad }
					y1={ y( min ) }
					y2={ y( min ) }
					className="fb-log__grid"
				/>
				<line
					x1={ pad }
					x2={ w - pad }
					y1={ y( max ) }
					y2={ y( max ) }
					className="fb-log__grid"
				/>
				<text x={ pad } y={ y( max ) - 8 } className="fb-log__axis">
					{ max } kg
				</text>
				<text x={ pad } y={ y( min ) + 18 } className="fb-log__axis">
					{ min } kg
				</text>
				<polyline points={ line } className="fb-log__line" />
				{ points.map( ( p, i ) => (
					<circle
						key={ p.id }
						cx={ x( i ) }
						cy={ y( p.weight_kg ) }
						r="4"
						className="fb-log__dot"
					/>
				) ) }
			</svg>
			<figcaption className="fb-muted">
				{ sprintf(
					/* translators: 1: change in kg with sign, 2: number of entries */
					__(
						'%1$s kg over your last %2$d entries.',
						'fitbull-core'
					),
					change > 0 ? `+${ change }` : String( change ),
					points.length
				) }
			</figcaption>
		</figure>
	);
}

export default function ProgressLog( { exercises = [], today } ) {
	const dateId = useId();
	const weightId = useId();
	const repsId = useId();
	const exerciseId = useId();

	const [ tab, setTab ] = useState( 'weight' );
	const [ entries, setEntries ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ form, setForm ] = useState( {
		date: today,
		weight: '',
		reps: '5',
		exercise: exercises[ 0 ]?.id || '',
	} );

	useEffect( () => {
		apiFetch( { path: PATH } )
			.then( setEntries )
			.catch( () =>
				setMessage( __( 'Could not load your log.', 'fitbull-core' ) )
			)
			.finally( () => setLoading( false ) );
	}, [] );

	const weights = entries.filter( ( e ) => e.type === 'weight' );
	const lifts = entries.filter( ( e ) => e.type === 'lift' );

	const bests = useMemo( () => {
		const byExercise = {};
		lifts.forEach( ( e ) => {
			const est = estimateOneRepMax( e.weight_kg, e.reps );
			const best = byExercise[ e.exercise_id ];
			if ( ! best || est > best.estimate ) {
				byExercise[ e.exercise_id ] = { ...e, estimate: est };
			}
		} );
		return Object.values( byExercise ).sort(
			( a, b ) => b.estimate - a.estimate
		);
	}, [ lifts ] );

	const update = ( key ) => ( event ) =>
		setForm( ( f ) => ( { ...f, [ key ]: event.target.value } ) );

	const submit = ( event ) => {
		event.preventDefault();
		setSaving( true );
		setMessage( '' );
		const data = {
			type: tab,
			date: form.date,
			weight_kg: Number( form.weight ),
		};
		if ( tab === 'lift' ) {
			data.exercise_id = Number( form.exercise );
			data.reps = Number( form.reps );
		}
		apiFetch( { path: PATH, method: 'POST', data } )
			.then( ( saved ) => {
				setEntries( ( list ) =>
					[ saved, ...list ].sort( ( a, b ) =>
						a.date < b.date ? 1 : -1
					)
				);
				setForm( ( f ) => ( { ...f, weight: '' } ) );
				setMessage( __( 'Saved.', 'fitbull-core' ) );
			} )
			.catch( ( e ) =>
				setMessage(
					e.message || __( 'Could not save.', 'fitbull-core' )
				)
			)
			.finally( () => setSaving( false ) );
	};

	const remove = ( entry ) => {
		apiFetch( { path: `${ PATH }/${ entry.id }`, method: 'DELETE' } )
			.then( () =>
				setEntries( ( list ) =>
					list.filter( ( e ) => e.id !== entry.id )
				)
			)
			.catch( ( e ) => setMessage( e.message ) );
	};

	const shown = tab === 'weight' ? weights : lifts;

	return (
		<div className="fb-ui fb-log">
			<div
				className="fb-seg"
				style={ { '--fb-count': 2 } }
				role="group"
				aria-label={ __( 'What to log', 'fitbull-core' ) }
			>
				<button
					type="button"
					aria-pressed={ tab === 'weight' }
					onClick={ () => setTab( 'weight' ) }
				>
					{ __( 'Bodyweight', 'fitbull-core' ) }
				</button>
				<button
					type="button"
					aria-pressed={ tab === 'lift' }
					onClick={ () => setTab( 'lift' ) }
				>
					{ __( 'Lifts', 'fitbull-core' ) }
				</button>
			</div>

			<form className="fb-panel" onSubmit={ submit }>
				<div className="fb-row">
					<div className="fb-field">
						<label htmlFor={ dateId }>
							{ __( 'Date', 'fitbull-core' ) }
						</label>
						<input
							id={ dateId }
							type="date"
							max={ today }
							required
							value={ form.date }
							onChange={ update( 'date' ) }
						/>
					</div>
					{ tab === 'lift' && (
						<div className="fb-field">
							<label htmlFor={ exerciseId }>
								{ __( 'Exercise', 'fitbull-core' ) }
							</label>
							<select
								id={ exerciseId }
								value={ form.exercise }
								onChange={ update( 'exercise' ) }
							>
								{ exercises.map( ( e ) => (
									<option key={ e.id } value={ e.id }>
										{ e.title }
									</option>
								) ) }
							</select>
						</div>
					) }
					<div className="fb-field">
						<label htmlFor={ weightId }>
							{ tab === 'weight'
								? __( 'Bodyweight (kg)', 'fitbull-core' )
								: __( 'Weight lifted (kg)', 'fitbull-core' ) }
						</label>
						<input
							id={ weightId }
							type="number"
							inputMode="decimal"
							min="1"
							max="500"
							step="0.1"
							required
							value={ form.weight }
							onChange={ update( 'weight' ) }
						/>
					</div>
					{ tab === 'lift' && (
						<div className="fb-field">
							<label htmlFor={ repsId }>
								{ __( 'Reps', 'fitbull-core' ) }
							</label>
							<input
								id={ repsId }
								type="number"
								min="1"
								max="30"
								required
								value={ form.reps }
								onChange={ update( 'reps' ) }
							/>
						</div>
					) }
					<button
						type="submit"
						className="fb-btn"
						disabled={ saving }
					>
						{ saving
							? __( 'Saving…', 'fitbull-core' )
							: __( 'Add entry', 'fitbull-core' ) }
					</button>
				</div>
				{ message && (
					<p className="fb-muted" role="status">
						{ message }
					</p>
				) }
			</form>

			{ tab === 'weight' && ! loading && (
				<div className="fb-panel">
					<h3 className="fb-panel__title">
						{ __( 'Trend', 'fitbull-core' ) }
					</h3>
					<WeightChart entries={ weights } />
				</div>
			) }

			{ tab === 'lift' && bests.length > 0 && (
				<div className="fb-panel">
					<h3 className="fb-panel__title">
						{ __( 'Best estimated maxes', 'fitbull-core' ) }
					</h3>
					<div className="fb-stats">
						{ bests.slice( 0, 6 ).map( ( b ) => (
							<div key={ b.exercise_id } className="fb-stat">
								<span className="fb-stat__value">
									{ b.estimate }
								</span>
								<span className="fb-stat__label">
									{ sprintf(
										/* translators: 1: exercise name, 2: weight, 3: reps */
										__(
											'%1$s (from %2$s kg × %3$d)',
											'fitbull-core'
										),
										b.exercise_title,
										b.weight_kg,
										b.reps
									) }
								</span>
							</div>
						) ) }
					</div>
				</div>
			) }

			<div className="fb-panel">
				<h3 className="fb-panel__title">
					{ __( 'History', 'fitbull-core' ) }
				</h3>
				{ loading && (
					<p className="fb-muted">
						{ __( 'Loading…', 'fitbull-core' ) }
					</p>
				) }
				{ ! loading && shown.length === 0 && (
					<p className="fb-muted">
						{ __( 'No entries yet.', 'fitbull-core' ) }
					</p>
				) }
				{ shown.length > 0 && (
					<div className="fb-table-wrap">
						<table className="fb-table">
							<thead>
								<tr>
									<th scope="col">
										{ __( 'Date', 'fitbull-core' ) }
									</th>
									{ tab === 'lift' && (
										<th scope="col">
											{ __( 'Exercise', 'fitbull-core' ) }
										</th>
									) }
									<th scope="col">
										{ __( 'Weight', 'fitbull-core' ) }
									</th>
									{ tab === 'lift' && (
										<th scope="col">
											{ __( 'Reps', 'fitbull-core' ) }
										</th>
									) }
									<th scope="col">
										<span className="fb-sr-only">
											{ __( 'Actions', 'fitbull-core' ) }
										</span>
									</th>
								</tr>
							</thead>
							<tbody>
								{ shown.map( ( e ) => (
									<tr key={ e.id }>
										<td className="fb-num">{ e.date }</td>
										{ tab === 'lift' && (
											<td>{ e.exercise_title }</td>
										) }
										<td className="fb-num">
											{ e.weight_kg } kg
										</td>
										{ tab === 'lift' && (
											<td className="fb-num">
												{ e.reps }
											</td>
										) }
										<td>
											<button
												type="button"
												className="fb-link-button"
												onClick={ () => remove( e ) }
											>
												{ __(
													'Delete',
													'fitbull-core'
												) }
												<span className="fb-sr-only">{ ` ${ e.date }` }</span>
											</button>
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				) }
			</div>
		</div>
	);
}

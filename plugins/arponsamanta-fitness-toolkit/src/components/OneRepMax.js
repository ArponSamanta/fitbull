import { useId, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { estimateOneRepMax, MAX_REPS, workingWeights } from '../lib/oneRepMax';

export default function OneRepMax() {
	const weightId = useId();
	const repsId = useId();
	const [ weight, setWeight ] = useState( '60' );
	const [ reps, setReps ] = useState( '5' );

	const max = estimateOneRepMax( weight, reps );
	const rows = max ? workingWeights( max ) : [];

	return (
		<div className="fb-ui">
			<div className="fb-panel">
				<div className="fb-row">
					<div className="fb-field">
						<label htmlFor={ weightId }>
							{ __(
								'Weight lifted (kg)',
								'arponsamanta-fitness-toolkit'
							) }
						</label>
						<input
							id={ weightId }
							type="number"
							inputMode="decimal"
							min="1"
							max="500"
							step="0.5"
							value={ weight }
							onChange={ ( e ) => setWeight( e.target.value ) }
						/>
					</div>
					<div className="fb-field">
						<label htmlFor={ repsId }>
							{ __(
								'Reps done',
								'arponsamanta-fitness-toolkit'
							) }
						</label>
						<input
							id={ repsId }
							type="number"
							inputMode="numeric"
							min="1"
							max={ MAX_REPS }
							value={ reps }
							onChange={ ( e ) => setReps( e.target.value ) }
						/>
					</div>
				</div>

				<div aria-live="polite">
					{ max ? (
						<div className="fb-stats">
							<div className="fb-stat">
								<span className="fb-stat__value">
									{ sprintf(
										/* translators: %s: weight in kg */
										__(
											'%s kg',
											'arponsamanta-fitness-toolkit'
										),
										max
									) }
								</span>
								<span className="fb-stat__label">
									{ __(
										'Estimated one-rep max',
										'arponsamanta-fitness-toolkit'
									) }
								</span>
							</div>
						</div>
					) : (
						<p className="fb-notice">
							{ sprintf(
								/* translators: %d: maximum reps */
								__(
									'Enter a weight and between 1 and %d reps.',
									'arponsamanta-fitness-toolkit'
								),
								MAX_REPS
							) }
						</p>
					) }
				</div>

				{ max > 0 && Number( reps ) > 10 && (
					<p className="fb-muted">
						{ __(
							'Estimates are less accurate above 10 reps. A heavier set of 3 to 6 gives a better number.',
							'arponsamanta-fitness-toolkit'
						) }
					</p>
				) }
			</div>

			{ rows.length > 0 && (
				<div className="fb-panel">
					<h3 className="fb-panel__title">
						{ __(
							'Working weights',
							'arponsamanta-fitness-toolkit'
						) }
					</h3>
					<div className="fb-table-wrap">
						<table className="fb-table">
							<thead>
								<tr>
									<th scope="col">
										{ __(
											'% of max',
											'arponsamanta-fitness-toolkit'
										) }
									</th>
									<th scope="col">
										{ __(
											'Weight',
											'arponsamanta-fitness-toolkit'
										) }
									</th>
									<th scope="col">
										{ __(
											'About this many reps',
											'arponsamanta-fitness-toolkit'
										) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( row ) => (
									<tr key={ row.percent }>
										<td className="fb-num">
											{ row.percent }%
										</td>
										<td className="fb-num">
											{ sprintf(
												/* translators: %s: weight in kg */
												__(
													'%s kg',
													'arponsamanta-fitness-toolkit'
												),
												row.weight
											) }
										</td>
										<td className="fb-num">{ row.reps }</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
					<p className="fb-muted">
						{ __(
							'Weights are rounded to 2.5 kg. Never test a true max without a spotter or safety arms.',
							'arponsamanta-fitness-toolkit'
						) }
					</p>
				</div>
			) }
		</div>
	);
}

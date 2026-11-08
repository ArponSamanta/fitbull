/**
 * One-rep-max estimates and working weights.
 *
 * Uses the average of the Epley and Brzycki formulas, which agree closely
 * for sets of up to about 10 reps and drift apart above that.
 */

export const MAX_REPS = 15;

/** Percent of 1RM and roughly how many reps you can do at it. */
export const PERCENT_TABLE = [
	{ percent: 100, reps: 1 },
	{ percent: 95, reps: 2 },
	{ percent: 90, reps: 4 },
	{ percent: 85, reps: 6 },
	{ percent: 80, reps: 8 },
	{ percent: 75, reps: 10 },
	{ percent: 70, reps: 12 },
	{ percent: 65, reps: 15 },
];

const roundTo = ( value, step ) => Math.round( value / step ) * step;

export function epley( weight, reps ) {
	return weight * ( 1 + reps / 30 );
}

export function brzycki( weight, reps ) {
	return ( weight * 36 ) / ( 37 - reps );
}

/**
 * @param {number} weight Weight lifted, kg.
 * @param {number} reps   Reps completed, 1 to MAX_REPS.
 * @return {number} Estimated 1RM in kg, to the nearest 0.5 kg (0 if invalid).
 */
export function estimateOneRepMax( weight, reps ) {
	const w = Number( weight );
	const r = Math.round( Number( reps ) );
	if ( ! ( w > 0 ) || r < 1 || r > MAX_REPS ) {
		return 0;
	}
	if ( r === 1 ) {
		return roundTo( w, 0.5 );
	}
	return roundTo( ( epley( w, r ) + brzycki( w, r ) ) / 2, 0.5 );
}

/**
 * Working weights from a 1RM, rounded to 2.5 kg plate jumps.
 *
 * @param {number} oneRepMax Estimated 1RM, kg.
 * @return {Array<{percent:number,reps:number,weight:number}>} Rows.
 */
export function workingWeights( oneRepMax ) {
	return PERCENT_TABLE.map( ( row ) => ( {
		...row,
		weight: roundTo( ( oneRepMax * row.percent ) / 100, 2.5 ),
	} ) );
}

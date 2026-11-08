import { brzycki, epley, estimateOneRepMax, workingWeights } from './oneRepMax';

describe( 'one-rep max', () => {
	it( 'implements Epley and Brzycki', () => {
		expect( epley( 100, 5 ) ).toBeCloseTo( 116.67, 1 );
		expect( brzycki( 100, 5 ) ).toBeCloseTo( 112.5, 1 );
	} );

	it( 'averages both formulas and rounds to 0.5 kg', () => {
		// (116.67 + 112.5) / 2 = 114.58 -> 114.5
		expect( estimateOneRepMax( 100, 5 ) ).toBe( 114.5 );
	} );

	it( 'returns the weight itself for a single', () => {
		expect( estimateOneRepMax( 142.3, 1 ) ).toBe( 142.5 );
	} );

	it( 'returns 0 for invalid input', () => {
		expect( estimateOneRepMax( 0, 5 ) ).toBe( 0 );
		expect( estimateOneRepMax( 100, 0 ) ).toBe( 0 );
		expect( estimateOneRepMax( 100, 20 ) ).toBe( 0 );
		expect( estimateOneRepMax( 'abc', 5 ) ).toBe( 0 );
	} );

	it( 'gives working weights in 2.5 kg steps, heaviest first', () => {
		const rows = workingWeights( 100 );
		expect( rows[ 0 ] ).toEqual( { percent: 100, reps: 1, weight: 100 } );
		expect( rows.find( ( r ) => r.percent === 85 ).weight ).toBe( 85 );
		rows.forEach( ( r ) => expect( r.weight % 2.5 ).toBe( 0 ) );
	} );
} );

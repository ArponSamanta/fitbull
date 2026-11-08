import { calculateMacros, macroShares, normaliseInput } from './macros';

const base = {
	sex: 'male',
	age: 25,
	heightCm: 172,
	weightKg: 72,
	activity: 'moderate',
	goal: 'maintain',
};

describe( 'calculateMacros', () => {
	it( 'uses the Mifflin-St Jeor formula for BMR', () => {
		// 10*72 + 6.25*172 - 5*25 + 5 = 1675
		expect( calculateMacros( base ).bmr ).toBe( 1675 );
		// Female constant is -161: 1675 - 166 = 1509
		expect( calculateMacros( { ...base, sex: 'female' } ).bmr ).toBe(
			1509
		);
	} );

	it( 'applies the activity factor and rounds calories to 10', () => {
		const result = calculateMacros( base );
		expect( result.tdee ).toBe( Math.round( 1675 * 1.55 ) );
		expect( result.calories % 10 ).toBe( 0 );
		expect( result.calories ).toBe( 2600 );
	} );

	it( 'cuts calories for fat loss and adds them for a lean bulk', () => {
		const maintain = calculateMacros( base ).calories;
		expect(
			calculateMacros( { ...base, goal: 'lose' } ).calories
		).toBeLessThan( maintain );
		expect(
			calculateMacros( { ...base, goal: 'gain' } ).calories
		).toBeGreaterThan( maintain );
	} );

	it( 'sets protein per kg of body weight by goal', () => {
		expect( calculateMacros( base ).proteinG ).toBe(
			Math.round( 72 * 1.6 )
		);
		expect( calculateMacros( { ...base, goal: 'lose' } ).proteinG ).toBe(
			144
		);
	} );

	it( 'keeps macro calories close to the calorie target', () => {
		const r = calculateMacros( { ...base, goal: 'gain' } );
		const fromMacros = r.proteinG * 4 + r.carbsG * 4 + r.fatG * 9;
		expect( Math.abs( fromMacros - r.calories ) ).toBeLessThanOrEqual( 10 );
	} );
} );

describe( 'normaliseInput', () => {
	it( 'clamps values outside the supported range', () => {
		const n = normaliseInput( { ...base, age: 5, weightKg: 900 } );
		expect( n.age ).toBe( 16 );
		expect( n.weightKg ).toBe( 200 );
	} );

	it( 'falls back to safe defaults for unknown options', () => {
		const n = normaliseInput( {
			...base,
			sex: 'x',
			activity: 'extreme',
			goal: 'shred',
		} );
		expect( n ).toMatchObject( {
			sex: 'male',
			activity: 'moderate',
			goal: 'maintain',
		} );
	} );
} );

describe( 'macroShares', () => {
	it( 'returns zero shares for zero calories', () => {
		expect(
			macroShares( { calories: 0, proteinG: 0, carbsG: 0, fatG: 0 } )
		).toEqual( {
			protein: 0,
			carbs: 0,
			fat: 0,
		} );
	} );

	it( 'adds up to roughly 100%', () => {
		const s = macroShares( calculateMacros( base ) );
		expect( s.protein + s.carbs + s.fat ).toBeGreaterThanOrEqual( 99 );
		expect( s.protein + s.carbs + s.fat ).toBeLessThanOrEqual( 101 );
	} );
} );

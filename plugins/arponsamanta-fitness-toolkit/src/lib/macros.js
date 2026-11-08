/**
 * Calorie and macro targets (Mifflin-St Jeor).
 *
 * Mirrors includes/class-macro-calculator.php and reads the same
 * data/macro-config.json, so the block and the REST endpoint agree.
 */
import config from '../../data/macro-config.json';

export { config };

const clamp = ( value, [ min, max ] ) =>
	Math.min( max, Math.max( min, value ) );

/**
 * Keep inputs inside the supported ranges so the UI never shows nonsense.
 *
 * @param {Object} input Raw form values.
 * @return {Object} Sanitised values.
 */
export function normaliseInput( input ) {
	const { limits } = config;
	return {
		sex: input.sex === 'female' ? 'female' : 'male',
		age: clamp( Number( input.age ) || limits.age[ 0 ], limits.age ),
		heightCm: clamp(
			Number( input.heightCm ) || limits.heightCm[ 0 ],
			limits.heightCm
		),
		weightKg: clamp(
			Number( input.weightKg ) || limits.weightKg[ 0 ],
			limits.weightKg
		),
		activity: config.activity[ input.activity ]
			? input.activity
			: 'moderate',
		goal: config.goal[ input.goal ] ? input.goal : 'maintain',
	};
}

/**
 * @param {Object} raw Form values: sex, age, heightCm, weightKg, activity, goal.
 * @return {{bmr:number,tdee:number,calories:number,proteinG:number,carbsG:number,fatG:number}} Targets.
 */
export function calculateMacros( raw ) {
	const input = normaliseInput( raw );

	const bmr =
		10 * input.weightKg +
		6.25 * input.heightCm -
		5 * input.age +
		( input.sex === 'male' ? 5 : -161 );

	const tdee = bmr * config.activity[ input.activity ].factor;
	const goal = config.goal[ input.goal ];
	const calories =
		Math.round( ( tdee * ( 1 + goal.calorieChange ) ) / 10 ) * 10;

	const proteinG = Math.round( input.weightKg * goal.proteinPerKg );
	const fatG = Math.round( ( calories * config.fatShare ) / 9 );
	const carbsG = Math.max(
		0,
		Math.round( ( calories - proteinG * 4 - fatG * 9 ) / 4 )
	);

	return {
		bmr: Math.round( bmr ),
		tdee: Math.round( tdee ),
		calories,
		proteinG,
		carbsG,
		fatG,
	};
}

/**
 * Share of daily calories each macro supplies, as whole percentages.
 *
 * @param {Object} targets Result of calculateMacros().
 * @return {{protein:number,carbs:number,fat:number}} Percentages.
 */
export function macroShares( targets ) {
	const { calories, proteinG, carbsG, fatG } = targets;
	if ( ! calories ) {
		return { protein: 0, carbs: 0, fat: 0 };
	}
	return {
		protein: Math.round( ( proteinG * 4 * 100 ) / calories ),
		carbs: Math.round( ( carbsG * 4 * 100 ) / calories ),
		fat: Math.round( ( fatG * 9 * 100 ) / calories ),
	};
}

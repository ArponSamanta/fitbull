import { useEffect, useMemo, useState, useId } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import {
	calculateMacros,
	config,
	macroShares,
	normaliseInput,
} from '../lib/macros';

const STORAGE_KEY = 'fitbull:macro-inputs';

const DEFAULTS = {
	sex: 'male',
	age: 25,
	heightCm: 170,
	weightKg: 70,
	activity: 'moderate',
	goal: 'maintain',
};

function loadSaved() {
	try {
		const saved = JSON.parse( window.localStorage.getItem( STORAGE_KEY ) );
		return saved ? normaliseInput( { ...DEFAULTS, ...saved } ) : DEFAULTS;
	} catch ( e ) {
		return DEFAULTS;
	}
}

function save( values ) {
	try {
		window.localStorage.setItem( STORAGE_KEY, JSON.stringify( values ) );
	} catch ( e ) {
		// Storage can be unavailable (private mode); the calculator still works.
	}
}

function Segmented( { label, options, value, onChange } ) {
	return (
		<fieldset className="fb-calc__segmented">
			<legend className="fb-calc__label">{ label }</legend>
			<div
				className={ `fb-calc__segments fb-calc__segments--${ options.length }` }
				style={ { '--fb-count': options.length } }
			>
				{ options.map( ( option ) => (
					<button
						key={ option.value }
						type="button"
						className="fb-calc__segment"
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

function NumberField( { label, unit, value, range, onChange } ) {
	const id = useId();
	const [ draft, setDraft ] = useState( String( value ) );

	useEffect( () => setDraft( String( value ) ), [ value ] );

	const commit = ( next ) => {
		const number = Number( next );
		if ( ! Number.isNaN( number ) && next !== '' ) {
			onChange( Math.min( range[ 1 ], Math.max( range[ 0 ], number ) ) );
		} else {
			setDraft( String( value ) );
		}
	};

	return (
		<div className="fb-calc__number">
			<label className="fb-calc__label" htmlFor={ id }>
				{ label }
			</label>
			<div className="fb-calc__number-row">
				<button
					type="button"
					className="fb-calc__step"
					aria-label={ sprintf(
						/* translators: %s: field name */ __(
							'Decrease %s',
							'arponsamanta-fitness-toolkit'
						),
						label
					) }
					onClick={ () =>
						onChange( Math.max( range[ 0 ], value - 1 ) )
					}
				>
					−
				</button>
				<input
					id={ id }
					type="number"
					inputMode="numeric"
					min={ range[ 0 ] }
					max={ range[ 1 ] }
					value={ draft }
					onChange={ ( event ) => setDraft( event.target.value ) }
					onBlur={ ( event ) => commit( event.target.value ) }
					onKeyDown={ ( event ) =>
						event.key === 'Enter' && commit( event.target.value )
					}
				/>
				<span className="fb-calc__unit">{ unit }</span>
				<button
					type="button"
					className="fb-calc__step"
					aria-label={ sprintf(
						/* translators: %s: field name */ __(
							'Increase %s',
							'arponsamanta-fitness-toolkit'
						),
						label
					) }
					onClick={ () =>
						onChange( Math.min( range[ 1 ], value + 1 ) )
					}
				>
					+
				</button>
			</div>
		</div>
	);
}

function MacroBar( { label, grams, share, tone } ) {
	return (
		<div className="fb-calc__macro">
			<div className="fb-calc__macro-head">
				<span>{ label }</span>
				<span className="fb-calc__muted">
					{ sprintf(
						/* translators: %d: grams */ __(
							'%d g',
							'arponsamanta-fitness-toolkit'
						),
						grams
					) }
				</span>
			</div>
			<div
				className="fb-calc__track"
				role="img"
				aria-label={ sprintf(
					/* translators: 1: macro name, 2: percent of calories */
					__(
						'%1$s supplies %2$d%% of calories',
						'arponsamanta-fitness-toolkit'
					),
					label,
					share
				) }
			>
				<div
					className={ `fb-calc__fill fb-calc__fill--${ tone }` }
					style={ { width: `${ share }%` } }
				/>
			</div>
		</div>
	);
}

export default function MacroCalculator( {
	compact = false,
	mealPlanUrl = '',
} ) {
	const [ values, setValues ] = useState( DEFAULTS );

	// Read saved inputs after mount so server and first client render match.
	useEffect( () => setValues( loadSaved() ), [] );

	const update = ( key ) => ( next ) =>
		setValues( ( current ) => {
			const merged = normaliseInput( { ...current, [ key ]: next } );
			save( merged );
			return merged;
		} );

	const result = useMemo( () => calculateMacros( values ), [ values ] );
	const shares = macroShares( result );
	const option = ( group ) =>
		Object.entries( config[ group ] ).map( ( [ value, item ] ) => ( {
			value,
			label: item.label,
		} ) );

	return (
		<div className={ `fb-calc${ compact ? ' fb-calc--compact' : '' }` }>
			<div className="fb-calc__head">
				<h2 className="fb-calc__title">
					{ __(
						'Your daily targets',
						'arponsamanta-fitness-toolkit'
					) }
				</h2>
				<span className="fb-calc__muted">
					{ __( 'Macro calculator', 'arponsamanta-fitness-toolkit' ) }
				</span>
			</div>

			<Segmented
				label={ __( 'Sex', 'arponsamanta-fitness-toolkit' ) }
				value={ values.sex }
				onChange={ update( 'sex' ) }
				options={ [
					{
						value: 'male',
						label: __( 'Male', 'arponsamanta-fitness-toolkit' ),
					},
					{
						value: 'female',
						label: __( 'Female', 'arponsamanta-fitness-toolkit' ),
					},
				] }
			/>

			<div className="fb-calc__numbers">
				<NumberField
					label={ __( 'Age', 'arponsamanta-fitness-toolkit' ) }
					unit={ __( 'yrs', 'arponsamanta-fitness-toolkit' ) }
					value={ values.age }
					range={ config.limits.age }
					onChange={ update( 'age' ) }
				/>
				<NumberField
					label={ __( 'Height', 'arponsamanta-fitness-toolkit' ) }
					unit="cm"
					value={ values.heightCm }
					range={ config.limits.heightCm }
					onChange={ update( 'heightCm' ) }
				/>
				<NumberField
					label={ __( 'Weight', 'arponsamanta-fitness-toolkit' ) }
					unit="kg"
					value={ values.weightKg }
					range={ config.limits.weightKg }
					onChange={ update( 'weightKg' ) }
				/>
			</div>

			<Segmented
				label={ __( 'Activity', 'arponsamanta-fitness-toolkit' ) }
				value={ values.activity }
				onChange={ update( 'activity' ) }
				options={ option( 'activity' ) }
			/>
			<Segmented
				label={ __( 'Goal', 'arponsamanta-fitness-toolkit' ) }
				value={ values.goal }
				onChange={ update( 'goal' ) }
				options={ option( 'goal' ) }
			/>

			<div className="fb-calc__result" aria-live="polite">
				<p className="fb-calc__kcal">
					<span className="fb-calc__kcal-number">
						{ result.calories.toLocaleString( 'en-IN' ) }
					</span>
					<span className="fb-calc__muted">
						{ __( 'kcal per day', 'arponsamanta-fitness-toolkit' ) }
					</span>
				</p>
				<MacroBar
					label={ __( 'Protein', 'arponsamanta-fitness-toolkit' ) }
					grams={ result.proteinG }
					share={ shares.protein }
					tone="protein"
				/>
				<MacroBar
					label={ __( 'Carbs', 'arponsamanta-fitness-toolkit' ) }
					grams={ result.carbsG }
					share={ shares.carbs }
					tone="carbs"
				/>
				<MacroBar
					label={ __( 'Fat', 'arponsamanta-fitness-toolkit' ) }
					grams={ result.fatG }
					share={ shares.fat }
					tone="fat"
				/>
				{ ! compact && (
					<p className="fb-calc__muted fb-calc__note">
						{ sprintf(
							/* translators: 1: BMR kcal, 2: maintenance kcal */
							__(
								'Resting burn %1$s kcal · maintenance %2$s kcal',
								'arponsamanta-fitness-toolkit'
							),
							result.bmr.toLocaleString( 'en-IN' ),
							result.tdee.toLocaleString( 'en-IN' )
						) }
					</p>
				) }
			</div>

			{ mealPlanUrl && (
				<a className="fb-calc__cta" href={ mealPlanUrl }>
					{ __(
						'Get an Indian meal plan for this',
						'arponsamanta-fitness-toolkit'
					) }
				</a>
			) }
			<p className="fb-calc__disclaimer">
				{ __(
					'Estimates only, not medical advice.',
					'arponsamanta-fitness-toolkit'
				) }
			</p>
		</div>
	);
}

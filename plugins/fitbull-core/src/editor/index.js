/**
 * Sidebar panels for the structured fields of each Fitbull content type.
 * Values are post meta registered in includes/class-meta.php.
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { useState } from '@wordpress/element';
import {
	Button,
	Flex,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

const PATTERN_OPTIONS = [
	{ value: 'squat', label: __( 'Squat', 'fitbull-core' ) },
	{ value: 'hinge', label: __( 'Hip hinge', 'fitbull-core' ) },
	{ value: 'lunge', label: __( 'Lunge (single leg)', 'fitbull-core' ) },
	{ value: 'push_h', label: __( 'Horizontal push (chest)', 'fitbull-core' ) },
	{
		value: 'push_v',
		label: __( 'Vertical push (overhead)', 'fitbull-core' ),
	},
	{ value: 'pull_h', label: __( 'Horizontal pull (row)', 'fitbull-core' ) },
	{
		value: 'pull_v',
		label: __( 'Vertical pull (pulldown)', 'fitbull-core' ),
	},
	{ value: 'core', label: __( 'Core', 'fitbull-core' ) },
	{ value: 'arms', label: __( 'Arms', 'fitbull-core' ) },
];

const controlProps = {
	__nextHasNoMarginBottom: true,
	__next40pxDefaultSize: true,
};

function useMeta( postType ) {
	const [ meta = {}, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta'
	);
	const set = ( key ) => ( value ) => setMeta( { ...meta, [ key ]: value } );
	return [ meta, set ];
}

// Ordered list of two-field rows, e.g. steps (title, text) or mistakes (title, fix).
function RowList( { rows = [], onChange, fields, addLabel, itemLabel } ) {
	const updateRow = ( index, key, value ) =>
		onChange(
			rows.map( ( row, i ) =>
				i === index ? { ...row, [ key ]: value } : row
			)
		);
	const move = ( index, delta ) => {
		const next = [ ...rows ];
		const [ item ] = next.splice( index, 1 );
		next.splice( index + delta, 0, item );
		onChange( next );
	};

	return (
		<div className="fitbull-rowlist">
			{ rows.map( ( row, index ) => (
				<fieldset
					key={ index }
					style={ {
						border: '1px solid #ddd',
						borderRadius: 4,
						padding: 8,
						marginBottom: 8,
					} }
				>
					<legend style={ { fontWeight: 600, padding: '0 4px' } }>
						{ sprintf( itemLabel, index + 1 ) }
					</legend>
					<TextControl
						{ ...controlProps }
						label={ fields[ 0 ].label }
						value={ row[ fields[ 0 ].key ] || '' }
						onChange={ ( value ) =>
							updateRow( index, fields[ 0 ].key, value )
						}
					/>
					<TextareaControl
						__nextHasNoMarginBottom
						label={ fields[ 1 ].label }
						rows={ 2 }
						value={ row[ fields[ 1 ].key ] || '' }
						onChange={ ( value ) =>
							updateRow( index, fields[ 1 ].key, value )
						}
					/>
					<Flex
						justify="flex-end"
						gap={ 1 }
						style={ { marginTop: 6 } }
					>
						<Button
							size="small"
							icon="arrow-up-alt2"
							label={ __( 'Move up', 'fitbull-core' ) }
							disabled={ index === 0 }
							onClick={ () => move( index, -1 ) }
						/>
						<Button
							size="small"
							icon="arrow-down-alt2"
							label={ __( 'Move down', 'fitbull-core' ) }
							disabled={ index === rows.length - 1 }
							onClick={ () => move( index, 1 ) }
						/>
						<Button
							size="small"
							isDestructive
							variant="tertiary"
							onClick={ () =>
								onChange(
									rows.filter( ( _, i ) => i !== index )
								)
							}
						>
							{ __( 'Remove', 'fitbull-core' ) }
						</Button>
					</Flex>
				</fieldset>
			) ) }
			<Button
				variant="secondary"
				onClick={ () =>
					onChange( [
						...rows,
						{ [ fields[ 0 ].key ]: '', [ fields[ 1 ].key ]: '' },
					] )
				}
			>
				{ addLabel }
			</Button>
		</div>
	);
}

function ExercisePanels() {
	const [ meta, set ] = useMeta( 'fb_exercise' );
	return (
		<>
			<PluginDocumentSettingPanel
				name="fitbull-exercise"
				title={ __( 'Exercise', 'fitbull-core' ) }
			>
				<Flex direction="column" gap={ 4 }>
					<SelectControl
						{ ...controlProps }
						label={ __( 'Level', 'fitbull-core' ) }
						value={ meta.fb_difficulty }
						options={ [
							{
								value: 'beginner',
								label: __( 'Beginner', 'fitbull-core' ),
							},
							{
								value: 'intermediate',
								label: __( 'Intermediate', 'fitbull-core' ),
							},
							{
								value: 'advanced',
								label: __( 'Advanced', 'fitbull-core' ),
							},
						] }
						onChange={ set( 'fb_difficulty' ) }
					/>
					<SelectControl
						{ ...controlProps }
						label={ __( 'Movement pattern', 'fitbull-core' ) }
						help={ __(
							'The workout plan generator fills each session slot by pattern.',
							'fitbull-core'
						) }
						value={ meta.fb_pattern }
						options={ PATTERN_OPTIONS }
						onChange={ set( 'fb_pattern' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Timed hold (seconds, not reps)',
							'fitbull-core'
						) }
						checked={ !! meta.fb_timed }
						onChange={ set( 'fb_timed' ) }
					/>
					<TextControl
						{ ...controlProps }
						type="url"
						label={ __( 'Form video URL', 'fitbull-core' ) }
						help={ __( 'YouTube or Vimeo link.', 'fitbull-core' ) }
						value={ meta.fb_video_url || '' }
						onChange={ set( 'fb_video_url' ) }
					/>
					<TextControl
						{ ...controlProps }
						label={ __( 'Secondary muscles', 'fitbull-core' ) }
						help={ __(
							'Comma separated. Primary muscles are set in the Muscles panel.',
							'fitbull-core'
						) }
						value={ ( meta.fb_secondary_muscles || [] ).join(
							', '
						) }
						onChange={ ( value ) =>
							set( 'fb_secondary_muscles' )(
								value
									.split( ',' )
									.map( ( item ) => item.trim() )
									.filter( Boolean )
							)
						}
					/>
				</Flex>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-steps"
				title={ __( 'Steps', 'fitbull-core' ) }
			>
				<RowList
					rows={ meta.fb_steps }
					onChange={ set( 'fb_steps' ) }
					/* translators: %d: step number */
					itemLabel={ __( 'Step %d', 'fitbull-core' ) }
					addLabel={ __( 'Add step', 'fitbull-core' ) }
					fields={ [
						{ key: 'title', label: __( 'Cue', 'fitbull-core' ) },
						{ key: 'text', label: __( 'Detail', 'fitbull-core' ) },
					] }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-mistakes"
				title={ __( 'Common mistakes', 'fitbull-core' ) }
			>
				<RowList
					rows={ meta.fb_mistakes }
					onChange={ set( 'fb_mistakes' ) }
					/* translators: %d: mistake number */
					itemLabel={ __( 'Mistake %d', 'fitbull-core' ) }
					addLabel={ __( 'Add mistake', 'fitbull-core' ) }
					fields={ [
						{
							key: 'title',
							label: __( 'Mistake', 'fitbull-core' ),
						},
						{
							key: 'fix',
							label: __( 'How to fix it', 'fitbull-core' ),
						},
					] }
				/>
			</PluginDocumentSettingPanel>
		</>
	);
}

function GearPanel() {
	const [ meta, set ] = useMeta( 'fb_equipment' );
	return (
		<PluginDocumentSettingPanel
			name="fitbull-gear"
			title={ __( 'Gear details', 'fitbull-core' ) }
		>
			<Flex direction="column" gap={ 4 }>
				<TextControl
					type="number"
					{ ...controlProps }
					label={ __( 'Price (₹)', 'fitbull-core' ) }
					min={ 0 }
					value={ meta.fb_price_inr || '' }
					onChange={ ( value ) =>
						set( 'fb_price_inr' )( parseInt( value, 10 ) || 0 )
					}
				/>
				<TextControl
					{ ...controlProps }
					type="url"
					label={ __( 'Where to buy (URL)', 'fitbull-core' ) }
					value={ meta.fb_buy_url || '' }
					onChange={ set( 'fb_buy_url' ) }
				/>
				<SelectControl
					{ ...controlProps }
					label={ __( 'Budget tier', 'fitbull-core' ) }
					value={ meta.fb_budget_tier }
					options={ [
						{
							value: 'starter',
							label: __( 'Starter', 'fitbull-core' ),
						},
						{
							value: 'serious',
							label: __( 'Serious', 'fitbull-core' ),
						},
						{
							value: 'full',
							label: __( 'Full setup', 'fitbull-core' ),
						},
					] }
					onChange={ set( 'fb_budget_tier' ) }
				/>
				<TextControl
					{ ...controlProps }
					type="number"
					min={ 1 }
					label={ __( 'Buying order', 'fitbull-core' ) }
					help={ __(
						'Lower numbers are recommended first within a tier.',
						'fitbull-core'
					) }
					value={ meta.fb_priority ?? 10 }
					onChange={ ( value ) =>
						set( 'fb_priority' )( parseInt( value, 10 ) || 1 )
					}
				/>
			</Flex>
		</PluginDocumentSettingPanel>
	);
}

function SupplementPanel() {
	const [ meta, set ] = useMeta( 'fb_supplement' );
	return (
		<PluginDocumentSettingPanel
			name="fitbull-supplement"
			title={ __( 'Evidence', 'fitbull-core' ) }
		>
			<Flex direction="column" gap={ 4 }>
				<SelectControl
					{ ...controlProps }
					label={ __( 'Strength of evidence', 'fitbull-core' ) }
					value={ meta.fb_evidence }
					options={ [
						{
							value: 'strong',
							label: __( 'Strong', 'fitbull-core' ),
						},
						{
							value: 'moderate',
							label: __( 'Moderate', 'fitbull-core' ),
						},
						{ value: 'weak', label: __( 'Weak', 'fitbull-core' ) },
						{
							value: 'none',
							label: __( 'None / not rated', 'fitbull-core' ),
						},
					] }
					onChange={ set( 'fb_evidence' ) }
				/>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'Verdict', 'fitbull-core' ) }
					help={ __(
						'One or two plain sentences: worth it or not, and for whom.',
						'fitbull-core'
					) }
					value={ meta.fb_verdict || '' }
					onChange={ set( 'fb_verdict' ) }
				/>
			</Flex>
		</PluginDocumentSettingPanel>
	);
}

// Keeps a text draft so blank lines can be typed; saves non-empty lines.
function MethodField( { value, onChange } ) {
	const [ draft, setDraft ] = useState( value.join( '\n' ) );
	return (
		<TextareaControl
			__nextHasNoMarginBottom
			label={ __( 'Steps, one per line', 'fitbull-core' ) }
			rows={ 8 }
			value={ draft }
			onChange={ ( text ) => {
				setDraft( text );
				onChange(
					text
						.split( '\n' )
						.map( ( line ) => line.trim() )
						.filter( Boolean )
				);
			} }
		/>
	);
}

function RecipePanels() {
	const [ meta, set ] = useMeta( 'fb_recipe' );
	const number = ( key ) => ( value ) =>
		set( key )( Math.max( 0, parseInt( value, 10 ) || 0 ) );
	const fields = [
		[ 'fb_calories', __( 'Calories (kcal)', 'fitbull-core' ) ],
		[ 'fb_protein_g', __( 'Protein (g)', 'fitbull-core' ) ],
		[ 'fb_carbs_g', __( 'Carbs (g)', 'fitbull-core' ) ],
		[ 'fb_fat_g', __( 'Fat (g)', 'fitbull-core' ) ],
		[ 'fb_prep_minutes', __( 'Time (minutes)', 'fitbull-core' ) ],
	];
	const fromMacros =
		( meta.fb_protein_g || 0 ) * 4 +
		( meta.fb_carbs_g || 0 ) * 4 +
		( meta.fb_fat_g || 0 ) * 9;
	const off =
		meta.fb_calories > 0 &&
		Math.abs( fromMacros - meta.fb_calories ) > meta.fb_calories * 0.1;

	return (
		<>
			<PluginDocumentSettingPanel
				name="fitbull-nutrition"
				title={ __( 'Nutrition per serving', 'fitbull-core' ) }
			>
				<Flex direction="column" gap={ 3 }>
					{ fields.map( ( [ key, label ] ) => (
						<TextControl
							key={ key }
							{ ...controlProps }
							type="number"
							min={ 0 }
							label={ label }
							value={ meta[ key ] ?? 0 }
							onChange={ number( key ) }
						/>
					) ) }
					<p
						style={ {
							margin: 0,
							color: off ? '#b32d2e' : '#757575',
						} }
					>
						{ sprintf(
							/* translators: %d: kcal calculated from macros */
							__( 'Macros add up to %d kcal.', 'fitbull-core' ),
							fromMacros
						) }
						{ off &&
							' ' +
								__(
									'That is more than 10% away from the calories field.',
									'fitbull-core'
								) }
					</p>
				</Flex>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-ingredients"
				title={ __( 'Ingredients', 'fitbull-core' ) }
			>
				<RowList
					rows={ meta.fb_ingredients }
					onChange={ set( 'fb_ingredients' ) }
					/* translators: %d: ingredient number */
					itemLabel={ __( 'Ingredient %d', 'fitbull-core' ) }
					addLabel={ __( 'Add ingredient', 'fitbull-core' ) }
					fields={ [
						{
							key: 'item',
							label: __( 'Ingredient', 'fitbull-core' ),
						},
						{ key: 'qty', label: __( 'Amount', 'fitbull-core' ) },
					] }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-method"
				title={ __( 'Method', 'fitbull-core' ) }
			>
				<MethodField
					value={ meta.fb_method || [] }
					onChange={ set( 'fb_method' ) }
				/>
			</PluginDocumentSettingPanel>
		</>
	);
}

function FitbullPanels() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
	if ( postType === 'fb_exercise' ) {
		return <ExercisePanels />;
	}
	if ( postType === 'fb_equipment' ) {
		return <GearPanel />;
	}
	if ( postType === 'fb_supplement' ) {
		return <SupplementPanel />;
	}
	if ( postType === 'fb_recipe' ) {
		return <RecipePanels />;
	}
	return null;
}

registerPlugin( 'fitbull-core', { render: FitbullPanels } );

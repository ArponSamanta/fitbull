/**
 * Sidebar panels for the structured fields of each fitness content type.
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
	{ value: 'squat', label: __( 'Squat', 'arponsamanta-fitness-toolkit' ) },
	{
		value: 'hinge',
		label: __( 'Hip hinge', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'lunge',
		label: __( 'Lunge (single leg)', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'push_h',
		label: __( 'Horizontal push (chest)', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'push_v',
		label: __( 'Vertical push (overhead)', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'pull_h',
		label: __( 'Horizontal pull (row)', 'arponsamanta-fitness-toolkit' ),
	},
	{
		value: 'pull_v',
		label: __( 'Vertical pull (pulldown)', 'arponsamanta-fitness-toolkit' ),
	},
	{ value: 'core', label: __( 'Core', 'arponsamanta-fitness-toolkit' ) },
	{ value: 'arms', label: __( 'Arms', 'arponsamanta-fitness-toolkit' ) },
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
							label={ __(
								'Move up',
								'arponsamanta-fitness-toolkit'
							) }
							disabled={ index === 0 }
							onClick={ () => move( index, -1 ) }
						/>
						<Button
							size="small"
							icon="arrow-down-alt2"
							label={ __(
								'Move down',
								'arponsamanta-fitness-toolkit'
							) }
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
							{ __( 'Remove', 'arponsamanta-fitness-toolkit' ) }
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
				title={ __( 'Exercise', 'arponsamanta-fitness-toolkit' ) }
			>
				<Flex direction="column" gap={ 4 }>
					<SelectControl
						{ ...controlProps }
						label={ __( 'Level', 'arponsamanta-fitness-toolkit' ) }
						value={ meta.fb_difficulty }
						options={ [
							{
								value: 'beginner',
								label: __(
									'Beginner',
									'arponsamanta-fitness-toolkit'
								),
							},
							{
								value: 'intermediate',
								label: __(
									'Intermediate',
									'arponsamanta-fitness-toolkit'
								),
							},
							{
								value: 'advanced',
								label: __(
									'Advanced',
									'arponsamanta-fitness-toolkit'
								),
							},
						] }
						onChange={ set( 'fb_difficulty' ) }
					/>
					<SelectControl
						{ ...controlProps }
						label={ __(
							'Movement pattern',
							'arponsamanta-fitness-toolkit'
						) }
						help={ __(
							'The workout plan generator fills each session slot by pattern.',
							'arponsamanta-fitness-toolkit'
						) }
						value={ meta.fb_pattern }
						options={ PATTERN_OPTIONS }
						onChange={ set( 'fb_pattern' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Timed hold (seconds, not reps)',
							'arponsamanta-fitness-toolkit'
						) }
						checked={ !! meta.fb_timed }
						onChange={ set( 'fb_timed' ) }
					/>
					<TextControl
						{ ...controlProps }
						type="url"
						label={ __(
							'Form video URL',
							'arponsamanta-fitness-toolkit'
						) }
						help={ __(
							'YouTube or Vimeo link.',
							'arponsamanta-fitness-toolkit'
						) }
						value={ meta.fb_video_url || '' }
						onChange={ set( 'fb_video_url' ) }
					/>
					<TextControl
						{ ...controlProps }
						label={ __(
							'Secondary muscles',
							'arponsamanta-fitness-toolkit'
						) }
						help={ __(
							'Comma separated. Primary muscles are set in the Muscles panel.',
							'arponsamanta-fitness-toolkit'
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
				title={ __( 'Steps', 'arponsamanta-fitness-toolkit' ) }
			>
				<RowList
					rows={ meta.fb_steps }
					onChange={ set( 'fb_steps' ) }
					/* translators: %d: step number */
					itemLabel={ __(
						'Step %d',
						'arponsamanta-fitness-toolkit'
					) }
					addLabel={ __(
						'Add step',
						'arponsamanta-fitness-toolkit'
					) }
					fields={ [
						{
							key: 'title',
							label: __( 'Cue', 'arponsamanta-fitness-toolkit' ),
						},
						{
							key: 'text',
							label: __(
								'Detail',
								'arponsamanta-fitness-toolkit'
							),
						},
					] }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-mistakes"
				title={ __(
					'Common mistakes',
					'arponsamanta-fitness-toolkit'
				) }
			>
				<RowList
					rows={ meta.fb_mistakes }
					onChange={ set( 'fb_mistakes' ) }
					/* translators: %d: mistake number */
					itemLabel={ __(
						'Mistake %d',
						'arponsamanta-fitness-toolkit'
					) }
					addLabel={ __(
						'Add mistake',
						'arponsamanta-fitness-toolkit'
					) }
					fields={ [
						{
							key: 'title',
							label: __(
								'Mistake',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							key: 'fix',
							label: __(
								'How to fix it',
								'arponsamanta-fitness-toolkit'
							),
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
			title={ __( 'Gear details', 'arponsamanta-fitness-toolkit' ) }
		>
			<Flex direction="column" gap={ 4 }>
				<TextControl
					type="number"
					{ ...controlProps }
					label={ __( 'Price (₹)', 'arponsamanta-fitness-toolkit' ) }
					min={ 0 }
					value={ meta.fb_price_inr || '' }
					onChange={ ( value ) =>
						set( 'fb_price_inr' )( parseInt( value, 10 ) || 0 )
					}
				/>
				<TextControl
					{ ...controlProps }
					type="url"
					label={ __(
						'Where to buy (URL)',
						'arponsamanta-fitness-toolkit'
					) }
					value={ meta.fb_buy_url || '' }
					onChange={ set( 'fb_buy_url' ) }
				/>
				<SelectControl
					{ ...controlProps }
					label={ __(
						'Budget tier',
						'arponsamanta-fitness-toolkit'
					) }
					value={ meta.fb_budget_tier }
					options={ [
						{
							value: 'starter',
							label: __(
								'Starter',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							value: 'serious',
							label: __(
								'Serious',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							value: 'full',
							label: __(
								'Full setup',
								'arponsamanta-fitness-toolkit'
							),
						},
					] }
					onChange={ set( 'fb_budget_tier' ) }
				/>
				<TextControl
					{ ...controlProps }
					type="number"
					min={ 1 }
					label={ __(
						'Buying order',
						'arponsamanta-fitness-toolkit'
					) }
					help={ __(
						'Lower numbers are recommended first within a tier.',
						'arponsamanta-fitness-toolkit'
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
			title={ __( 'Evidence', 'arponsamanta-fitness-toolkit' ) }
		>
			<Flex direction="column" gap={ 4 }>
				<SelectControl
					{ ...controlProps }
					label={ __(
						'Strength of evidence',
						'arponsamanta-fitness-toolkit'
					) }
					value={ meta.fb_evidence }
					options={ [
						{
							value: 'strong',
							label: __(
								'Strong',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							value: 'moderate',
							label: __(
								'Moderate',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							value: 'weak',
							label: __( 'Weak', 'arponsamanta-fitness-toolkit' ),
						},
						{
							value: 'none',
							label: __(
								'None / not rated',
								'arponsamanta-fitness-toolkit'
							),
						},
					] }
					onChange={ set( 'fb_evidence' ) }
				/>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'Verdict', 'arponsamanta-fitness-toolkit' ) }
					help={ __(
						'One or two plain sentences: worth it or not, and for whom.',
						'arponsamanta-fitness-toolkit'
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
			label={ __(
				'Steps, one per line',
				'arponsamanta-fitness-toolkit'
			) }
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
		[
			'fb_calories',
			__( 'Calories (kcal)', 'arponsamanta-fitness-toolkit' ),
		],
		[ 'fb_protein_g', __( 'Protein (g)', 'arponsamanta-fitness-toolkit' ) ],
		[ 'fb_carbs_g', __( 'Carbs (g)', 'arponsamanta-fitness-toolkit' ) ],
		[ 'fb_fat_g', __( 'Fat (g)', 'arponsamanta-fitness-toolkit' ) ],
		[
			'fb_prep_minutes',
			__( 'Time (minutes)', 'arponsamanta-fitness-toolkit' ),
		],
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
				title={ __(
					'Nutrition per serving',
					'arponsamanta-fitness-toolkit'
				) }
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
							__(
								'Macros add up to %d kcal.',
								'arponsamanta-fitness-toolkit'
							),
							fromMacros
						) }
						{ off &&
							' ' +
								__(
									'That is more than 10% away from the calories field.',
									'arponsamanta-fitness-toolkit'
								) }
					</p>
				</Flex>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-ingredients"
				title={ __( 'Ingredients', 'arponsamanta-fitness-toolkit' ) }
			>
				<RowList
					rows={ meta.fb_ingredients }
					onChange={ set( 'fb_ingredients' ) }
					/* translators: %d: ingredient number */
					itemLabel={ __(
						'Ingredient %d',
						'arponsamanta-fitness-toolkit'
					) }
					addLabel={ __(
						'Add ingredient',
						'arponsamanta-fitness-toolkit'
					) }
					fields={ [
						{
							key: 'item',
							label: __(
								'Ingredient',
								'arponsamanta-fitness-toolkit'
							),
						},
						{
							key: 'qty',
							label: __(
								'Amount',
								'arponsamanta-fitness-toolkit'
							),
						},
					] }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="fitbull-method"
				title={ __( 'Method', 'arponsamanta-fitness-toolkit' ) }
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

registerPlugin( 'arponsamanta-fitness-toolkit', { render: FitbullPanels } );

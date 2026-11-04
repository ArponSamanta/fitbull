import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import MacroCalculator from '../../components/MacroCalculator';
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	const { compact, mealPlanUrl } = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'fitbull-core' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Compact layout', 'fitbull-core' ) }
						help={ __(
							'Hides the BMR and maintenance line, for tight spaces like a hero.',
							'fitbull-core'
						) }
						checked={ compact }
						onChange={ ( value ) =>
							setAttributes( { compact: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Meal plan link', 'fitbull-core' ) }
						help={ __(
							'Optional. Shows a button under the results.',
							'fitbull-core'
						) }
						type="url"
						value={ mealPlanUrl }
						onChange={ ( value ) =>
							setAttributes( { mealPlanUrl: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<MacroCalculator
					compact={ compact }
					mealPlanUrl={ mealPlanUrl }
				/>
			</div>
		</>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

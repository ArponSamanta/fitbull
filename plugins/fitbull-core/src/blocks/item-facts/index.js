import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Disabled, PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes, context: { postId } } ) {
		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'fitbull-core' ) }>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Layout', 'fitbull-core' ) }
							value={ attributes.layout }
							options={ [
								{
									value: 'card',
									label: __(
										'Compact (for lists)',
										'fitbull-core'
									),
								},
								{
									value: 'full',
									label: __(
										'Full (for single pages)',
										'fitbull-core'
									),
								},
							] }
							onChange={ ( layout ) =>
								setAttributes( { layout } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<div { ...useBlockProps() }>
					<Disabled>
						<ServerSideRender
							block={ metadata.name }
							attributes={ attributes }
							urlQueryArgs={ { post_id: postId } }
						/>
					</Disabled>
				</div>
			</>
		);
	},
	save: () => null,
} );

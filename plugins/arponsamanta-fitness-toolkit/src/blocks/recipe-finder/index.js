import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Disabled, PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes } ) {
		return (
			<>
				<InspectorControls>
					<PanelBody
						title={ __(
							'Settings',
							'arponsamanta-fitness-toolkit'
						) }
					>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __(
								'Recipes per page',
								'arponsamanta-fitness-toolkit'
							) }
							min={ 3 }
							max={ 48 }
							value={ attributes.perPage }
							onChange={ ( perPage ) =>
								setAttributes( { perPage } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<div { ...useBlockProps() }>
					<Disabled>
						<ServerSideRender
							block={ metadata.name }
							attributes={ attributes }
						/>
					</Disabled>
				</div>
			</>
		);
	},
	save: () => null,
} );

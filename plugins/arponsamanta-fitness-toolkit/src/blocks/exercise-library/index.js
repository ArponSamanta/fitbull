import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	RangeControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	const { perPage, showFilters } = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'arponsamanta-fitness-toolkit' ) }>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Exercises per page', 'arponsamanta-fitness-toolkit' ) }
						min={ 3 }
						max={ 48 }
						value={ perPage }
						onChange={ ( value ) =>
							setAttributes( { perPage: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show filters', 'arponsamanta-fitness-toolkit' ) }
						checked={ showFilters }
						onChange={ ( value ) =>
							setAttributes( { showFilters: value } )
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
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

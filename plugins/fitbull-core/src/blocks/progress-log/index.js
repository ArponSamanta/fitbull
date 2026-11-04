import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit() {
		return (
			<div { ...useBlockProps() }>
				<Placeholder
					icon="chart-line"
					label={ __( 'Progress log', 'fitbull-core' ) }
					instructions={ __(
						'Signed-in visitors log their bodyweight and lifts here. Logged-out visitors see a prompt to log in.',
						'fitbull-core'
					) }
				/>
			</div>
		);
	},
	save: () => null,
} );

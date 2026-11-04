import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import OneRepMax from '../../components/OneRepMax';
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit() {
		return (
			<div { ...useBlockProps() }>
				<OneRepMax />
			</div>
		);
	},
	save: () => null,
} );

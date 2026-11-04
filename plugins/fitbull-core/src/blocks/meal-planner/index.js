import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled } from '@wordpress/components';

import MealPlanner from '../../components/MealPlanner';
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit() {
		return (
			<div { ...useBlockProps() }>
				<Disabled>
					<MealPlanner />
				</Disabled>
			</div>
		);
	},
	save: () => null,
} );

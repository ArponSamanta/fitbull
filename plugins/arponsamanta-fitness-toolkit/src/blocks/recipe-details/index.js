import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { context: { postId, postType } } ) {
		const blockProps = useBlockProps();
		if ( ! postId || postType !== 'fb_recipe' ) {
			return (
				<div { ...blockProps }>
					<Placeholder
						icon="list-view"
						label={ __(
							'Recipe details',
							'arponsamanta-fitness-toolkit'
						) }
						instructions={ __(
							'Shows the nutrition, ingredients and method of the recipe being viewed. Fill them in from the Recipe panels in the sidebar.',
							'arponsamanta-fitness-toolkit'
						) }
					/>
				</div>
			);
		}
		return (
			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						urlQueryArgs={ { post_id: postId } }
					/>
				</Disabled>
			</div>
		);
	},
	save: () => null,
} );

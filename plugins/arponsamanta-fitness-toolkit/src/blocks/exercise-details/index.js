import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './style.scss';

function Edit( { context: { postId, postType } } ) {
	const blockProps = useBlockProps();

	if ( ! postId || postType !== 'fb_exercise' ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="list-view"
					label={ __(
						'Exercise details',
						'arponsamanta-fitness-toolkit'
					) }
					instructions={ __(
						'Shows the steps, mistakes and muscles of the exercise being viewed. Fill them in from the Exercise panel in the sidebar.',
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
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

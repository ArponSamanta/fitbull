import { createRoot } from '@wordpress/element';

import ExerciseLibrary from '../../components/ExerciseLibrary';

function mount() {
	document.querySelectorAll( '[data-fitbull-library]' ).forEach( ( node ) => {
		const props = JSON.parse( node.dataset.fitbullLibrary || '{}' );
		createRoot( node ).render( <ExerciseLibrary { ...props } /> );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount );
} else {
	mount();
}

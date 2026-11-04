import { createRoot } from '@wordpress/element';

import MacroCalculator from '../../components/MacroCalculator';

function mount() {
	document.querySelectorAll( '[data-fitbull-macro]' ).forEach( ( node ) => {
		const props = JSON.parse( node.dataset.fitbullMacro || '{}' );
		createRoot( node ).render( <MacroCalculator { ...props } /> );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount );
} else {
	mount();
}

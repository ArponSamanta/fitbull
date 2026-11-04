import { createRoot } from '@wordpress/element';

/**
 * Mount a React component on every element carrying `data-{attr}`, passing
 * the attribute's JSON as props. render.php prints that element with
 * server HTML inside, which React replaces once the script runs.
 *
 * @param {string}   attr      Data attribute name, e.g. "fitbull-plan".
 * @param {Function} Component React component.
 */
export default function mount( attr, Component ) {
	const run = () => {
		document.querySelectorAll( `[data-${ attr }]` ).forEach( ( node ) => {
			let props = {};
			try {
				props = JSON.parse(
					node.getAttribute( `data-${ attr }` ) || '{}'
				);
			} catch ( e ) {
				props = {};
			}
			createRoot( node ).render( <Component { ...props } /> );
		} );
	};
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', run );
	} else {
		run();
	}
}

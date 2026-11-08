import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

const TIERS = [
	{
		value: 'starter',
		label: __( 'Starter', 'arponsamanta-fitness-toolkit' ),
		hint: __(
			'Train at home with almost no space or spend.',
			'arponsamanta-fitness-toolkit'
		),
	},
	{
		value: 'serious',
		label: __( 'Serious', 'arponsamanta-fitness-toolkit' ),
		hint: __(
			'Enough to build muscle properly in a spare corner.',
			'arponsamanta-fitness-toolkit'
		),
	},
	{
		value: 'full',
		label: __( 'Full setup', 'arponsamanta-fitness-toolkit' ),
		hint: __(
			'A complete barbell gym for a spare room or garage.',
			'arponsamanta-fitness-toolkit'
		),
	},
];

export default function GearKit( { items = [] } ) {
	const [ tier, setTier ] = useState( 'serious' );
	const upTo = TIERS.findIndex( ( t ) => t.value === tier );
	const allowed = TIERS.slice( 0, upTo + 1 ).map( ( t ) => t.value );

	const kit = items
		.filter( ( item ) => allowed.includes( item.tier ) )
		.sort(
			( a, b ) =>
				allowed.indexOf( a.tier ) - allowed.indexOf( b.tier ) ||
				a.priority - b.priority
		);
	const priced = kit.filter( ( item ) => item.price > 0 );
	const total = priced.reduce( ( sum, item ) => sum + item.price, 0 );

	return (
		<div className="fb-ui fb-kit">
			<div className="fb-panel">
				<fieldset className="fb-field">
					<legend>
						{ __( 'How far do you want to go?', 'arponsamanta-fitness-toolkit' ) }
					</legend>
					<div
						className={ `fb-seg fb-seg--${ TIERS.length }` }
						style={ { '--fb-count': TIERS.length } }
					>
						{ TIERS.map( ( t ) => (
							<button
								key={ t.value }
								type="button"
								aria-pressed={ tier === t.value }
								onClick={ () => setTier( t.value ) }
							>
								{ t.label }
							</button>
						) ) }
					</div>
				</fieldset>
				<p className="fb-muted">{ TIERS[ upTo ].hint }</p>
			</div>

			<ol className="fb-kit__list" aria-live="polite">
				{ kit.map( ( item, i ) => (
					<li key={ item.id } className="fb-panel fb-kit__item">
						<span className="fb-kit__step">{ i + 1 }</span>
						<div className="fb-kit__body">
							<h3 className="fb-kit__title">
								<a href={ item.link }>{ item.title }</a>
							</h3>
							<p className="fb-muted">{ item.excerpt }</p>
						</div>
						<div className="fb-kit__side">
							{ item.price > 0 && (
								<span className="fb-num">
									{ sprintf(
										/* translators: %s: price in rupees */
										__( 'About ₹%s', 'arponsamanta-fitness-toolkit' ),
										item.price.toLocaleString( 'en-IN' )
									) }
								</span>
							) }
							{ item.buy_url && (
								<a
									className="fb-btn fb-btn--ghost fb-btn--small"
									href={ item.buy_url }
									rel="sponsored nofollow noopener noreferrer"
									target="_blank"
								>
									{ __( 'Check price', 'arponsamanta-fitness-toolkit' ) }
								</a>
							) }
						</div>
					</li>
				) ) }
			</ol>

			{ kit.length === 0 && (
				<p className="fb-muted">
					{ __( 'No gear added yet.', 'arponsamanta-fitness-toolkit' ) }
				</p>
			) }
			{ priced.length > 0 && (
				<p className="fb-muted">
					{ sprintf(
						/* translators: 1: total price, 2: number of priced items */
						_n(
							'About ₹%1$s for the %2$d item with a listed price.',
							'About ₹%1$s for the %2$d items with a listed price.',
							priced.length,
							'arponsamanta-fitness-toolkit'
						),
						total.toLocaleString( 'en-IN' ),
						priced.length
					) }
				</p>
			) }
			<p className="fb-muted">
				{ __(
					'Buy in this order: each item unlocks more exercises than the next. Some links may be affiliate links.',
					'arponsamanta-fitness-toolkit'
				) }
			</p>
		</div>
	);
}

<?php
/**
 * Home gym kit builder: every gear item, in buying order, as plain HTML
 * first, then the React view that filters by budget tier.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

use Fitbull\Core\Content_Types;

$fitbull_tiers = array( 'starter', 'serious', 'full' );
$fitbull_items = array_map(
	static fn( $post ) => array(
		'id'       => $post->ID,
		'title'    => get_the_title( $post ),
		'link'     => get_permalink( $post ),
		'excerpt'  => wp_strip_all_tags( get_the_excerpt( $post ) ),
		'tier'     => get_post_meta( $post->ID, 'fb_budget_tier', true ) ?: 'starter',
		'priority' => (int) get_post_meta( $post->ID, 'fb_priority', true ),
		'price'    => (int) get_post_meta( $post->ID, 'fb_price_inr', true ),
		'buy_url'  => esc_url_raw( (string) get_post_meta( $post->ID, 'fb_buy_url', true ) ),
	),
	get_posts(
		array(
			'post_type'      => Content_Types::EQUIPMENT,
			'post_status'    => 'publish',
			'posts_per_page' => 200,
		)
	)
);
usort(
	$fitbull_items,
	static fn( $a, $b ) => array( array_search( $a['tier'], $fitbull_tiers, true ), $a['priority'] ) <=> array( array_search( $b['tier'], $fitbull_tiers, true ), $b['priority'] )
);
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div data-fitbull-kit="<?php echo esc_attr( wp_json_encode( array( 'items' => $fitbull_items ) ) ); ?>">
		<div class="fb-ui fb-kit">
			<ol class="fb-kit__list">
				<?php foreach ( $fitbull_items as $fitbull_i => $fitbull_item ) : ?>
					<li class="fb-panel fb-kit__item">
						<span class="fb-kit__step"><?php echo esc_html( (string) ( $fitbull_i + 1 ) ); ?></span>
						<div class="fb-kit__body">
							<h3 class="fb-kit__title"><a href="<?php echo esc_url( $fitbull_item['link'] ); ?>"><?php echo esc_html( $fitbull_item['title'] ); ?></a></h3>
							<p class="fb-muted"><?php echo esc_html( $fitbull_item['excerpt'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</div>

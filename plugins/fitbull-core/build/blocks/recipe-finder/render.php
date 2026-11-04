<?php
/**
 * Server render: the top recipes as crawlable HTML, plus the data the
 * React view hydrates from.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

use Fitbull\Core\Content_Types;
use Fitbull\Core\Rest_Planner;

$fitbull_per_page = max( 1, min( 48, (int) ( $attributes['perPage'] ?? 12 ) ) );
$fitbull_initial  = Rest_Planner::query_recipes( array(), 1, $fitbull_per_page );
$fitbull_terms    = static function ( string $taxonomy, array $order ): array {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	$list  = is_wp_error( $terms ) ? array() : array_map(
		static fn( $t ) => array(
			'slug' => $t->slug,
			'name' => $t->name,
		),
		$terms
	);
	$rank  = static function ( string $slug ) use ( $order ): int {
		$pos = array_search( $slug, $order, true );
		return false === $pos ? 99 : (int) $pos;
	};
	usort( $list, static fn( $a, $b ) => $rank( $a['slug'] ) <=> $rank( $b['slug'] ) );
	return $list;
};
$fitbull_filters = array(
	'diets' => $fitbull_terms( Content_Types::DIET, array_keys( Content_Types::diet_includes() ) ),
	'meals' => $fitbull_terms( Content_Types::MEAL, Fitbull\Core\Meal_Planner::MEALS ),
);
$fitbull_props   = array(
	'perPage' => $fitbull_per_page,
	'initial' => $fitbull_initial,
	'filters' => $fitbull_filters,
);
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div <?php echo Fitbull\Core\Blocks::props_attribute( 'fitbull-recipes', $fitbull_props ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in props_attribute(). ?>>
		<div class="fb-ui fb-recipes">
			<?php if ( $fitbull_initial['items'] ) : ?>
				<ul class="fb-recipes__grid">
					<?php foreach ( $fitbull_initial['items'] as $fitbull_recipe ) : ?>
						<li class="fb-rcard">
							<div class="fb-rcard__media" aria-hidden="true"><span class="fb-rcard__kcal"><?php echo esc_html( (string) $fitbull_recipe['calories'] ); ?></span></div>
							<div class="fb-rcard__body">
								<h3 class="fb-rcard__title"><a href="<?php echo esc_url( $fitbull_recipe['link'] ); ?>"><?php echo esc_html( $fitbull_recipe['title'] ); ?></a></h3>
								<p class="fb-rcard__meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: kcal, 2: protein grams, 3: minutes */
											__( '%1$d kcal · %2$d g protein · %3$d min', 'fitbull-core' ),
											$fitbull_recipe['calories'],
											$fitbull_recipe['protein_g'],
											$fitbull_recipe['prep_minutes']
										)
									);
									?>
								</p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="fb-muted"><?php esc_html_e( 'No recipes yet. Add some under Recipes in the dashboard.', 'fitbull-core' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

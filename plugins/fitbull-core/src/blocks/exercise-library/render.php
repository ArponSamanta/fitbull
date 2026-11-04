<?php
/**
 * Server render: the first page of exercises as plain HTML (crawlable,
 * works without JS), plus the data the React view hydrates from.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

use Fitbull\Core\Rest_Controller;

$fitbull_per_page = max( 1, min( 48, (int) ( $attributes['perPage'] ?? 12 ) ) );
$fitbull_initial  = Rest_Controller::query_exercises( array(), 1, $fitbull_per_page );
$fitbull_filters  = Rest_Controller::filters();
$fitbull_levels   = array(
	'beginner'     => __( 'Beginner', 'fitbull-core' ),
	'intermediate' => __( 'Intermediate', 'fitbull-core' ),
	'advanced'     => __( 'Advanced', 'fitbull-core' ),
);
$fitbull_props    = array(
	'perPage'     => $fitbull_per_page,
	'showFilters' => ! isset( $attributes['showFilters'] ) || (bool) $attributes['showFilters'],
	'initial'     => $fitbull_initial,
	'filters'     => $fitbull_filters,
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div data-fitbull-library="<?php echo esc_attr( wp_json_encode( $fitbull_props ) ); ?>">
		<div class="fb-lib">
			<?php if ( $fitbull_props['showFilters'] && $fitbull_filters['muscles'] ) : ?>
				<div class="fb-lib__filters">
					<div class="fb-lib__chips">
						<?php foreach ( $fitbull_filters['muscles'] as $fitbull_term ) : ?>
							<a class="fb-chip" href="<?php echo esc_url( get_term_link( $fitbull_term['slug'], Fitbull\Core\Content_Types::MUSCLE ) ); ?>"><?php echo esc_html( $fitbull_term['name'] ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $fitbull_initial['items'] ) : ?>
				<ul class="fb-lib__grid">
					<?php foreach ( $fitbull_initial['items'] as $fitbull_ex ) : ?>
						<li class="fb-ex-card">
							<div class="fb-ex-card__media" aria-hidden="true">
								<?php if ( $fitbull_ex['image'] ) : ?>
									<img src="<?php echo esc_url( $fitbull_ex['image'] ); ?>" alt="" loading="lazy">
								<?php else : ?>
									<span class="fb-ex-card__initial"><?php echo esc_html( mb_substr( $fitbull_ex['title'], 0, 1 ) ); ?></span>
								<?php endif; ?>
							</div>
							<div class="fb-ex-card__body">
								<h3 class="fb-ex-card__title"><a href="<?php echo esc_url( $fitbull_ex['link'] ); ?>"><?php echo esc_html( $fitbull_ex['title'] ); ?></a></h3>
								<p class="fb-ex-card__meta">
									<?php
									echo esc_html(
										implode(
											' · ',
											array_filter(
												array(
													$fitbull_levels[ $fitbull_ex['difficulty'] ] ?? '',
													implode( ', ', $fitbull_ex['equipment'] ),
													implode( ', ', $fitbull_ex['muscles'] ),
												)
											)
										)
									);
									?>
								</p>
							</div>
							<span class="fb-badge fb-badge--<?php echo esc_attr( $fitbull_ex['difficulty'] ); ?>"><?php echo esc_html( $fitbull_levels[ $fitbull_ex['difficulty'] ] ?? '' ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="fb-lib__empty"><?php esc_html_e( 'No exercises yet. Add some under Exercises in the dashboard.', 'fitbull-core' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

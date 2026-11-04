<?php
/**
 * Server render: a mount point for the React calculator, plus a
 * server-calculated result so the block is useful before JS loads.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

$fitbull_props = array(
	'compact'     => ! empty( $attributes['compact'] ),
	'mealPlanUrl' => esc_url_raw( $attributes['mealPlanUrl'] ?? '' ),
);

$fitbull_preview = ( new Fitbull\Core\Macro_Calculator() )->calculate(
	array(
		'sex'       => 'male',
		'age'       => 25,
		'height_cm' => 170,
		'weight_kg' => 70,
		'activity'  => 'moderate',
		'goal'      => 'maintain',
	)
);
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div data-fitbull-macro="<?php echo esc_attr( wp_json_encode( $fitbull_props ) ); ?>">
		<div class="fb-calc<?php echo $fitbull_props['compact'] ? ' fb-calc--compact' : ''; ?>">
			<div class="fb-calc__head">
				<h2 class="fb-calc__title"><?php esc_html_e( 'Your daily targets', 'fitbull-core' ); ?></h2>
				<span class="fb-calc__muted"><?php esc_html_e( 'Macro calculator', 'fitbull-core' ); ?></span>
			</div>
			<p class="fb-calc__kcal">
				<span class="fb-calc__kcal-number"><?php echo esc_html( number_format_i18n( $fitbull_preview['calories'] ) ); ?></span>
				<span class="fb-calc__muted"><?php esc_html_e( 'kcal per day for a 25-year-old, 170 cm, 70 kg, moderately active man. Loading the calculator…', 'fitbull-core' ); ?></span>
			</p>
		</div>
	</div>
</div>

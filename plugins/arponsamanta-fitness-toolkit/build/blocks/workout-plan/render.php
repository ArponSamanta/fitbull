<?php
/**
 * Mount point for the workout plan generator, with the equipment list
 * the visitor can tick (everything except bodyweight, which is implied).
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

use Fitbull\Core\Content_Types;
use Fitbull\Core\Plan_Generator;

$fitbull_terms     = get_terms(
	array(
		'taxonomy'   => Content_Types::GEAR_TYPE,
		'hide_empty' => true,
	)
);
$fitbull_equipment = array();
if ( ! is_wp_error( $fitbull_terms ) ) {
	foreach ( $fitbull_terms as $fitbull_term ) {
		if ( Plan_Generator::BODYWEIGHT !== $fitbull_term->slug ) {
			$fitbull_equipment[] = array(
				'slug' => $fitbull_term->slug,
				'name' => $fitbull_term->name,
			);
		}
	}
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div data-fitbull-plan="<?php echo esc_attr( wp_json_encode( array( 'equipment' => $fitbull_equipment ) ) ); ?>">
		<div class="fb-ui">
			<div class="fb-panel">
				<h3 class="fb-panel__title"><?php esc_html_e( 'Workout plan generator', 'arponsamanta-fitness-toolkit' ); ?></h3>
				<p class="fb-muted"><?php esc_html_e( 'Loading… This tool needs JavaScript. You can still browse every exercise in the library.', 'arponsamanta-fitness-toolkit' ); ?></p>
			</div>
		</div>
	</div>
</div>

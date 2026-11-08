<?php
/**
 * Mount point for the meal planner.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div data-fitbull-meals="<?php echo esc_attr( wp_json_encode( array() ) ); ?>">
		<div class="fb-ui"><div class="fb-panel"><p class="fb-muted"><?php esc_html_e( 'Loading the meal planner… This tool needs JavaScript.', 'arponsamanta-fitness-toolkit' ); ?></p></div></div>
	</div>
</div>

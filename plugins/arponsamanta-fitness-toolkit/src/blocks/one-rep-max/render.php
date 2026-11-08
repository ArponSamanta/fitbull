<?php
/**
 * Mount point for the one-rep-max calculator.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div data-fitbull-1rm="<?php echo esc_attr( wp_json_encode( array() ) ); ?>">
		<div class="fb-ui"><p class="fb-muted"><?php esc_html_e( 'Loading the one-rep-max calculator…', 'arponsamanta-fitness-toolkit' ); ?></p></div>
	</div>
</div>

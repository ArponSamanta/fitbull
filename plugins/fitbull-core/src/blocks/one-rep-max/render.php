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
	<div <?php echo Fitbull\Core\Blocks::props_attribute( 'fitbull-1rm', array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in props_attribute(). ?>>
		<div class="fb-ui"><p class="fb-muted"><?php esc_html_e( 'Loading the one-rep-max calculator…', 'fitbull-core' ); ?></p></div>
	</div>
</div>

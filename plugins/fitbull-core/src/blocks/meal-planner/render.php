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
	<div <?php echo Fitbull\Core\Blocks::props_attribute( 'fitbull-meals', array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in props_attribute(). ?>>
		<div class="fb-ui"><div class="fb-panel"><p class="fb-muted"><?php esc_html_e( 'Loading the meal planner… This tool needs JavaScript.', 'fitbull-core' ); ?></p></div></div>
	</div>
</div>

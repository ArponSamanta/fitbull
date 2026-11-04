<?php
/**
 * Title: Hero with macro calculator
 * Slug: fitbull/hero
 * Categories: fitbull, banner
 * Description: Headline, two calls to action and the live macro calculator.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
			<p class="is-style-eyebrow"><?php esc_html_e( 'Workouts · Diet · Gear · Supplements', 'fitbull' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":1} -->
			<h1 class="wp-block-heading"><?php echo wp_kses_post( __( 'Train smart.<br>Eat Indian.<br><span class="fb-brand-accent">Buy right.</span>', 'fitbull' ) ); ?></h1>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"muted","fontSize":"large"} -->
			<p class="has-muted-color has-text-color has-large-font-size"><?php esc_html_e( 'Correct form for every lift, meal plans built on dal, paneer and rice, and honest gear picks for every budget. No bro-science.', 'fitbull' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/tools/"><?php esc_html_e( 'Build my free plan', 'fitbull' ); ?></a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-ghost"} -->
				<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="/exercises/"><?php esc_html_e( 'Explore exercises', 'fitbull' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:fitbull/macro-calculator {"compact":true} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

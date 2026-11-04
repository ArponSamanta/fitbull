<?php
/**
 * Title: Lime call-to-action band
 * Slug: fitbull/cta-band
 * Categories: fitbull, call-to-action
 * Description: Bright band pointing beginners to the free plan.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull" style="padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"backgroundColor":"lime","textColor":"ink","style":{"border":{"radius":"28px"},"spacing":{"padding":{"top":"3.5rem","bottom":"3.5rem","left":"clamp(1.5rem, 5vw, 3.5rem)","right":"clamp(1.5rem, 5vw, 3.5rem)"}},"elements":{"link":{"color":{"text":"var:preset|color|ink"}}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group has-ink-color has-lime-background-color has-text-color has-background has-link-color" style="border-radius:28px;padding-top:3.5rem;padding-right:clamp(1.5rem, 5vw, 3.5rem);padding-bottom:3.5rem;padding-left:clamp(1.5rem, 5vw, 3.5rem)">
		<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"560px"},"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group">
			<!-- wp:heading {"textColor":"ink"} -->
			<h2 class="wp-block-heading has-ink-color has-text-color"><?php esc_html_e( 'New to the gym?', 'fitbull' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size"><?php esc_html_e( 'Get your calorie target, then follow the 3-day full-body plan. Free, no sign-up.', 'fitbull' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"backgroundColor":"ink"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-ink-background-color has-background wp-element-button" href="/tools/"><?php esc_html_e( 'Start the plan', 'fitbull' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

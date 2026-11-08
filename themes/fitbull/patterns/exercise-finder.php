<?php
/**
 * Title: Exercise finder
 * Slug: fitbull/exercise-finder
 * Categories: fitbull
 * Description: Intro text beside the filterable exercise library. Needs the ArponSamanta Fitness Toolkit plugin.
 */

if ( ! class_exists( 'Fitbull\\Core\\Blocks' ) ) {
	return;
}
?>
<!-- wp:group {"align":"full","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"border":{"top":{"color":"var:preset|color|line","width":"1px"},"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="border-top-color:var(--wp--preset--color--line);border-top-width:1px;border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
	<p class="is-style-eyebrow"><?php esc_html_e( 'Exercise finder', 'fitbull' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Pick a muscle. Get the moves.', 'fitbull' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"textColor":"muted"} -->
	<p class="has-muted-color has-text-color"><?php esc_html_e( 'Choose what you want to train and the equipment you have. Easiest exercises come first.', 'fitbull' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:fitbull/exercise-library {"perPage":6} /-->
</div>
<!-- /wp:group -->

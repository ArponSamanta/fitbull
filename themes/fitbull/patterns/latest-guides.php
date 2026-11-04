<?php
/**
 * Title: Latest guides
 * Slug: fitbull/latest-guides
 * Categories: fitbull, query
 * Description: The three newest articles as cards.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Start here', 'fitbull' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:query {"queryId":1,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
	<div class="wp-block-query">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"300px"}} -->
			<!-- wp:group {"className":"fb-post-card","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0.75rem"}}} -->
			<div class="wp-block-group fb-post-card">
				<!-- wp:post-featured-image {"isLink":true} /-->
				<!-- wp:post-terms {"term":"category","className":"is-style-eyebrow","style":{"typography":{"textTransform":"uppercase","fontWeight":"600","letterSpacing":"0.1em"}},"fontSize":"small"} /-->
				<!-- wp:post-title {"level":3,"isLink":true} /-->
				<!-- wp:post-excerpt {"excerptLength":20,"textColor":"muted"} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
		<!-- wp:query-no-results -->
			<!-- wp:paragraph {"textColor":"muted"} -->
			<p class="has-muted-color has-text-color"><?php esc_html_e( 'Guides are on the way.', 'fitbull' ); ?></p>
			<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->

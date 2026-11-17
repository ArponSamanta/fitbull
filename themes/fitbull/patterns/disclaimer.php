<?php
/**
 * Title: Disclaimer note
 * Slug: fitbull/disclaimer
 * Categories: fitbull
 * Description: A small muted note for health or affiliate disclaimers. Edit the text to suit your site.
 *
 * @package Fitbull
 */

?>
<!-- wp:group {"className":"fb-disclaimer","style":{"spacing":{"padding":{"top":"var:preset|spacing|30"}},"border":{"top":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group fb-disclaimer" style="border-top-color:var(--wp--preset--color--line);border-top-width:1px;padding-top:var(--wp--preset--spacing--30)">
<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'This article is general information, not medical advice. Talk to a doctor before starting a new training or diet plan.', 'fitbull' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

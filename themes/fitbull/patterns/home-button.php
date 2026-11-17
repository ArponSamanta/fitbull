<?php
/**
 * Title: Home page button
 * Slug: fitbull/home-button
 * Inserter: no
 * Description: A button back to the site's home page, used by the 404 template.
 *
 * @package Fitbull
 */

?>
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home page', 'fitbull' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

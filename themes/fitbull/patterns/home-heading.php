<?php
/**
 * Title: Blog home heading
 * Slug: fitbull/home-heading
 * Inserter: no
 * Description: The level 1 heading of the blog index: the posts page's title, or "Latest articles" when the front page lists the posts.
 *
 * @package Fitbull
 */

$fitbull_posts_page = (int) get_option( 'page_for_posts' );
$fitbull_heading    = $fitbull_posts_page ? get_the_title( $fitbull_posts_page ) : __( 'Latest articles', 'fitbull' );
?>
<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"var:preset|font-size|xx-large"}}} -->
<h1 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--xx-large)"><?php echo esc_html( $fitbull_heading ); ?></h1>
<!-- /wp:heading -->

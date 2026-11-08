<?php
/**
 * Server render for a single exercise's structured fields.
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance (for context).
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

use Fitbull\Core\Content_Types;

$fitbull_post_id = (int) ( $block->context['postId'] ?? 0 );
if ( ! $fitbull_post_id ) {
	$fitbull_post_id = (int) get_the_ID();
}
// Never show a draft or private post to someone who can't read it.
if ( $fitbull_post_id && ! is_post_publicly_viewable( $fitbull_post_id ) && ! current_user_can( 'read_post', $fitbull_post_id ) ) {
	return;
}
if ( ! $fitbull_post_id || Content_Types::EXERCISE !== get_post_type( $fitbull_post_id ) ) {
	return;
}

$fitbull_levels     = array(
	'beginner'     => __( 'Beginner', 'arponsamanta-fitness-toolkit' ),
	'intermediate' => __( 'Intermediate', 'arponsamanta-fitness-toolkit' ),
	'advanced'     => __( 'Advanced', 'arponsamanta-fitness-toolkit' ),
);
$fitbull_difficulty = get_post_meta( $fitbull_post_id, 'fb_difficulty', true ) ?: 'beginner';
$fitbull_steps      = (array) get_post_meta( $fitbull_post_id, 'fb_steps', true );
$fitbull_mistakes   = (array) get_post_meta( $fitbull_post_id, 'fb_mistakes', true );
$fitbull_secondary  = array_filter( (array) get_post_meta( $fitbull_post_id, 'fb_secondary_muscles', true ) );
$fitbull_video      = get_post_meta( $fitbull_post_id, 'fb_video_url', true );
$fitbull_muscles    = get_the_terms( $fitbull_post_id, Content_Types::MUSCLE );
$fitbull_equipment  = get_the_terms( $fitbull_post_id, Content_Types::GEAR_TYPE );
$fitbull_embed      = $fitbull_video ? wp_oembed_get( $fitbull_video ) : false;
$fitbull_tools      = get_page_by_path( 'tools' );
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'fb-exd' ) ) ); ?>>
	<div class="fb-exd__tags">
		<span class="fb-badge fb-badge--<?php echo esc_attr( $fitbull_difficulty ); ?>"><?php echo esc_html( $fitbull_levels[ $fitbull_difficulty ] ?? '' ); ?></span>
		<?php if ( $fitbull_equipment && ! is_wp_error( $fitbull_equipment ) ) : ?>
			<?php foreach ( $fitbull_equipment as $fitbull_term ) : ?>
				<a class="fb-badge" href="<?php echo esc_url( get_term_link( $fitbull_term ) ); ?>"><?php echo esc_html( $fitbull_term->name ); ?></a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<div class="fb-exd__grid">
		<div class="fb-exd__main">
			<?php if ( $fitbull_embed ) : ?>
				<div class="fb-exd__video"><?php echo wp_kses( $fitbull_embed, Fitbull\Core\Blocks::EMBED_HTML ); ?></div>
			<?php endif; ?>

			<?php if ( $fitbull_steps ) : ?>
				<section class="fb-exd__section">
					<h2 class="fb-exd__heading"><?php esc_html_e( 'How to do it', 'arponsamanta-fitness-toolkit' ); ?></h2>
					<ol class="fb-exd__steps">
						<?php foreach ( $fitbull_steps as $fitbull_step ) : ?>
							<li class="fb-exd__step">
								<span class="fb-exd__step-title"><?php echo esc_html( $fitbull_step['title'] ?? '' ); ?></span>
								<span class="fb-exd__step-text"><?php echo esc_html( $fitbull_step['text'] ?? '' ); ?></span>
							</li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>

			<?php if ( $fitbull_mistakes ) : ?>
				<section class="fb-exd__section">
					<h2 class="fb-exd__heading"><?php esc_html_e( 'Common mistakes', 'arponsamanta-fitness-toolkit' ); ?></h2>
					<ul class="fb-exd__mistakes">
						<?php foreach ( $fitbull_mistakes as $fitbull_mistake ) : ?>
							<li class="fb-exd__mistake">
								<span class="fb-exd__mistake-title"><?php echo esc_html( $fitbull_mistake['title'] ?? '' ); ?></span>
								<span class="fb-exd__mistake-fix"><?php echo esc_html( $fitbull_mistake['fix'] ?? '' ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>

		<aside class="fb-exd__aside">
			<div class="fb-exd__card">
				<h2 class="fb-exd__card-title"><?php esc_html_e( 'Muscles worked', 'arponsamanta-fitness-toolkit' ); ?></h2>
				<ul class="fb-exd__muscles">
					<?php if ( $fitbull_muscles && ! is_wp_error( $fitbull_muscles ) ) : ?>
						<?php foreach ( $fitbull_muscles as $fitbull_term ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $fitbull_term ) ); ?>"><?php echo esc_html( $fitbull_term->name ); ?></a><span class="fb-badge fb-badge--beginner"><?php esc_html_e( 'Primary', 'arponsamanta-fitness-toolkit' ); ?></span></li>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php foreach ( $fitbull_secondary as $fitbull_name ) : ?>
						<li><span><?php echo esc_html( $fitbull_name ); ?></span><span class="fb-badge"><?php esc_html_e( 'Secondary', 'arponsamanta-fitness-toolkit' ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( $fitbull_tools ) : ?>
				<div class="fb-exd__card">
					<h2 class="fb-exd__card-title"><?php esc_html_e( 'Fuel the work', 'arponsamanta-fitness-toolkit' ); ?></h2>
					<p class="fb-exd__muted"><?php esc_html_e( 'Get your daily calorie and protein target in under a minute.', 'arponsamanta-fitness-toolkit' ); ?></p>
					<a class="fb-exd__cta" href="<?php echo esc_url( get_permalink( $fitbull_tools ) ); ?>"><?php esc_html_e( 'Open the calculator', 'arponsamanta-fitness-toolkit' ); ?></a>
				</div>
			<?php endif; ?>

			<p class="fb-exd__muted fb-exd__small"><?php esc_html_e( 'Information only, not medical advice. Stop if you feel sharp pain, and check with a doctor before training with an injury.', 'arponsamanta-fitness-toolkit' ); ?></p>
		</aside>
	</div>
</div>

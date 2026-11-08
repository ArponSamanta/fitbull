<?php
/**
 * Server render for the current recipe: nutrition, ingredients and method.
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance (for context).
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

use Fitbull\Core\Content_Types;

$fitbull_id = (int) ( $block->context['postId'] ?? 0 );
if ( ! $fitbull_id ) {
	$fitbull_id = (int) get_the_ID();
}
// Never show a draft or private post to someone who can't read it.
if ( $fitbull_id && ! is_post_publicly_viewable( $fitbull_id ) && ! current_user_can( 'read_post', $fitbull_id ) ) {
	return;
}
if ( ! $fitbull_id || Content_Types::RECIPE !== get_post_type( $fitbull_id ) ) {
	return;
}

$fitbull_int         = static fn( string $key ) => (int) get_post_meta( $fitbull_id, $key, true );
$fitbull_kcal        = $fitbull_int( 'fb_calories' );
$fitbull_macros      = array(
	array( __( 'Protein', 'arponsamanta-fitness-toolkit' ), $fitbull_int( 'fb_protein_g' ), 4, '' ),
	array( __( 'Carbs', 'arponsamanta-fitness-toolkit' ), $fitbull_int( 'fb_carbs_g' ), 4, 'sky' ),
	array( __( 'Fat', 'arponsamanta-fitness-toolkit' ), $fitbull_int( 'fb_fat_g' ), 9, 'amber' ),
);
$fitbull_ingredients = array_filter( (array) get_post_meta( $fitbull_id, 'fb_ingredients', true ) );
$fitbull_method      = array_filter( (array) get_post_meta( $fitbull_id, 'fb_method', true ) );
$fitbull_planner     = get_page_by_path( 'meal-plan' );
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'fb-ui fb-recipe' ) ) ); ?>>
	<div class="fb-recipe__grid">
		<div class="fb-recipe__main">
			<?php if ( $fitbull_ingredients ) : ?>
				<section class="fb-panel">
					<h2 class="fb-panel__title"><?php esc_html_e( 'Ingredients', 'arponsamanta-fitness-toolkit' ); ?></h2>
					<ul class="fb-recipe__ingredients">
						<?php foreach ( $fitbull_ingredients as $fitbull_row ) : ?>
							<li><span><?php echo esc_html( $fitbull_row['item'] ?? '' ); ?></span><span class="fb-muted fb-num"><?php echo esc_html( $fitbull_row['qty'] ?? '' ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $fitbull_method ) : ?>
				<section class="fb-panel">
					<h2 class="fb-panel__title"><?php esc_html_e( 'Method', 'arponsamanta-fitness-toolkit' ); ?></h2>
					<ol class="fb-recipe__method">
						<?php foreach ( $fitbull_method as $fitbull_step ) : ?>
							<li><?php echo esc_html( $fitbull_step ); ?></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>
		</div>

		<aside class="fb-panel fb-recipe__nutrition">
			<h2 class="fb-panel__title"><?php esc_html_e( 'Per serving', 'arponsamanta-fitness-toolkit' ); ?></h2>
			<p class="fb-recipe__kcal"><span class="fb-stat__value"><?php echo esc_html( number_format_i18n( $fitbull_kcal ) ); ?></span> <span class="fb-muted"><?php esc_html_e( 'kcal', 'arponsamanta-fitness-toolkit' ); ?></span></p>
			<?php foreach ( $fitbull_macros as list( $fitbull_label, $fitbull_grams, $fitbull_per_gram, $fitbull_tone ) ) : ?>
				<?php $fitbull_share = $fitbull_kcal ? min( 100, (int) round( $fitbull_grams * $fitbull_per_gram * 100 / $fitbull_kcal ) ) : 0; ?>
				<div class="fb-meter">
					<div class="fb-meter__head"><span><?php echo esc_html( $fitbull_label ); ?></span><span class="fb-muted fb-num"><?php echo esc_html( sprintf( /* translators: %d: grams */ __( '%d g', 'arponsamanta-fitness-toolkit' ), $fitbull_grams ) ); ?></span></div>
					<div class="fb-meter__track"><div class="fb-meter__fill<?php echo $fitbull_tone ? ' fb-meter__fill--' . esc_attr( $fitbull_tone ) : ''; ?>" style="width:<?php echo esc_attr( (string) $fitbull_share ); ?>%"></div></div>
				</div>
			<?php endforeach; ?>
			<p class="fb-muted"><?php esc_html_e( 'Approximate values. They vary with brands and portion sizes.', 'arponsamanta-fitness-toolkit' ); ?></p>
			<?php if ( $fitbull_planner ) : ?>
				<a class="fb-btn" href="<?php echo esc_url( get_permalink( $fitbull_planner ) ); ?>"><?php esc_html_e( 'Plan a day around this', 'arponsamanta-fitness-toolkit' ); ?></a>
			<?php endif; ?>
		</aside>
	</div>
</div>

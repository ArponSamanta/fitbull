<?php
/**
 * Key facts for the current item, by post type. Works inside Query Loop
 * blocks (via context) and on single pages.
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
$fitbull_type = $fitbull_id ? get_post_type( $fitbull_id ) : '';
if ( ! $fitbull_type ) {
	return;
}

$fitbull_full   = 'full' === ( $attributes['layout'] ?? 'card' );
$fitbull_meta   = static fn( string $key ) => get_post_meta( $fitbull_id, $key, true );
$fitbull_badges = array();
$fitbull_text   = '';
$fitbull_button = null;

switch ( $fitbull_type ) {
	case Content_Types::SUPPLEMENT:
		$fitbull_levels   = array(
			'strong'   => __( 'Strong evidence', 'arponsamanta-fitness-toolkit' ),
			'moderate' => __( 'Moderate evidence', 'arponsamanta-fitness-toolkit' ),
			'weak'     => __( 'Weak evidence', 'arponsamanta-fitness-toolkit' ),
			'none'     => __( 'No good evidence', 'arponsamanta-fitness-toolkit' ),
		);
		$fitbull_evidence = $fitbull_meta( 'fb_evidence' ) ?: 'none';
		$fitbull_badges[] = array( $fitbull_levels[ $fitbull_evidence ] ?? '', 'evidence-' . $fitbull_evidence );
		$fitbull_text     = (string) $fitbull_meta( 'fb_verdict' );
		break;

	case Content_Types::EQUIPMENT:
		$fitbull_tiers    = array(
			'starter' => __( 'Starter', 'arponsamanta-fitness-toolkit' ),
			'serious' => __( 'Serious', 'arponsamanta-fitness-toolkit' ),
			'full'    => __( 'Full setup', 'arponsamanta-fitness-toolkit' ),
		);
		$fitbull_tier     = $fitbull_meta( 'fb_budget_tier' ) ?: 'starter';
		$fitbull_badges[] = array( $fitbull_tiers[ $fitbull_tier ] ?? '', 'tier-' . $fitbull_tier );
		$fitbull_price    = (int) $fitbull_meta( 'fb_price_inr' );
		if ( $fitbull_price ) {
			/* translators: %s: price in rupees */
			$fitbull_badges[] = array( sprintf( __( 'About ₹%s', 'arponsamanta-fitness-toolkit' ), number_format_i18n( $fitbull_price ) ), '' );
		}
		$fitbull_url = (string) $fitbull_meta( 'fb_buy_url' );
		if ( $fitbull_full && $fitbull_url ) {
			$fitbull_button = $fitbull_url;
		}
		break;

	case Content_Types::RECIPE:
		$fitbull_badges[] = array( sprintf( /* translators: %d: kcal */ __( '%d kcal', 'arponsamanta-fitness-toolkit' ), (int) $fitbull_meta( 'fb_calories' ) ), 'kcal' );
		$fitbull_badges[] = array( sprintf( /* translators: %d: grams */ __( '%d g protein', 'arponsamanta-fitness-toolkit' ), (int) $fitbull_meta( 'fb_protein_g' ) ), '' );
		$fitbull_badges[] = array( sprintf( /* translators: %d: minutes */ __( '%d min', 'arponsamanta-fitness-toolkit' ), (int) $fitbull_meta( 'fb_prep_minutes' ) ), '' );
		$fitbull_diets    = get_the_terms( $fitbull_id, Content_Types::DIET );
		if ( $fitbull_diets && ! is_wp_error( $fitbull_diets ) ) {
			$fitbull_badges[] = array( implode( ', ', wp_list_pluck( $fitbull_diets, 'name' ) ), '' );
		}
		break;

	case Content_Types::EXERCISE:
		$fitbull_levels     = array(
			'beginner'     => __( 'Beginner', 'arponsamanta-fitness-toolkit' ),
			'intermediate' => __( 'Intermediate', 'arponsamanta-fitness-toolkit' ),
			'advanced'     => __( 'Advanced', 'arponsamanta-fitness-toolkit' ),
		);
		$fitbull_difficulty = $fitbull_meta( 'fb_difficulty' ) ?: 'beginner';
		$fitbull_badges[]   = array( $fitbull_levels[ $fitbull_difficulty ] ?? '', $fitbull_difficulty );
		break;

	default:
		return;
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'fb-facts' . ( $fitbull_full ? ' fb-facts--full' : '' ) ) ) ); ?>>
	<div class="fb-facts__badges">
		<?php foreach ( $fitbull_badges as list( $fitbull_label, $fitbull_variant ) ) : ?>
			<?php if ( $fitbull_label ) : ?>
				<span class="fb-badge<?php echo $fitbull_variant ? ' fb-badge--' . esc_attr( $fitbull_variant ) : ''; ?>"><?php echo esc_html( $fitbull_label ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php if ( $fitbull_text ) : ?>
		<p class="fb-facts__text"><?php echo esc_html( $fitbull_text ); ?></p>
	<?php endif; ?>
	<?php if ( $fitbull_button ) : ?>
		<p class="fb-facts__buy">
			<a class="fb-btn" href="<?php echo esc_url( $fitbull_button ); ?>" rel="sponsored nofollow noopener" target="_blank"><?php esc_html_e( 'Check price', 'arponsamanta-fitness-toolkit' ); ?></a>
			<span class="fb-facts__note"><?php esc_html_e( 'This may be an affiliate link.', 'arponsamanta-fitness-toolkit' ); ?></span>
		</p>
	<?php endif; ?>
</div>

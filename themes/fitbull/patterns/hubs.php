<?php
/**
 * Title: Six content hubs
 * Slug: fitbull/hubs
 * Categories: fitbull
 * Description: Grid of linked cards, one per section of the site. Needs the Fitbull Core plugin.
 */

// These sections come from the Fitbull Core plugin; without it, show nothing.
if ( ! class_exists( 'Fitbull\\Core\\Blocks' ) ) {
	return;
}

$fitbull_hubs = array(
	array( '01', __( 'Workout plan', 'fitbull' ), __( 'A weekly plan for your goal, schedule and equipment, in under a minute.', 'fitbull' ), '/workout-plan/', true ),
	array( '02', __( 'Exercise library', 'fitbull' ), __( 'Form cues, common mistakes and easier or harder versions.', 'fitbull' ), '/exercises/', false ),
	array( '03', __( 'Recipes and meal plan', 'fitbull' ), __( 'Indian dishes with macros, and a day of meals that hits your target.', 'fitbull' ), '/meal-plan/', false ),
	array( '04', __( 'Supplements', 'fitbull' ), __( 'What works, what to skip, rated by strength of evidence.', 'fitbull' ), '/supplements/', false ),
	array( '05', __( 'Gear and equipment', 'fitbull' ), __( 'What to buy first for a home gym, by budget.', 'fitbull' ), '/gear/', false ),
	array( '06', __( 'Tools and progress', 'fitbull' ), __( 'Macro and one-rep-max calculators, plus your private progress log.', 'fitbull' ), '/tools/', false ),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group">
		<!-- wp:heading -->
		<h2 class="wp-block-heading"><?php esc_html_e( 'Everything in one place', 'fitbull' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"textColor":"muted","style":{"layout":{"selfStretch":"fixed","flexSize":"420px"}}} -->
		<p class="has-muted-color has-text-color"><?php esc_html_e( 'Six hubs, all linked, so a squat guide is one tap from the right shoes and the right meal.', 'fitbull' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"layout":{"type":"grid","minimumColumnWidth":"340px"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
	<div class="wp-block-group">
		<?php foreach ( $fitbull_hubs as $fitbull_hub ) : ?>
			<?php $fitbull_class = $fitbull_hub[4] ? 'fb-hub fb-hub--accent' : 'fb-hub'; ?>
			<!-- wp:group {"className":"<?php echo esc_attr( $fitbull_class ); ?>","layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group <?php echo esc_attr( $fitbull_class ); ?>">
				<!-- wp:paragraph {"className":"fb-hub__num"} -->
				<p class="fb-hub__num"><?php echo esc_html( $fitbull_hub[0] ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:heading {"level":3,"className":"fb-hub__title"} -->
				<h3 class="wp-block-heading fb-hub__title"><a href="<?php echo esc_url( home_url( $fitbull_hub[3] ) ); ?>"><?php echo esc_html( $fitbull_hub[1] ); ?></a></h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"fb-hub__text"} -->
				<p class="fb-hub__text"><?php echo esc_html( $fitbull_hub[2] ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

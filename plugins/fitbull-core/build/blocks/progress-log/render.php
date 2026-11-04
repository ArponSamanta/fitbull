<?php
/**
 * Progress log: a login prompt for visitors, or the React log for users.
 * Entries are private and loaded from /fitbull/v1/logs after mount.
 *
 * @var array $attributes Block attributes.
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) :
	$fitbull_here = get_permalink() ?: home_url( '/' );
	?>
	<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
		<div class="fb-ui">
			<div class="fb-panel">
				<h3 class="fb-panel__title"><?php esc_html_e( 'Track your progress', 'fitbull-core' ); ?></h3>
				<p class="fb-muted"><?php esc_html_e( 'Log in to record your bodyweight and lifts. Your entries are private to your account.', 'fitbull-core' ); ?></p>
				<p class="fb-row">
					<a class="fb-btn" href="<?php echo esc_url( wp_login_url( $fitbull_here ) ); ?>"><?php esc_html_e( 'Log in', 'fitbull-core' ); ?></a>
					<?php if ( get_option( 'users_can_register' ) ) : ?>
						<a class="fb-btn fb-btn--ghost" href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Create an account', 'fitbull-core' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</div>
	<?php
	return;
endif;

$fitbull_exercises = array_map(
	static fn( $post ) => array(
		'id'    => $post->ID,
		'title' => get_the_title( $post ),
	),
	get_posts(
		array(
			'post_type'      => Fitbull\Core\Content_Types::EXERCISE,
			'post_status'    => 'publish',
			'posts_per_page' => 500,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	)
);
$fitbull_props     = array(
	'exercises' => $fitbull_exercises,
	'today'     => current_time( 'Y-m-d' ),
);
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div <?php echo Fitbull\Core\Blocks::props_attribute( 'fitbull-log', $fitbull_props ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in props_attribute(). ?>>
		<div class="fb-ui"><div class="fb-panel"><p class="fb-muted"><?php esc_html_e( 'Loading your log…', 'fitbull-core' ); ?></p></div></div>
	</div>
</div>

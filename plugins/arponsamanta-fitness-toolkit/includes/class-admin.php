<?php
/**
 * Tools → Fitness Toolkit demo content: a one-click importer for people who
 * don't use WP-CLI.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Admin {

	public const PAGE = 'fitbull-demo';

	public static function register_page(): void {
		add_management_page(
			__( 'Fitness Toolkit demo content', 'arponsamanta-fitness-toolkit' ),
			__( 'Fitness Toolkit demo content', 'arponsamanta-fitness-toolkit' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function action_links( array $links ): array {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=' . self::PAGE ) ),
			esc_html__( 'Demo content', 'arponsamanta-fitness-toolkit' )
		);
		return $links;
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$key      = 'fitbull_imported_' . get_current_user_id();
		$imported = get_transient( $key );
		$imported = false === $imported ? null : absint( $imported );
		delete_transient( $key );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Fitness Toolkit demo content', 'arponsamanta-fitness-toolkit' ); ?></h1>
			<?php if ( null !== $imported ) : ?>
				<div class="notice notice-success"><p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of items created. */
							_n( 'Imported %d item.', 'Imported %d items.', $imported, 'arponsamanta-fitness-toolkit' ),
							$imported
						)
					);
					?>
				</p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Adds exercises, Indian recipes, gear, supplements, three articles, the tool pages and a main menu. Anything that already exists is skipped, so it is safe to run again.', 'arponsamanta-fitness-toolkit' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fitbull_import_demo">
				<?php wp_nonce_field( 'fitbull_import_demo' ); ?>
				<?php submit_button( __( 'Import demo content', 'arponsamanta-fitness-toolkit' ) ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'arponsamanta-fitness-toolkit' ), 403 );
		}
		check_admin_referer( 'fitbull_import_demo' );

		$created = Demo_Content::import();
		set_transient( 'fitbull_imported_' . get_current_user_id(), $created, MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'tools.php?page=' . self::PAGE ) );
		exit;
	}
}

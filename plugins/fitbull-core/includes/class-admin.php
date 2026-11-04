<?php
/**
 * Tools → Fitbull demo content: a one-click importer for people who
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
			__( 'Fitbull demo content', 'fitbull-core' ),
			__( 'Fitbull demo content', 'fitbull-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function action_links( array $links ): array {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=' . self::PAGE ) ),
			esc_html__( 'Demo content', 'fitbull-core' )
		);
		return $links;
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only; the import itself is nonce-checked.
		$imported = isset( $_GET['imported'] ) ? absint( $_GET['imported'] ) : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Fitbull demo content', 'fitbull-core' ); ?></h1>
			<?php if ( null !== $imported ) : ?>
				<div class="notice notice-success"><p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of items created. */
							_n( 'Imported %d item.', 'Imported %d items.', $imported, 'fitbull-core' ),
							$imported
						)
					);
					?>
				</p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Adds exercises, Indian recipes, gear, supplements, three articles, the tool pages and a main menu. Anything that already exists is skipped, so it is safe to run again.', 'fitbull-core' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fitbull_import_demo">
				<?php wp_nonce_field( 'fitbull_import_demo' ); ?>
				<?php submit_button( __( 'Import demo content', 'fitbull-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'fitbull-core' ), 403 );
		}
		check_admin_referer( 'fitbull_import_demo' );

		$created = Demo_Content::import();
		wp_safe_redirect( admin_url( 'tools.php?page=' . self::PAGE . '&imported=' . $created ) );
		exit;
	}
}

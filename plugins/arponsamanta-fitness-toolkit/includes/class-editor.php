<?php
/**
 * Editor sidebar panels for exercise, gear and supplement fields.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Editor {

	public static function enqueue(): void {
		$asset_file = FITBULL_CORE_DIR . 'build/editor/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_script(
			'arponsamanta-fitness-toolkit-editor',
			FITBULL_CORE_URL . 'build/editor/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
		wp_set_script_translations( 'arponsamanta-fitness-toolkit-editor', 'arponsamanta-fitness-toolkit' );
	}
}

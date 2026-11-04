<?php
/**
 * WP-CLI commands.
 *
 *   wp fitbull seed      Load demo exercises, recipes, gear, supplements, articles, pages and a menu.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

use WP_CLI;

class CLI {

	/**
	 * Load demo content from data/demo-content.json.
	 *
	 * Safe to run more than once: items that already exist (by slug) are skipped.
	 *
	 * ## EXAMPLES
	 *
	 *     wp fitbull seed
	 *
	 * @when after_wp_load
	 */
	public function seed(): void {
		$created = Demo_Content::import();
		WP_CLI::success( sprintf( 'Seeded %d items. Existing items were left alone.', $created ) );
	}
}

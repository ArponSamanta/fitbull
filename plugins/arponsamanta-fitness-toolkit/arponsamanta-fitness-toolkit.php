<?php
/**
 * Plugin Name:       ArponSamanta Fitness Toolkit
 * Description:       Exercise library, workout plan generator, Indian recipes and meal planner, macro and 1RM calculators, progress log, gear and supplement guides.
 * Version:           1.0.2
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Arpon Samanta
 * Author URI:        https://github.com/ArponSamanta
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       arponsamanta-fitness-toolkit
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

define( 'FITBULL_CORE_VERSION', '1.0.2' );
define( 'FITBULL_CORE_FILE', __FILE__ );
define( 'FITBULL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FITBULL_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FITBULL_CORE_DIR . 'includes/class-macro-calculator.php';
require_once FITBULL_CORE_DIR . 'includes/class-plan-generator.php';
require_once FITBULL_CORE_DIR . 'includes/class-meal-planner.php';
require_once FITBULL_CORE_DIR . 'includes/class-content-types.php';
require_once FITBULL_CORE_DIR . 'includes/class-meta.php';
require_once FITBULL_CORE_DIR . 'includes/class-progress-log.php';
require_once FITBULL_CORE_DIR . 'includes/class-rest-controller.php';
require_once FITBULL_CORE_DIR . 'includes/class-rest-planner.php';
require_once FITBULL_CORE_DIR . 'includes/class-rest-logs.php';
require_once FITBULL_CORE_DIR . 'includes/class-blocks.php';
require_once FITBULL_CORE_DIR . 'includes/class-editor.php';
require_once FITBULL_CORE_DIR . 'includes/class-demo-content.php';
require_once FITBULL_CORE_DIR . 'includes/class-admin.php';

// Data model and blocks.
add_action( 'init', array( 'Fitbull\\Core\\Content_Types', 'register' ) );
add_action( 'init', array( 'Fitbull\\Core\\Meta', 'register' ) );
add_action( 'init', array( 'Fitbull\\Core\\Blocks', 'register' ) );
add_action( 'enqueue_block_editor_assets', array( 'Fitbull\\Core\\Editor', 'enqueue' ) );

// REST API.
add_action(
	'rest_api_init',
	static function () {
		( new Fitbull\Core\Rest_Controller() )->register_routes();
		( new Fitbull\Core\Rest_Planner() )->register_routes();
		( new Fitbull\Core\Rest_Logs() )->register_routes();
	}
);

// Progress log table, privacy tools and cleanup.
add_action( 'plugins_loaded', array( 'Fitbull\\Core\\Progress_Log', 'maybe_upgrade' ) );
add_filter( 'wp_privacy_personal_data_exporters', array( 'Fitbull\\Core\\Progress_Log', 'register_exporter' ) );
add_filter( 'wp_privacy_personal_data_erasers', array( 'Fitbull\\Core\\Progress_Log', 'register_eraser' ) );
add_action( 'deleted_user', array( 'Fitbull\\Core\\Progress_Log', 'delete_all_for_user' ) );

// Admin: demo content importer under Tools.
add_action( 'admin_menu', array( 'Fitbull\\Core\\Admin', 'register_page' ) );
add_action( 'admin_post_fitbull_import_demo', array( 'Fitbull\\Core\\Admin', 'handle_import' ) );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'Fitbull\\Core\\Admin', 'action_links' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once FITBULL_CORE_DIR . 'includes/class-cli.php';
	WP_CLI::add_command( 'fitness-toolkit', 'Fitbull\\Core\\CLI' );
}

register_activation_hook(
	__FILE__,
	static function () {
		Fitbull\Core\Progress_Log::install();
		Fitbull\Core\Content_Types::register();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

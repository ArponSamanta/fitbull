<?php
/**
 * Plugin Name:       Fitbull Core
 * Plugin URI:        https://github.com/ArponSamanta/fitbull
 * Description:       Content types, REST API and interactive blocks (macro calculator, exercise library) for the Fitbull fitness site.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Arpon Samanta
 * Author URI:        https://github.com/ArponSamanta
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fitbull-core
 *
 * @package Fitbull\Core
 */

defined( 'ABSPATH' ) || exit;

define( 'FITBULL_CORE_VERSION', '0.1.0' );
define( 'FITBULL_CORE_FILE', __FILE__ );
define( 'FITBULL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FITBULL_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FITBULL_CORE_DIR . 'includes/class-macro-calculator.php';
require_once FITBULL_CORE_DIR . 'includes/class-content-types.php';
require_once FITBULL_CORE_DIR . 'includes/class-meta.php';
require_once FITBULL_CORE_DIR . 'includes/class-rest-controller.php';
require_once FITBULL_CORE_DIR . 'includes/class-blocks.php';
require_once FITBULL_CORE_DIR . 'includes/class-editor.php';

add_action( 'init', array( 'Fitbull\\Core\\Content_Types', 'register' ) );
add_action( 'init', array( 'Fitbull\\Core\\Meta', 'register' ) );
add_action( 'init', array( 'Fitbull\\Core\\Blocks', 'register' ) );
add_action( 'rest_api_init', array( new Fitbull\Core\Rest_Controller(), 'register_routes' ) );
add_action( 'enqueue_block_editor_assets', array( 'Fitbull\\Core\\Editor', 'enqueue' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once FITBULL_CORE_DIR . 'includes/class-cli.php';
	WP_CLI::add_command( 'fitbull', 'Fitbull\\Core\\CLI' );
}

register_activation_hook(
	__FILE__,
	static function () {
		Fitbull\Core\Content_Types::register();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

<?php
/**
 * Unit tests cover code with no WordPress dependency, so no WP bootstrap is needed.
 */
// The plugin's classes exit without ABSPATH; give them one so they load without WordPress.
define( 'ABSPATH', __DIR__ . '/' );

// Exception messages are escaped with esc_html(); stand in for it here.
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

require_once dirname( __DIR__, 2 ) . '/includes/class-macro-calculator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-plan-generator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-meal-planner.php';

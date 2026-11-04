<?php
/**
 * Unit tests cover code with no WordPress dependency, so no WP bootstrap is needed.
 */
// The plugin's classes exit without ABSPATH; give them one so they load without WordPress.
define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__, 2 ) . '/includes/class-macro-calculator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-plan-generator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-meal-planner.php';

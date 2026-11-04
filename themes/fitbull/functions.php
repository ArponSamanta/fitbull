<?php
/**
 * Fitbull theme setup.
 *
 * @package Fitbull
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the small stylesheet for things theme.json cannot express
 * (cards, chips, hover states), on the front end and in the editor.
 */
function fitbull_enqueue_styles() {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'fitbull', get_theme_file_uri( 'assets/css/theme.css' ), array(), $version );
}
add_action( 'wp_enqueue_scripts', 'fitbull_enqueue_styles' );

function fitbull_editor_styles() {
	add_editor_style( 'assets/css/theme.css' );
}
add_action( 'after_setup_theme', 'fitbull_editor_styles' );

/**
 * Pattern category for the theme's homepage sections.
 */
function fitbull_pattern_categories() {
	register_block_pattern_category(
		'fitbull',
		array( 'label' => __( 'Fitbull', 'fitbull' ) )
	);
}
add_action( 'init', 'fitbull_pattern_categories' );

/**
 * Block styles used across templates and patterns.
 */
function fitbull_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'ghost',
			'label' => __( 'Ghost', 'fitbull' ),
		)
	);
	register_block_style(
		'core/group',
		array(
			'name'  => 'card',
			'label' => __( 'Card', 'fitbull' ),
		)
	);
	register_block_style(
		'core/paragraph',
		array(
			'name'  => 'eyebrow',
			'label' => __( 'Eyebrow', 'fitbull' ),
		)
	);
}
add_action( 'init', 'fitbull_block_styles' );

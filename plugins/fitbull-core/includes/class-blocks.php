<?php
/**
 * Registers the plugin's blocks from their built block.json files, plus
 * the shared "fitbull-core-ui" stylesheet the blocks list as a style.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Blocks {

	public const BLOCKS = array(
		'macro-calculator',
		'one-rep-max',
		'exercise-library',
		'exercise-details',
		'workout-plan',
		'recipe-finder',
		'recipe-details',
		'meal-planner',
		'progress-log',
		'gear-kit',
		'item-facts',
	);

	public static function register(): void {
		wp_register_style(
			'fitbull-core-ui',
			FITBULL_CORE_URL . 'assets/ui.css',
			array(),
			FITBULL_CORE_VERSION
		);

		foreach ( self::BLOCKS as $block ) {
			$dir = FITBULL_CORE_DIR . 'build/blocks/' . $block;
			if ( file_exists( $dir . '/block.json' ) ) {
				register_block_type( $dir );
			}
		}
	}

	/**
	 * Props for a React mount point, printed as a data attribute.
	 */
	public static function props_attribute( string $name, array $props ): string {
		return sprintf( 'data-%s="%s"', esc_attr( $name ), esc_attr( wp_json_encode( $props ) ) );
	}
}

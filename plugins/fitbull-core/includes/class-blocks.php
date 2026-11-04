<?php
/**
 * Registers the plugin's blocks from their built block.json files.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

class Blocks {

	public const BLOCKS = array( 'macro-calculator', 'exercise-library', 'exercise-details' );

	public static function register(): void {
		foreach ( self::BLOCKS as $block ) {
			$dir = FITBULL_CORE_DIR . 'build/blocks/' . $block;
			if ( file_exists( $dir . '/block.json' ) ) {
				register_block_type( $dir );
			}
		}
	}
}

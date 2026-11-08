<?php
/**
 * Registers the plugin's blocks from their built block.json files, plus
 * the shared "arponsamanta-fitness-toolkit-ui" stylesheet the blocks list as a style.
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

	/**
	 * Tags allowed in an exercise's video embed: the iframe that
	 * YouTube and Vimeo return from oEmbed, and nothing else.
	 */
	public const EMBED_HTML = array(
		'iframe' => array(
			'src'             => true,
			'title'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
			'loading'         => true,
		),
	);

	public static function register(): void {
		wp_register_style(
			'arponsamanta-fitness-toolkit-ui',
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
}

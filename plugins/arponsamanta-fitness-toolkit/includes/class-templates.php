<?php
/**
 * Block templates for the plugin's post types (single and archive views of
 * exercises, recipes, gear and supplements).
 *
 * They live in the plugin, not the theme, because they are built on the
 * plugin's blocks. A block theme can still override any of them by
 * shipping a template file with the same slug.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Templates {

	/**
	 * Template slug => array( title, description ).
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	private static function templates(): array {
		return array(
			'single-fb_exercise'    => array( __( 'Single exercise', 'arponsamanta-fitness-toolkit' ), __( 'An exercise with its cues, common mistakes and muscles.', 'arponsamanta-fitness-toolkit' ) ),
			'archive-fb_exercise'   => array( __( 'Exercise library', 'arponsamanta-fitness-toolkit' ), __( 'All exercises, with filters.', 'arponsamanta-fitness-toolkit' ) ),
			'single-fb_recipe'      => array( __( 'Single recipe', 'arponsamanta-fitness-toolkit' ), __( 'A recipe with macros, ingredients and method.', 'arponsamanta-fitness-toolkit' ) ),
			'archive-fb_recipe'     => array( __( 'Recipe finder', 'arponsamanta-fitness-toolkit' ), __( 'All recipes, with diet and meal filters.', 'arponsamanta-fitness-toolkit' ) ),
			'single-fb_equipment'   => array( __( 'Single gear item', 'arponsamanta-fitness-toolkit' ), __( 'A piece of gear with its budget tier and price.', 'arponsamanta-fitness-toolkit' ) ),
			'archive-fb_equipment'  => array( __( 'Gear archive', 'arponsamanta-fitness-toolkit' ), __( 'The home gym kit builder.', 'arponsamanta-fitness-toolkit' ) ),
			'single-fb_supplement'  => array( __( 'Single supplement', 'arponsamanta-fitness-toolkit' ), __( 'A supplement with its evidence rating and verdict.', 'arponsamanta-fitness-toolkit' ) ),
			'archive-fb_supplement' => array( __( 'Supplements archive', 'arponsamanta-fitness-toolkit' ), __( 'All supplements, rated by evidence.', 'arponsamanta-fitness-toolkit' ) ),
		);
	}

	public static function register(): void {
		// register_block_template() arrived in WordPress 6.7.
		if ( ! function_exists( 'register_block_template' ) || ! wp_is_block_theme() ) {
			return;
		}

		foreach ( self::templates() as $slug => list( $title, $description ) ) {
			$file = FITBULL_CORE_DIR . 'templates/' . $slug . '.html';
			if ( ! is_readable( $file ) ) {
				continue;
			}
			register_block_template(
				'arponsamanta-fitness-toolkit//' . $slug,
				array(
					'title'       => $title,
					'description' => $description,
					'content'     => (string) file_get_contents( $file ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
				)
			);
		}
	}
}

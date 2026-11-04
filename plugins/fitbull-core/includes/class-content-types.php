<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Content_Types {

	public const EXERCISE   = 'fb_exercise';
	public const EQUIPMENT  = 'fb_equipment';
	public const SUPPLEMENT = 'fb_supplement';
	public const RECIPE     = 'fb_recipe';
	public const MUSCLE     = 'fb_muscle';
	public const GEAR_TYPE  = 'fb_equipment_type';
	public const DIET       = 'fb_diet';
	public const MEAL       = 'fb_meal';

	public static function register(): void {
		$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' );

		$types = array(
			self::EXERCISE   => array( __( 'Exercise', 'fitbull-core' ), __( 'Exercises', 'fitbull-core' ), 'exercises', 'dashicons-universal-access' ),
			self::RECIPE     => array( __( 'Recipe', 'fitbull-core' ), __( 'Recipes', 'fitbull-core' ), 'recipes', 'dashicons-carrot' ),
			self::EQUIPMENT  => array( __( 'Gear item', 'fitbull-core' ), __( 'Gear', 'fitbull-core' ), 'gear', 'dashicons-cart' ),
			self::SUPPLEMENT => array( __( 'Supplement', 'fitbull-core' ), __( 'Supplements', 'fitbull-core' ), 'supplements', 'dashicons-beaker' ),
		);

		foreach ( $types as $type => list( $singular, $plural, $slug, $icon ) ) {
			register_post_type(
				$type,
				array(
					'labels'       => self::labels( $singular, $plural ),
					'public'       => true,
					'show_in_rest' => true,
					'menu_icon'    => $icon,
					'has_archive'  => $slug,
					'rewrite'      => array( 'slug' => $slug ),
					'supports'     => $supports,
				)
			);
		}

		$taxonomies = array(
			self::MUSCLE    => array( array( self::EXERCISE ), __( 'Muscles', 'fitbull-core' ), __( 'Muscle', 'fitbull-core' ), 'muscle' ),
			self::GEAR_TYPE => array( array( self::EXERCISE, self::EQUIPMENT ), __( 'Equipment', 'fitbull-core' ), __( 'Equipment', 'fitbull-core' ), 'equipment' ),
			self::DIET      => array( array( self::RECIPE ), __( 'Diets', 'fitbull-core' ), __( 'Diet', 'fitbull-core' ), 'diet' ),
			self::MEAL      => array( array( self::RECIPE ), __( 'Meals', 'fitbull-core' ), __( 'Meal', 'fitbull-core' ), 'meal' ),
		);

		foreach ( $taxonomies as $taxonomy => list( $object_types, $plural, $singular, $slug ) ) {
			register_taxonomy(
				$taxonomy,
				$object_types,
				array(
					'labels'            => array(
						'name'          => $plural,
						'singular_name' => $singular,
					),
					'public'            => true,
					'hierarchical'      => true,
					'show_in_rest'      => true,
					'show_admin_column' => true,
					'rewrite'           => array( 'slug' => $slug ),
				)
			);
		}
	}

	/**
	 * Diets a visitor can eat, from most to least restrictive. A vegetarian
	 * can eat vegan recipes, an eggetarian can eat both, and so on.
	 *
	 * @return array<string,string[]> Diet slug => allowed recipe diet slugs.
	 */
	public static function diet_includes(): array {
		return array(
			'vegan'      => array( 'vegan' ),
			'veg'        => array( 'veg', 'vegan' ),
			'eggetarian' => array( 'eggetarian', 'veg', 'vegan' ),
			'non-veg'    => array( 'non-veg', 'eggetarian', 'veg', 'vegan' ),
		);
	}

	private static function labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			/* translators: %s: singular post type name. */
			'add_new_item'  => sprintf( __( 'Add new %s', 'fitbull-core' ), strtolower( $singular ) ),
			/* translators: %s: singular post type name. */
			'edit_item'     => sprintf( __( 'Edit %s', 'fitbull-core' ), strtolower( $singular ) ),
			/* translators: %s: plural post type name. */
			'all_items'     => sprintf( __( 'All %s', 'fitbull-core' ), strtolower( $plural ) ),
			/* translators: %s: plural post type name. */
			'search_items'  => sprintf( __( 'Search %s', 'fitbull-core' ), strtolower( $plural ) ),
		);
	}
}

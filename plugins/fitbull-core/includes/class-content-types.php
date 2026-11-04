<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

class Content_Types {

	public const EXERCISE   = 'fb_exercise';
	public const EQUIPMENT  = 'fb_equipment';
	public const SUPPLEMENT = 'fb_supplement';
	public const MUSCLE     = 'fb_muscle';
	public const GEAR_TYPE  = 'fb_equipment_type';

	public static function register(): void {
		$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' );

		register_post_type(
			self::EXERCISE,
			array(
				'labels'       => self::labels( __( 'Exercise', 'fitbull-core' ), __( 'Exercises', 'fitbull-core' ) ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-universal-access',
				'has_archive'  => 'exercises',
				'rewrite'      => array( 'slug' => 'exercises' ),
				'supports'     => $supports,
			)
		);

		register_post_type(
			self::EQUIPMENT,
			array(
				'labels'       => self::labels( __( 'Gear item', 'fitbull-core' ), __( 'Gear', 'fitbull-core' ) ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-cart',
				'has_archive'  => 'gear',
				'rewrite'      => array( 'slug' => 'gear' ),
				'supports'     => $supports,
			)
		);

		register_post_type(
			self::SUPPLEMENT,
			array(
				'labels'       => self::labels( __( 'Supplement', 'fitbull-core' ), __( 'Supplements', 'fitbull-core' ) ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-beaker',
				'has_archive'  => 'supplements',
				'rewrite'      => array( 'slug' => 'supplements' ),
				'supports'     => $supports,
			)
		);

		register_taxonomy(
			self::MUSCLE,
			array( self::EXERCISE ),
			array(
				'labels'            => array(
					'name'          => __( 'Muscles', 'fitbull-core' ),
					'singular_name' => __( 'Muscle', 'fitbull-core' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'muscle' ),
			)
		);

		register_taxonomy(
			self::GEAR_TYPE,
			array( self::EXERCISE, self::EQUIPMENT ),
			array(
				'labels'            => array(
					'name'          => __( 'Equipment', 'fitbull-core' ),
					'singular_name' => __( 'Equipment', 'fitbull-core' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'equipment' ),
			)
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

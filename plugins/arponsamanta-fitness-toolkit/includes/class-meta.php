<?php
/**
 * Structured fields for each content type, exposed to the REST API
 * and edited in the block editor sidebar (see src/editor).
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Meta {

	public const DIFFICULTIES = array( 'beginner', 'intermediate', 'advanced' );
	public const EVIDENCE     = array( 'strong', 'moderate', 'weak', 'none' );
	public const BUDGET_TIERS = array( 'starter', 'serious', 'full' );

	/** Movement patterns used by the workout plan generator. */
	public const PATTERNS = array( 'squat', 'hinge', 'lunge', 'push_h', 'push_v', 'pull_h', 'pull_v', 'core', 'arms' );

	public static function register(): void {
		$exercise = Content_Types::EXERCISE;

		self::string( $exercise, 'fb_difficulty', self::DIFFICULTIES, 'beginner' );
		self::string( $exercise, 'fb_pattern', self::PATTERNS, 'core' );
		self::boolean( $exercise, 'fb_timed' );
		self::string( $exercise, 'fb_video_url', null, '', 'esc_url_raw' );
		self::list_of_objects(
			$exercise,
			'fb_steps',
			array(
				'title' => array( 'type' => 'string' ),
				'text'  => array( 'type' => 'string' ),
			)
		);
		self::list_of_objects(
			$exercise,
			'fb_mistakes',
			array(
				'title' => array( 'type' => 'string' ),
				'fix'   => array( 'type' => 'string' ),
			)
		);
		self::list_of_strings( $exercise, 'fb_secondary_muscles' );

		$recipe = Content_Types::RECIPE;
		foreach ( array( 'fb_calories', 'fb_protein_g', 'fb_carbs_g', 'fb_fat_g', 'fb_prep_minutes' ) as $key ) {
			self::integer( $recipe, $key, 0 );
		}
		self::integer( $recipe, 'fb_servings', 1 );
		self::list_of_objects(
			$recipe,
			'fb_ingredients',
			array(
				'item' => array( 'type' => 'string' ),
				'qty'  => array( 'type' => 'string' ),
			)
		);
		self::list_of_strings( $recipe, 'fb_method' );

		$gear = Content_Types::EQUIPMENT;
		self::integer( $gear, 'fb_price_inr', 0 );
		self::integer( $gear, 'fb_priority', 10 );
		self::string( $gear, 'fb_buy_url', null, '', 'esc_url_raw' );
		self::string( $gear, 'fb_budget_tier', self::BUDGET_TIERS, 'starter' );

		$supplement = Content_Types::SUPPLEMENT;
		self::string( $supplement, 'fb_evidence', self::EVIDENCE, 'none' );
		self::string( $supplement, 'fb_verdict', null, '' );
	}

	private static function integer( string $post_type, string $key, int $default ): void {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => $default,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => array( 'schema' => array( 'type' => 'integer', 'minimum' => 0 ) ),
			)
		);
	}

	private static function boolean( string $post_type, string $key ): void {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
	}

	private static function list_of_strings( string $post_type, string $key ): void {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'          => 'array',
				'single'        => true,
				'default'       => array(),
				'auth_callback' => array( __CLASS__, 'can_edit' ),
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
			)
		);
	}

	public static function can_edit( $allowed, $meta_key, $post_id ): bool {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Register a single string field, optionally limited to a set of values.
	 */
	private static function string( string $post_type, string $key, ?array $allowed, string $default, string $sanitize = 'sanitize_text_field' ): void {
		$schema = array( 'type' => 'string' );
		if ( $allowed ) {
			$schema['enum'] = $allowed;
		}

		register_post_meta(
			$post_type,
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => $default,
				'sanitize_callback' => static function ( $value ) use ( $allowed, $default, $sanitize ) {
					$value = call_user_func( $sanitize, (string) $value );
					return ( $allowed && ! in_array( $value, $allowed, true ) ) ? $default : $value;
				},
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => array( 'schema' => $schema ),
			)
		);
	}

	/**
	 * Register an ordered list of small objects (steps, mistakes).
	 */
	private static function list_of_objects( string $post_type, string $key, array $properties ): void {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'          => 'array',
				'single'        => true,
				'default'       => array(),
				'auth_callback' => array( __CLASS__, 'can_edit' ),
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'                 => 'object',
							'properties'           => $properties,
							'additionalProperties' => false,
						),
					),
				),
			)
		);
	}
}

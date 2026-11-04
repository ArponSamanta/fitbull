<?php
/**
 * Structured fields for each content type, exposed to the REST API
 * and edited in the block editor sidebar (see src/editor).
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

class Meta {

	public const DIFFICULTIES = array( 'beginner', 'intermediate', 'advanced' );
	public const EVIDENCE     = array( 'strong', 'moderate', 'weak', 'none' );
	public const BUDGET_TIERS = array( 'starter', 'serious', 'full' );

	public static function register(): void {
		$exercise = Content_Types::EXERCISE;

		self::string( $exercise, 'fb_difficulty', self::DIFFICULTIES, 'beginner' );
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
		register_post_meta(
			$exercise,
			'fb_secondary_muscles',
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

		$gear = Content_Types::EQUIPMENT;
		register_post_meta(
			$gear,
			'fb_price_inr',
			array(
				'type'              => 'integer',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => true,
			)
		);
		self::string( $gear, 'fb_buy_url', null, '', 'esc_url_raw' );
		self::string( $gear, 'fb_budget_tier', self::BUDGET_TIERS, 'starter' );

		$supplement = Content_Types::SUPPLEMENT;
		self::string( $supplement, 'fb_evidence', self::EVIDENCE, 'none' );
		self::string( $supplement, 'fb_verdict', null, '' );
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

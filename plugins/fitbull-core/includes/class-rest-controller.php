<?php
/**
 * REST API: /wp-json/fitbull/v1
 *
 *   GET /exercises  Filterable, paginated exercise list, easiest first.
 *   GET /filters    Muscle and equipment terms that have exercises.
 *   GET /macros     Calorie and macro targets from body stats.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Rest_Controller {

	public const NAMESPACE = 'fitbull/v1';

	/** Upper bound on exercises sorted in memory; the library is curated, not user-generated. */
	private const MAX_EXERCISES = 500;

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/exercises',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_exercises' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'muscle'     => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'equipment'  => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'difficulty' => array(
						'type' => 'string',
						'enum' => array_merge( array( '' ), Meta::DIFFICULTIES ),
					),
					'search'     => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'       => array(
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					),
					'per_page'   => array(
						'type'    => 'integer',
						'default' => 12,
						'minimum' => 1,
						'maximum' => 50,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/filters',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_filters' ),
				'permission_callback' => '__return_true',
			)
		);

		$limits = Macro_Calculator::load_config()['limits'];
		register_rest_route(
			self::NAMESPACE,
			'/macros',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_macros' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'sex'       => array(
						'type'     => 'string',
						'enum'     => array( 'male', 'female' ),
						'required' => true,
					),
					'age'       => array(
						'type'     => 'number',
						'minimum'  => $limits['age'][0],
						'maximum'  => $limits['age'][1],
						'required' => true,
					),
					'height_cm' => array(
						'type'     => 'number',
						'minimum'  => $limits['heightCm'][0],
						'maximum'  => $limits['heightCm'][1],
						'required' => true,
					),
					'weight_kg' => array(
						'type'     => 'number',
						'minimum'  => $limits['weightKg'][0],
						'maximum'  => $limits['weightKg'][1],
						'required' => true,
					),
					'activity'  => array(
						'type'    => 'string',
						'enum'    => array_keys( Macro_Calculator::load_config()['activity'] ),
						'default' => 'moderate',
					),
					'goal'      => array(
						'type'    => 'string',
						'enum'    => array_keys( Macro_Calculator::load_config()['goal'] ),
						'default' => 'maintain',
					),
				),
			)
		);
	}

	public function get_exercises( WP_REST_Request $request ): WP_REST_Response {
		$result   = self::query_exercises(
			array(
				'muscle'     => (string) $request['muscle'],
				'equipment'  => (string) $request['equipment'],
				'difficulty' => (string) $request['difficulty'],
				'search'     => (string) $request['search'],
			),
			(int) $request['page'],
			(int) $request['per_page']
		);
		$response = new WP_REST_Response( $result );
		$response->header( 'X-WP-Total', (string) $result['total'] );
		$response->header( 'X-WP-TotalPages', (string) $result['pages'] );
		return $response;
	}

	/**
	 * Shared by the REST route and the block's server render, so the first
	 * paint and every filter change show the same list.
	 *
	 * @return array{items:array<int,array<string,mixed>>,total:int,pages:int,page:int}
	 */
	public static function query_exercises( array $filters, int $page = 1, int $per_page = 12 ): array {
		$tax_query = array();
		if ( ! empty( $filters['muscle'] ) ) {
			$tax_query[] = array(
				'taxonomy' => Content_Types::MUSCLE,
				'field'    => 'slug',
				'terms'    => $filters['muscle'],
			);
		}
		if ( ! empty( $filters['equipment'] ) ) {
			$tax_query[] = array(
				'taxonomy' => Content_Types::GEAR_TYPE,
				'field'    => 'slug',
				'terms'    => $filters['equipment'],
			);
		}

		$args = array(
			'post_type'      => Content_Types::EXERCISE,
			'post_status'    => 'publish',
			'posts_per_page' => self::MAX_EXERCISES,
			'no_found_rows'  => true,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		if ( $tax_query ) {
			$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( ! empty( $filters['difficulty'] ) ) {
			$args['meta_key']   = 'fb_difficulty'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['meta_value'] = $filters['difficulty']; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		$posts = get_posts( $args );

		// Easiest first, then alphabetical (get_posts already sorted by title).
		$rank = array_flip( Meta::DIFFICULTIES );
		usort(
			$posts,
			static function ( WP_Post $a, WP_Post $b ) use ( $rank ) {
				$ra = $rank[ get_post_meta( $a->ID, 'fb_difficulty', true ) ] ?? 0;
				$rb = $rank[ get_post_meta( $b->ID, 'fb_difficulty', true ) ] ?? 0;
				return $ra <=> $rb;
			}
		);

		$total = count( $posts );
		$pages = max( 1, (int) ceil( $total / $per_page ) );
		$page  = min( max( 1, $page ), $pages );
		$slice = array_slice( $posts, ( $page - 1 ) * $per_page, $per_page );

		return array(
			'items' => array_map( array( __CLASS__, 'format_exercise' ), $slice ),
			'total' => $total,
			'pages' => $pages,
			'page'  => $page,
		);
	}

	public static function format_exercise( WP_Post $post ): array {
		return array(
			'id'         => $post->ID,
			'title'      => get_the_title( $post ),
			'link'       => get_permalink( $post ),
			'excerpt'    => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'difficulty' => get_post_meta( $post->ID, 'fb_difficulty', true ) ?: 'beginner',
			'muscles'    => self::term_names( $post->ID, Content_Types::MUSCLE ),
			'equipment'  => self::term_names( $post->ID, Content_Types::GEAR_TYPE ),
			'image'      => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: null,
		);
	}

	public function get_filters(): WP_REST_Response {
		return new WP_REST_Response( self::filters() );
	}

	public static function filters(): array {
		$map = static function ( string $taxonomy ): array {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
				)
			);
			if ( is_wp_error( $terms ) ) {
				return array();
			}
			return array_values(
				array_map(
					static fn( $term ) => array(
						'slug'  => $term->slug,
						'name'  => $term->name,
						'count' => (int) $term->count,
					),
					$terms
				)
			);
		};

		return array(
			'muscles'      => $map( Content_Types::MUSCLE ),
			'equipment'    => $map( Content_Types::GEAR_TYPE ),
			'difficulties' => Meta::DIFFICULTIES,
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_macros( WP_REST_Request $request ) {
		try {
			$result = ( new Macro_Calculator() )->calculate(
				array(
					'sex'       => $request['sex'],
					'age'       => $request['age'],
					'height_cm' => $request['height_cm'],
					'weight_kg' => $request['weight_kg'],
					'activity'  => $request['activity'],
					'goal'      => $request['goal'],
				)
			);
		} catch ( \InvalidArgumentException $e ) {
			return new WP_Error( 'fitbull_invalid_input', $e->getMessage(), array( 'status' => 400 ) );
		}
		return new WP_REST_Response( $result );
	}

	private static function term_names( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		return ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
	}
}

<?php
/**
 * REST API for the planners: /wp-json/fitbull/v1
 *
 *   GET /recipes       Filterable recipes, highest protein first.
 *   GET /workout-plan  Weekly plan from goal, days, level and equipment.
 *   GET /meal-plan     One day of meals near a calorie and protein target.
 *
 * WordPress loading lives here; the planning rules live in the pure
 * Plan_Generator and Meal_Planner classes.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Rest_Planner {

	private const MAX_ITEMS = 500;

	public function register_routes(): void {
		$diets = array_keys( Content_Types::diet_includes() );

		register_rest_route(
			Rest_Controller::NAMESPACE,
			'/recipes',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_recipes' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'diet'        => array(
						'type' => 'string',
						'enum' => array_merge( array( '' ), $diets ),
					),
					'meal'        => array(
						'type' => 'string',
						'enum' => array_merge( array( '' ), Meal_Planner::MEALS ),
					),
					'min_protein' => array(
						'type'    => 'integer',
						'minimum' => 0,
						'maximum' => 100,
						'default' => 0,
					),
					'search'      => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'        => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'    => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 12,
					),
				),
			)
		);

		register_rest_route(
			Rest_Controller::NAMESPACE,
			'/workout-plan',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_workout_plan' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'goal'      => array(
						'type'     => 'string',
						'enum'     => Plan_Generator::GOALS,
						'required' => true,
					),
					'days'      => array(
						'type'     => 'integer',
						'minimum'  => 2,
						'maximum'  => 5,
						'required' => true,
					),
					'level'     => array(
						'type'    => 'string',
						'enum'    => Plan_Generator::LEVELS,
						'default' => 'beginner',
					),
					'equipment' => array(
						'type'    => 'array',
						'items'   => array( 'type' => 'string' ),
						'default' => array(),
					),
				),
			)
		);

		register_rest_route(
			Rest_Controller::NAMESPACE,
			'/meal-plan',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_meal_plan' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'calories'      => array(
						'type'     => 'integer',
						'minimum'  => 1000,
						'maximum'  => 5000,
						'required' => true,
					),
					'protein_g'     => array(
						'type'     => 'integer',
						'minimum'  => 0,
						'maximum'  => 300,
						'required' => true,
					),
					'diet'          => array(
						'type'    => 'string',
						'enum'    => $diets,
						'default' => 'veg',
					),
					'include_snack' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'variant'       => array(
						'type'    => 'integer',
						'minimum' => 0,
						'maximum' => 100,
						'default' => 0,
					),
				),
			)
		);
	}

	public function get_recipes( WP_REST_Request $request ): WP_REST_Response {
		$result = self::query_recipes(
			array(
				'diet'        => (string) $request['diet'],
				'meal'        => (string) $request['meal'],
				'min_protein' => (int) $request['min_protein'],
				'search'      => (string) $request['search'],
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
	 * Shared by the REST route, the recipe finder's server render and the
	 * meal planner.
	 *
	 * @return array{items:array,total:int,pages:int,page:int}
	 */
	public static function query_recipes( array $filters, int $page = 1, int $per_page = 12 ): array {
		$tax_query = array();
		if ( ! empty( $filters['diet'] ) ) {
			$tax_query[] = array(
				'taxonomy' => Content_Types::DIET,
				'field'    => 'slug',
				'terms'    => Content_Types::diet_includes()[ $filters['diet'] ] ?? array( $filters['diet'] ),
			);
		}
		if ( ! empty( $filters['meal'] ) ) {
			$tax_query[] = array(
				'taxonomy' => Content_Types::MEAL,
				'field'    => 'slug',
				'terms'    => $filters['meal'],
			);
		}

		$args = array(
			'post_type'      => Content_Types::RECIPE,
			'post_status'    => 'publish',
			'posts_per_page' => self::MAX_ITEMS,
			'no_found_rows'  => true,
		);
		if ( $tax_query ) {
			$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		$items = array_map( array( __CLASS__, 'format_recipe' ), get_posts( $args ) );
		$min   = (int) ( $filters['min_protein'] ?? 0 );
		$items = array_values( array_filter( $items, static fn( $r ) => $r['protein_g'] >= $min ) );
		usort( $items, static fn( $a, $b ) => array( $b['protein_g'], $a['title'] ) <=> array( $a['protein_g'], $b['title'] ) );

		$total = count( $items );
		$pages = max( 1, (int) ceil( $total / $per_page ) );
		$page  = min( max( 1, $page ), $pages );

		return array(
			'items' => array_slice( $items, ( $page - 1 ) * $per_page, $per_page ),
			'total' => $total,
			'pages' => $pages,
			'page'  => $page,
		);
	}

	public static function format_recipe( WP_Post $post ): array {
		$int = static fn( string $key ) => (int) get_post_meta( $post->ID, $key, true );
		return array(
			'id'           => $post->ID,
			'title'        => get_the_title( $post ),
			'link'         => get_permalink( $post ),
			'excerpt'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'image'        => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: null,
			'calories'     => $int( 'fb_calories' ),
			'protein_g'    => $int( 'fb_protein_g' ),
			'carbs_g'      => $int( 'fb_carbs_g' ),
			'fat_g'        => $int( 'fb_fat_g' ),
			'prep_minutes' => $int( 'fb_prep_minutes' ),
			'diets'        => self::terms( $post->ID, Content_Types::DIET, 'name' ),
			'meals'        => self::terms( $post->ID, Content_Types::MEAL, 'slug' ),
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_workout_plan( WP_REST_Request $request ) {
		try {
			$plan = ( new Plan_Generator() )->generate(
				array(
					'goal'      => $request['goal'],
					'days'      => (int) $request['days'],
					'level'     => $request['level'],
					'equipment' => array_map( 'sanitize_title', (array) $request['equipment'] ),
				),
				self::exercises_for_planner()
			);
		} catch ( \InvalidArgumentException $e ) {
			return new WP_Error( 'fitbull_invalid_input', $e->getMessage(), array( 'status' => 400 ) );
		}
		return new WP_REST_Response( $plan );
	}

	/**
	 * Every published exercise in the shape Plan_Generator expects.
	 */
	public static function exercises_for_planner(): array {
		$posts = get_posts(
			array(
				'post_type'      => Content_Types::EXERCISE,
				'post_status'    => 'publish',
				'posts_per_page' => self::MAX_ITEMS,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		return array_map(
			static fn( WP_Post $post ) => array(
				'id'         => $post->ID,
				'title'      => get_the_title( $post ),
				'link'       => get_permalink( $post ),
				'difficulty' => get_post_meta( $post->ID, 'fb_difficulty', true ) ?: 'beginner',
				'pattern'    => get_post_meta( $post->ID, 'fb_pattern', true ) ?: 'core',
				'timed'      => (bool) get_post_meta( $post->ID, 'fb_timed', true ),
				'equipment'  => self::terms( $post->ID, Content_Types::GEAR_TYPE, 'slug' ),
			),
			$posts
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_meal_plan( WP_REST_Request $request ) {
		$recipes = self::query_recipes( array( 'diet' => $request['diet'] ), 1, self::MAX_ITEMS )['items'];
		try {
			$plan = ( new Meal_Planner() )->plan(
				array(
					'calories'      => (int) $request['calories'],
					'protein_g'     => (int) $request['protein_g'],
					'include_snack' => (bool) $request['include_snack'],
					'variant'       => (int) $request['variant'],
				),
				$recipes
			);
		} catch ( \InvalidArgumentException $e ) {
			return new WP_Error( 'fitbull_invalid_input', $e->getMessage(), array( 'status' => 400 ) );
		}
		return new WP_REST_Response( $plan );
	}

	private static function terms( int $post_id, string $taxonomy, string $field ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		return ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, $field ) : array();
	}
}

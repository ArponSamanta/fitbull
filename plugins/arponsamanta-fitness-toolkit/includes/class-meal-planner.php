<?php
/**
 * Builds a one-day meal plan from recipes that lands close to a calorie
 * and protein target, by choosing a recipe per meal and a portion size.
 *
 * Pure PHP (no WordPress calls), so it is unit tested on its own.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

// Unit tests define ABSPATH in tests/php/bootstrap.php, so this class loads without WordPress.
defined( 'ABSPATH' ) || exit;

class Meal_Planner {

	public const MEALS = array( 'breakfast', 'lunch', 'dinner', 'snack' );

	/** Portion sizes the planner may choose, in servings. */
	private const PORTIONS = array( 0.5, 1.0, 1.5, 2.0 );

	/**
	 * Recipes considered per meal. Keeps the search small (6^4 combinations
	 * x 4^4 portion sets) however many recipes the site has.
	 */
	private const MAX_PER_MEAL = 6;

	/** How many different plans "shuffle" cycles through. */
	private const VARIANTS = 5;

	/**
	 * @param array{calories:int,protein_g:int,include_snack?:bool,variant?:int} $target
	 * @param array<int,array{id:int,title:string,link:string,meals:string[],calories:int,protein_g:int,carbs_g:int,fat_g:int}> $recipes
	 * @return array{meals:array,totals:array,target:array,missing:string[]}
	 * @throws \InvalidArgumentException On an impossible target.
	 */
	public function plan( array $target, array $recipes ): array {
		$calories = (int) ( $target['calories'] ?? 0 );
		$protein  = (int) ( $target['protein_g'] ?? 0 );
		if ( $calories < 1000 || $calories > 5000 ) {
			throw new \InvalidArgumentException( esc_html( 'Calories must be between 1000 and 5000.' ) );
		}
		if ( $protein < 0 || $protein > 300 ) {
			throw new \InvalidArgumentException( esc_html( 'Protein must be between 0 and 300 g.' ) );
		}

		$slots   = ! empty( $target['include_snack'] ) ? self::MEALS : array( 'breakfast', 'lunch', 'dinner' );
		$options = array();
		$missing = array();
		foreach ( $slots as $slot ) {
			$options[ $slot ] = array_values(
				array_filter( $recipes, static fn( $r ) => in_array( $slot, (array) $r['meals'], true ) && $r['calories'] > 0 )
			);
			if ( ! $options[ $slot ] ) {
				$missing[] = $slot;
				continue;
			}
			// Prefer protein-dense dishes when there are many to choose from.
			usort(
				$options[ $slot ],
				static fn( $a, $b ) => ( $b['protein_g'] / $b['calories'] ) <=> ( $a['protein_g'] / $a['calories'] )
			);
			$options[ $slot ] = array_slice( $options[ $slot ], 0, self::MAX_PER_MEAL );
		}
		$slots = array_values( array_diff( $slots, $missing ) );
		if ( ! $slots ) {
			return $this->empty_plan( $calories, $protein, $missing );
		}

		// Score every combination of one recipe per meal, at its best portions.
		$ranked = array();
		foreach ( $this->combinations( $slots, $options ) as $combo ) {
			$ranked[] = $this->best_portions( $combo, $calories, $protein );
		}
		if ( ! $ranked ) {
			// Too few distinct recipes to fill every meal without repeats.
			return $this->empty_plan( $calories, $protein, $slots );
		}
		usort( $ranked, static fn( $a, $b ) => $a['score'] <=> $b['score'] );

		$choices = array_slice( $ranked, 0, self::VARIANTS );
		$variant = max( 0, (int) ( $target['variant'] ?? 0 ) ) % count( $choices );
		$chosen  = $choices[ $variant ];

		$meals = array();
		foreach ( $chosen['items'] as $i => $item ) {
			$meals[] = array(
				'meal'      => $slots[ $i ],
				'servings'  => $item['servings'],
				'recipe'    => array(
					'id'    => $item['recipe']['id'],
					'title' => $item['recipe']['title'],
					'link'  => $item['recipe']['link'],
				),
				'calories'  => (int) round( $item['recipe']['calories'] * $item['servings'] ),
				'protein_g' => (int) round( $item['recipe']['protein_g'] * $item['servings'] ),
				'carbs_g'   => (int) round( $item['recipe']['carbs_g'] * $item['servings'] ),
				'fat_g'     => (int) round( $item['recipe']['fat_g'] * $item['servings'] ),
			);
		}

		return array(
			'meals'    => $meals,
			'totals'   => $this->totals( $meals ),
			'target'   => array( 'calories' => $calories, 'protein_g' => $protein ),
			'missing'  => $missing,
			'variants' => count( $choices ),
		);
	}

	/**
	 * Cartesian product: every way to pick one recipe for each meal.
	 *
	 * @return \Generator<array<int,array>>
	 */
	private function combinations( array $slots, array $options, int $depth = 0, array $picked = array() ): \Generator {
		if ( count( $slots ) === $depth ) {
			yield $picked;
			return;
		}
		foreach ( $options[ $slots[ $depth ] ] as $recipe ) {
			// Don't serve the same dish twice in one day.
			if ( in_array( $recipe['id'], array_column( $picked, 'id' ), true ) ) {
				continue;
			}
			yield from $this->combinations( $slots, $options, $depth + 1, array_merge( $picked, array( $recipe ) ) );
		}
	}

	/**
	 * Try every portion size per meal and keep the closest fit.
	 * Score = calorie miss + 1.5 × protein shortfall (as fractions of target)
	 *         + a small nudge towards normal one-serving portions.
	 */
	private function best_portions( array $combo, int $calories, int $protein ): array {
		$best = null;
		foreach ( $this->portion_sets( count( $combo ) ) as $portions ) {
			$kcal = 0;
			$prot = 0;
			$odd  = 0;
			foreach ( $combo as $i => $recipe ) {
				$kcal += $recipe['calories'] * $portions[ $i ];
				$prot += $recipe['protein_g'] * $portions[ $i ];
				$odd  += abs( $portions[ $i ] - 1.0 );
			}
			$score = abs( $kcal - $calories ) / $calories
				+ 1.5 * max( 0, $protein - $prot ) / max( 1, $protein )
				+ 0.02 * $odd;
			if ( null === $best || $score < $best['score'] ) {
				$best = array(
					'score'    => $score,
					'portions' => $portions,
				);
			}
		}

		$items = array();
		foreach ( $combo as $i => $recipe ) {
			$items[] = array(
				'recipe'   => $recipe,
				'servings' => $best['portions'][ $i ],
			);
		}
		return array(
			'score' => $best['score'],
			'items' => $items,
		);
	}

	/** @return \Generator<float[]> */
	private function portion_sets( int $count, array $current = array() ): \Generator {
		if ( count( $current ) === $count ) {
			yield $current;
			return;
		}
		foreach ( self::PORTIONS as $portion ) {
			yield from $this->portion_sets( $count, array_merge( $current, array( $portion ) ) );
		}
	}

	private function totals( array $meals ): array {
		$sum = static fn( string $key ) => (int) array_sum( array_column( $meals, $key ) );
		return array(
			'calories'  => $sum( 'calories' ),
			'protein_g' => $sum( 'protein_g' ),
			'carbs_g'   => $sum( 'carbs_g' ),
			'fat_g'     => $sum( 'fat_g' ),
		);
	}

	private function empty_plan( int $calories, int $protein, array $missing ): array {
		return array(
			'meals'    => array(),
			'totals'   => $this->totals( array() ),
			'target'   => array(
				'calories'  => $calories,
				'protein_g' => $protein,
			),
			'missing'  => $missing,
			'variants' => 0,
		);
	}
}

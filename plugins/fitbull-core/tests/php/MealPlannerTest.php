<?php

use Fitbull\Core\Meal_Planner;
use PHPUnit\Framework\TestCase;

final class MealPlannerTest extends TestCase {

	private static function recipes(): array {
		$rows = array(
			array( 1, 'Moong dal chilla', array( 'breakfast' ), 280, 16, 38, 7 ),
			array( 2, 'Poha', array( 'breakfast' ), 330, 8, 52, 10 ),
			array( 3, 'Paneer bhurji with roti', array( 'lunch', 'dinner' ), 520, 28, 44, 26 ),
			array( 4, 'Rajma chawal', array( 'lunch' ), 515, 18, 88, 10 ),
			array( 5, 'Soya chunk curry with roti', array( 'dinner' ), 410, 30, 48, 11 ),
			array( 6, 'Roasted chana', array( 'snack' ), 150, 8, 23, 3 ),
		);
		return array_map(
			static fn( $r ) => array(
				'id'        => $r[0],
				'title'     => $r[1],
				'link'      => '/recipes/' . $r[0],
				'meals'     => $r[2],
				'calories'  => $r[3],
				'protein_g' => $r[4],
				'carbs_g'   => $r[5],
				'fat_g'     => $r[6],
			),
			$rows
		);
	}

	private function plan( array $target ): array {
		return ( new Meal_Planner() )->plan( $target, self::recipes() );
	}

	public function test_hits_the_calorie_target_within_ten_percent(): void {
		foreach ( array( 1600, 2200, 2800 ) as $calories ) {
			$plan = $this->plan(
				array(
					'calories'      => $calories,
					'protein_g'     => 100,
					'include_snack' => true,
				)
			);
			$this->assertEqualsWithDelta( $calories, $plan['totals']['calories'], $calories * 0.10, "Target {$calories}" );
		}
	}

	public function test_one_recipe_per_meal_in_order_and_no_repeats(): void {
		$plan = $this->plan( array( 'calories' => 2000, 'protein_g' => 90, 'include_snack' => true ) );
		$this->assertSame( array( 'breakfast', 'lunch', 'dinner', 'snack' ), array_column( $plan['meals'], 'meal' ) );
		$ids = array_map( static fn( $m ) => $m['recipe']['id'], $plan['meals'] );
		$this->assertSame( count( $ids ), count( array_unique( $ids ) ) );
	}

	public function test_without_snack_plans_three_meals(): void {
		$plan = $this->plan( array( 'calories' => 2000, 'protein_g' => 90 ) );
		$this->assertCount( 3, $plan['meals'] );
	}

	public function test_totals_match_the_sum_of_meals(): void {
		$plan = $this->plan( array( 'calories' => 2400, 'protein_g' => 120, 'include_snack' => true ) );
		$this->assertSame( array_sum( array_column( $plan['meals'], 'protein_g' ) ), $plan['totals']['protein_g'] );
	}

	public function test_shuffle_gives_a_different_plan(): void {
		$a = $this->plan( array( 'calories' => 2200, 'protein_g' => 100, 'variant' => 0 ) );
		$b = $this->plan( array( 'calories' => 2200, 'protein_g' => 100, 'variant' => 1 ) );
		$this->assertNotEquals( $a['meals'], $b['meals'] );
	}

	public function test_reports_meals_with_no_recipes(): void {
		$recipes = array_filter( self::recipes(), static fn( $r ) => ! in_array( 'snack', $r['meals'], true ) );
		$plan    = ( new Meal_Planner() )->plan( array( 'calories' => 2000, 'protein_g' => 90, 'include_snack' => true ), $recipes );
		$this->assertSame( array( 'snack' ), $plan['missing'] );
		$this->assertCount( 3, $plan['meals'] );
	}

	public function test_rejects_unrealistic_calorie_targets(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->plan( array( 'calories' => 300, 'protein_g' => 50 ) );
	}
}

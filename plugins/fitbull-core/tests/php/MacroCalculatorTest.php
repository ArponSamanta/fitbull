<?php

use Fitbull\Core\Macro_Calculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MacroCalculatorTest extends TestCase {

	private const BASE = array(
		'sex'       => 'male',
		'age'       => 25,
		'height_cm' => 172,
		'weight_kg' => 72,
		'activity'  => 'moderate',
		'goal'      => 'maintain',
	);

	private function calc( array $overrides = array() ): array {
		return ( new Macro_Calculator() )->calculate( array_merge( self::BASE, $overrides ) );
	}

	public function test_bmr_uses_mifflin_st_jeor(): void {
		$this->assertSame( 1675, $this->calc()['bmr'] );
		$this->assertSame( 1509, $this->calc( array( 'sex' => 'female' ) )['bmr'] );
	}

	/** Same expectation as src/lib/macros.test.js, so PHP and JS stay in step. */
	public function test_matches_the_javascript_calculator(): void {
		$result = $this->calc();
		$this->assertSame( 2596, $result['tdee'] );
		$this->assertSame( 2600, $result['calories'] );
		$this->assertSame( 115, $result['protein_g'] );
	}

	public function test_goal_moves_calories_in_the_right_direction(): void {
		$maintain = $this->calc()['calories'];
		$this->assertLessThan( $maintain, $this->calc( array( 'goal' => 'lose' ) )['calories'] );
		$this->assertGreaterThan( $maintain, $this->calc( array( 'goal' => 'gain' ) )['calories'] );
	}

	public function test_macros_add_up_to_the_calorie_target(): void {
		$r     = $this->calc( array( 'goal' => 'gain' ) );
		$total = $r['protein_g'] * 4 + $r['carbs_g'] * 4 + $r['fat_g'] * 9;
		$this->assertEqualsWithDelta( $r['calories'], $total, 10 );
	}

	public static function invalid_inputs(): array {
		return array(
			'age too low'      => array( array( 'age' => 10 ) ),
			'weight too high'  => array( array( 'weight_kg' => 500 ) ),
			'missing height'   => array( array( 'height_cm' => null ) ),
			'unknown sex'      => array( array( 'sex' => 'other' ) ),
			'unknown activity' => array( array( 'activity' => 'extreme' ) ),
			'unknown goal'     => array( array( 'goal' => 'shred' ) ),
		);
	}

	#[DataProvider( 'invalid_inputs' )]
	public function test_rejects_invalid_input( array $overrides ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->calc( $overrides );
	}
}

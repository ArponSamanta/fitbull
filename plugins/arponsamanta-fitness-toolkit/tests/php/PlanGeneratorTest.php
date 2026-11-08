<?php

use Fitbull\Core\Plan_Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanGeneratorTest extends TestCase {

	/** A small library covering every pattern with different equipment. */
	private static function library(): array {
		$rows = array(
			array( 1, 'Bodyweight squat', 'beginner', 'squat', array( 'bodyweight' ) ),
			array( 2, 'Barbell back squat', 'intermediate', 'squat', array( 'barbell' ) ),
			array( 3, 'Glute bridge', 'beginner', 'hinge', array( 'bodyweight' ) ),
			array( 4, 'Conventional deadlift', 'advanced', 'hinge', array( 'barbell' ) ),
			array( 5, 'Push-up', 'beginner', 'push_h', array( 'bodyweight' ) ),
			array( 6, 'Barbell bench press', 'intermediate', 'push_h', array( 'barbell', 'bench' ) ),
			array( 7, 'Dumbbell shoulder press', 'beginner', 'push_v', array( 'dumbbells' ) ),
			array( 8, 'Dumbbell row', 'beginner', 'pull_h', array( 'dumbbells' ) ),
			array( 9, 'Pull-up', 'advanced', 'pull_v', array( 'pull-up-bar' ) ),
			array( 10, 'Reverse lunge', 'beginner', 'lunge', array( 'bodyweight' ) ),
			array( 11, 'Plank', 'beginner', 'core', array( 'bodyweight' ), true ),
			array( 12, 'Biceps curl', 'beginner', 'arms', array( 'dumbbells' ) ),
		);
		return array_map(
			static fn( $r ) => array(
				'id'         => $r[0],
				'title'      => $r[1],
				'link'       => '/exercises/' . $r[0],
				'difficulty' => $r[2],
				'pattern'    => $r[3],
				'equipment'  => $r[4],
				'timed'      => $r[5] ?? false,
			),
			$rows
		);
	}

	private function plan( array $input ): array {
		return ( new Plan_Generator() )->generate(
			array_merge(
				array(
					'goal'      => 'muscle',
					'days'      => 3,
					'level'     => 'beginner',
					'equipment' => array(),
				),
				$input
			),
			self::library()
		);
	}

	private static function ids( array $plan ): array {
		$ids = array();
		foreach ( $plan['days'] as $day ) {
			foreach ( $day['exercises'] as $exercise ) {
				$ids[] = $exercise['id'];
			}
		}
		return $ids;
	}

	public static function splits(): array {
		return array(
			'2 days' => array( 2, array( 'full_a', 'full_b' ) ),
			'3 days' => array( 3, array( 'full_a', 'full_b', 'full_a' ) ),
			'4 days' => array( 4, array( 'upper', 'lower', 'upper', 'lower' ) ),
			'5 days' => array( 5, array( 'push', 'pull', 'legs', 'upper', 'lower' ) ),
		);
	}

	#[DataProvider( 'splits' )]
	public function test_days_choose_the_right_split( int $days, array $sessions ): void {
		$plan = $this->plan( array( 'days' => $days ) );
		$this->assertSame( $sessions, array_column( $plan['days'], 'session' ) );
	}

	public function test_a_repeated_session_repeats_its_exercises(): void {
		$plan = $this->plan( array( 'days' => 3, 'equipment' => array( 'dumbbells' ) ) );
		$this->assertSame( $plan['days'][0]['exercises'], $plan['days'][2]['exercises'] );
		$this->assertSame( 'Fri', $plan['days'][2]['weekday'] );
	}

	public function test_never_uses_equipment_the_user_does_not_own(): void {
		$plan    = $this->plan( array( 'equipment' => array() ) );
		$allowed = array( 1, 3, 5, 10, 11 ); // Bodyweight-only exercises.
		foreach ( self::ids( $plan ) as $id ) {
			$this->assertContains( $id, $allowed );
		}
	}

	public function test_an_exercise_needing_two_items_requires_both(): void {
		$plan = $this->plan(
			array(
				'level'     => 'intermediate',
				'equipment' => array( 'barbell' ),
			)
		);
		$this->assertNotContains( 6, self::ids( $plan ), 'Bench press also needs a bench.' );
	}

	public function test_beginners_never_get_harder_exercises(): void {
		$plan = $this->plan( array( 'equipment' => array( 'barbell', 'bench', 'dumbbells', 'pull-up-bar' ) ) );
		foreach ( array( 2, 4, 6, 9 ) as $hard ) {
			$this->assertNotContains( $hard, self::ids( $plan ) );
		}
	}

	public function test_intermediates_get_the_harder_option_first(): void {
		$plan  = $this->plan(
			array(
				'level'     => 'intermediate',
				'equipment' => array( 'barbell' ),
			)
		);
		$first = $plan['days'][0]['exercises'][0];
		$this->assertSame( 'squat', $first['slot'] );
		$this->assertSame( 2, $first['id'] );
	}

	public function test_prefers_equipment_the_user_owns_at_the_same_level(): void {
		$library   = self::library();
		$library[] = array(
			'id'         => 13,
			'title'      => 'Goblet squat',
			'link'       => '/exercises/13',
			'difficulty' => 'beginner',
			'pattern'    => 'squat',
			'equipment'  => array( 'dumbbells' ),
		);
		$plan = ( new Plan_Generator() )->generate(
			array( 'goal' => 'muscle', 'days' => 2, 'level' => 'beginner', 'equipment' => array( 'dumbbells' ) ),
			$library
		);
		$this->assertSame( 13, $plan['days'][0]['exercises'][0]['id'] );
	}

	public function test_missing_patterns_are_reported_not_faked(): void {
		// Bodyweight only: nothing pulls, so the plan says so.
		$plan = $this->plan( array( 'equipment' => array() ) );
		$this->assertContains( 'pull_h', $plan['notes'] );
	}

	public function test_no_exercise_repeats_within_a_session(): void {
		$plan = $this->plan(
			array(
				'days'      => 4,
				'equipment' => array( 'dumbbells' ),
			)
		);
		foreach ( $plan['days'] as $day ) {
			$ids = array_column( $day['exercises'], 'id' );
			$this->assertSame( count( $ids ), count( array_unique( $ids ) ) );
		}
	}

	public function test_goal_sets_the_rep_range_and_timed_exercises_use_seconds(): void {
		$strength = $this->plan( array( 'goal' => 'strength' ) )['days'][0]['exercises'];
		$this->assertSame( '4–6', $strength[0]['reps'] );
		$this->assertSame( 4, $strength[0]['sets'] );

		$plank = array_values( array_filter( $strength, static fn( $e ) => 11 === $e['id'] ) );
		$this->assertSame( '30–45 s', $plank[0]['reps'] );
	}

	public function test_alternatives_exclude_the_chosen_exercise(): void {
		$plan = $this->plan( array( 'level' => 'intermediate', 'equipment' => array( 'barbell' ) ) );
		$squat = $plan['days'][0]['exercises'][0];
		$this->assertSame( array( 1 ), array_column( $squat['alternatives'], 'id' ) );
	}

	public static function invalid_inputs(): array {
		return array(
			'one day'      => array( array( 'days' => 1 ) ),
			'six days'     => array( array( 'days' => 6 ) ),
			'unknown goal' => array( array( 'goal' => 'shred' ) ),
			'advanced'     => array( array( 'level' => 'advanced' ) ),
		);
	}

	#[DataProvider( 'invalid_inputs' )]
	public function test_rejects_invalid_input( array $input ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->plan( $input );
	}
}

<?php
/**
 * Rule-based weekly workout plan generator.
 *
 * Pure PHP: it receives exercises as plain arrays and returns a plan, so it
 * is unit tested without WordPress. Rest_Controller supplies the exercises.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

// Unit tests define ABSPATH in tests/php/bootstrap.php, so this class loads without WordPress.
defined( 'ABSPATH' ) || exit;

class Plan_Generator {

	public const GOALS  = array( 'muscle', 'strength', 'fat_loss' );
	public const LEVELS = array( 'beginner', 'intermediate' );

	/** Exercises that use no equipment. Everyone owns this. */
	public const BODYWEIGHT = 'bodyweight';

	/** Movement slots for each kind of session. */
	private const SESSIONS = array(
		'full_a' => array( 'squat', 'push_h', 'pull_h', 'hinge', 'core' ),
		'full_b' => array( 'hinge', 'push_v', 'pull_v', 'lunge', 'core' ),
		'upper'  => array( 'push_h', 'pull_h', 'push_v', 'pull_v', 'arms', 'arms' ),
		'lower'  => array( 'squat', 'hinge', 'lunge', 'core' ),
		'push'   => array( 'push_h', 'push_v', 'push_h', 'arms' ),
		'pull'   => array( 'pull_v', 'pull_h', 'pull_h', 'arms' ),
		'legs'   => array( 'squat', 'hinge', 'lunge', 'core' ),
	);

	/** Which sessions make up a week, by training days. */
	private const SPLITS = array(
		2 => array( 'full_a', 'full_b' ),
		3 => array( 'full_a', 'full_b', 'full_a' ),
		4 => array( 'upper', 'lower', 'upper', 'lower' ),
		5 => array( 'push', 'pull', 'legs', 'upper', 'lower' ),
	);

	private const SCHEDULE = array(
		2 => array( 'Mon', 'Thu' ),
		3 => array( 'Mon', 'Wed', 'Fri' ),
		4 => array( 'Mon', 'Tue', 'Thu', 'Fri' ),
		5 => array( 'Mon', 'Tue', 'Wed', 'Fri', 'Sat' ),
	);

	/** If no exercise fits a slot, try a similar movement. */
	private const FALLBACKS = array(
		'push_v' => array( 'push_h' ),
		'push_h' => array( 'push_v' ),
		'pull_v' => array( 'pull_h' ),
		'pull_h' => array( 'pull_v' ),
		'lunge'  => array( 'squat' ),
		'squat'  => array( 'lunge' ),
		'hinge'  => array(),
		'core'   => array(),
		'arms'   => array(),
	);

	private const ACCESSORY = array( 'core', 'arms' );

	/**
	 * @param array{goal:string,days:int,level:string,equipment:string[]} $input
	 * @param array<int,array{id:int,title:string,link:string,difficulty:string,pattern:string,equipment:string[],timed?:bool}> $exercises
	 * @return array{days:array,notes:string[],summary:array}
	 * @throws \InvalidArgumentException On unknown goal, level or day count.
	 */
	public function generate( array $input, array $exercises ): array {
		$goal  = $input['goal'] ?? '';
		$days  = (int) ( $input['days'] ?? 0 );
		$level = $input['level'] ?? '';
		if ( ! in_array( $goal, self::GOALS, true ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Caught and returned as a JSON WP_Error.
			throw new \InvalidArgumentException( 'Unknown goal.' );
		}
		if ( ! isset( self::SPLITS[ $days ] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Caught and returned as a JSON WP_Error.
			throw new \InvalidArgumentException( 'Days must be between 2 and 5.' );
		}
		if ( ! in_array( $level, self::LEVELS, true ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Caught and returned as a JSON WP_Error.
			throw new \InvalidArgumentException( 'Unknown level.' );
		}

		$owned      = array_unique( array_merge( array( self::BODYWEIGHT ), (array) ( $input['equipment'] ?? array() ) ) );
		$candidates = $this->candidates( $exercises, $owned, $level );
		$uses       = array();
		$notes      = array();
		$plan       = array();

		$built = array();
		foreach ( self::SPLITS[ $days ] as $index => $session ) {
			// A session that appears twice in the week (e.g. full body A on
			// Monday and Friday) repeats the same exercises, so progress is
			// easy to track. Variety comes from the different session types.
			if ( isset( $built[ $session ] ) ) {
				$plan[] = array_merge(
					$built[ $session ],
					array(
						'day'     => $index + 1,
						'weekday' => self::SCHEDULE[ $days ][ $index ],
					)
				);
				continue;
			}

			$today = array();
			$items = array();
			foreach ( self::SESSIONS[ $session ] as $pattern ) {
				$pick = $this->pick( $pattern, $candidates, $uses, $today );
				if ( ! $pick ) {
					// Only report a gap when nothing at all fits, not when the
					// one option was already used earlier in the same session.
					if ( ! $this->has_options( $pattern, $candidates ) ) {
						$notes[ $pattern ] = $pattern;
					}
					continue;
				}
				list( $exercise, $used_pattern, $options ) = $pick;
				$uses[ $exercise['id'] ] = ( $uses[ $exercise['id'] ] ?? 0 ) + 1;
				$today[]                 = $exercise['id'];
				$items[]                 = array_merge(
					$this->summary( $exercise ),
					$this->prescription( $goal, $used_pattern, ! empty( $exercise['timed'] ) ),
					array(
						'slot'         => $pattern,
						'alternatives' => array_map(
							array( $this, 'summary' ),
							array_values( array_filter( $options, static fn( $o ) => $o['id'] !== $exercise['id'] ) )
						),
					)
				);
			}
			$built[ $session ] = array(
				'day'       => $index + 1,
				'weekday'   => self::SCHEDULE[ $days ][ $index ],
				'session'   => $session,
				'exercises' => $items,
			);
			$plan[]            = $built[ $session ];
		}

		return array(
			'days'    => $plan,
			'notes'   => array_values( $notes ),
			'summary' => array(
				'goal'  => $goal,
				'days'  => $days,
				'level' => $level,
			),
		);
	}

	/**
	 * Exercises the visitor can do with what they own, grouped by pattern
	 * and ordered so the best fit for their level comes first.
	 */
	private function candidates( array $exercises, array $owned, string $level ): array {
		$rank    = array(
			'beginner'     => 0,
			'intermediate' => 1,
			'advanced'     => 2,
		);
		$allowed = 'beginner' === $level ? array( 'beginner' ) : array( 'beginner', 'intermediate', 'advanced' );

		$by_pattern = array();
		foreach ( $exercises as $exercise ) {
			$needs = array_diff( (array) $exercise['equipment'], array( self::BODYWEIGHT ) );
			if ( array_diff( $needs, $owned ) ) {
				continue;
			}
			if ( ! in_array( $exercise['difficulty'], $allowed, true ) ) {
				continue;
			}
			$by_pattern[ $exercise['pattern'] ][] = $exercise;
		}

		foreach ( $by_pattern as &$list ) {
			usort(
				$list,
				static function ( $a, $b ) use ( $rank, $level ) {
					$order = $rank[ $a['difficulty'] ] <=> $rank[ $b['difficulty'] ];
					// Beginners get the simplest version first, others the hardest.
					$order = 'beginner' === $level ? $order : -$order;
					if ( 0 !== $order ) {
						return $order;
					}
					// Same level: prefer the option that uses equipment they own.
					$gear = count( array_diff( (array) $b['equipment'], array( Plan_Generator::BODYWEIGHT ) ) )
						<=> count( array_diff( (array) $a['equipment'], array( Plan_Generator::BODYWEIGHT ) ) );
					return 0 !== $gear ? $gear : strcmp( $a['title'], $b['title'] );
				}
			);
		}
		return $by_pattern;
	}

	/**
	 * The least-used exercise for this slot that isn't already in today's
	 * session, falling back to a similar movement pattern.
	 *
	 * @return array{0:array,1:string,2:array}|null Exercise, pattern used, all options.
	 */
	private function pick( string $pattern, array $candidates, array $uses, array $today ): ?array {
		foreach ( array_merge( array( $pattern ), self::FALLBACKS[ $pattern ] ) as $try ) {
			$options = $candidates[ $try ] ?? array();
			if ( ! $options ) {
				continue;
			}
			$fresh = array_values( array_filter( $options, static fn( $o ) => ! in_array( $o['id'], $today, true ) ) );
			if ( ! $fresh ) {
				continue;
			}
			usort(
				$fresh,
				static fn( $a, $b ) => ( $uses[ $a['id'] ] ?? 0 ) <=> ( $uses[ $b['id'] ] ?? 0 )
			);
			return array( $fresh[0], $try, $options );
		}
		return null;
	}

	/**
	 * Sets, reps and rest by goal. Compound lifts get lower reps for
	 * strength; accessories (core, arms) stay in higher rep ranges.
	 */
	private function prescription( string $goal, string $pattern, bool $timed ): array {
		$accessory = in_array( $pattern, self::ACCESSORY, true );
		$table     = array(
			'strength' => $accessory ? array( 3, '10–12', 60 ) : array( 4, '4–6', 150 ),
			'muscle'   => $accessory ? array( 3, '12–15', 60 ) : array( 3, '8–12', 90 ),
			'fat_loss' => $accessory ? array( 3, '15', 45 ) : array( 3, '10–15', 60 ),
		);
		list( $sets, $reps, $rest ) = $table[ $goal ];

		if ( $timed ) {
			$reps = '30–45 s';
		} elseif ( 'lunge' === $pattern ) {
			$reps .= ' each leg';
		}

		return array(
			'sets'         => $sets,
			'reps'         => $reps,
			'rest_seconds' => $rest,
		);
	}

	private function summary( array $exercise ): array {
		return array(
			'id'    => (int) $exercise['id'],
			'title' => $exercise['title'],
			'link'  => $exercise['link'],
		);
	}

	private function has_options( string $pattern, array $candidates ): bool {
		foreach ( array_merge( array( $pattern ), self::FALLBACKS[ $pattern ] ) as $try ) {
			if ( ! empty( $candidates[ $try ] ) ) {
				return true;
			}
		}
		return false;
	}
}

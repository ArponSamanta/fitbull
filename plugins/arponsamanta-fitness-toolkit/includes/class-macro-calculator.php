<?php
/**
 * Calorie and macro targets (Mifflin-St Jeor).
 *
 * Pure PHP with no WordPress calls, so it is unit tested on its own.
 * The JavaScript calculator reads the same data/macro-config.json,
 * so the block and the REST endpoint always agree.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

// Unit tests define ABSPATH in tests/php/bootstrap.php, so this class loads without WordPress.
defined( 'ABSPATH' ) || exit;

class Macro_Calculator {

	/** @var array<string,mixed> */
	private array $config;

	public function __construct( ?array $config = null ) {
		$this->config = $config ?? self::load_config();
	}

	public static function load_config(): array {
		$json = file_get_contents( dirname( __DIR__ ) . '/data/macro-config.json' );
		return json_decode( (string) $json, true, 8, JSON_THROW_ON_ERROR );
	}

	public function config(): array {
		return $this->config;
	}

	/**
	 * @param array{sex:string,age:int|float,height_cm:int|float,weight_kg:int|float,activity:string,goal:string} $input
	 * @return array{bmr:int,tdee:int,calories:int,protein_g:int,carbs_g:int,fat_g:int}
	 * @throws \InvalidArgumentException When an input is missing or out of range.
	 */
	public function calculate( array $input ): array {
		$input = $this->validate( $input );

		$bmr = 10 * $input['weight_kg'] + 6.25 * $input['height_cm'] - 5 * $input['age']
			+ ( 'male' === $input['sex'] ? 5 : -161 );

		$tdee     = $bmr * $this->config['activity'][ $input['activity'] ]['factor'];
		$goal     = $this->config['goal'][ $input['goal'] ];
		$calories = (int) ( round( $tdee * ( 1 + $goal['calorieChange'] ) / 10 ) * 10 );

		$protein = (int) round( $input['weight_kg'] * $goal['proteinPerKg'] );
		$fat     = (int) round( $calories * $this->config['fatShare'] / 9 );
		$carbs   = (int) max( 0, round( ( $calories - $protein * 4 - $fat * 9 ) / 4 ) );

		return array(
			'bmr'       => (int) round( $bmr ),
			'tdee'      => (int) round( $tdee ),
			'calories'  => $calories,
			'protein_g' => $protein,
			'carbs_g'   => $carbs,
			'fat_g'     => $fat,
		);
	}

	private function validate( array $input ): array {
		if ( ! in_array( $input['sex'] ?? null, array( 'male', 'female' ), true ) ) {
			throw new \InvalidArgumentException( esc_html( 'sex must be "male" or "female".' ) );
		}
		if ( ! isset( $this->config['activity'][ $input['activity'] ?? '' ] ) ) {
			throw new \InvalidArgumentException( esc_html( 'Unknown activity level.' ) );
		}
		if ( ! isset( $this->config['goal'][ $input['goal'] ?? '' ] ) ) {
			throw new \InvalidArgumentException( esc_html( 'Unknown goal.' ) );
		}

		$ranges = array(
			'age'       => $this->config['limits']['age'],
			'height_cm' => $this->config['limits']['heightCm'],
			'weight_kg' => $this->config['limits']['weightKg'],
		);
		foreach ( $ranges as $key => list( $min, $max ) ) {
			if ( ! isset( $input[ $key ] ) || ! is_numeric( $input[ $key ] ) ) {
				throw new \InvalidArgumentException( esc_html( "{$key} is required." ) );
			}
			$value = (float) $input[ $key ];
			if ( $value < $min || $value > $max ) {
				throw new \InvalidArgumentException( esc_html( "{$key} must be between {$min} and {$max}." ) );
			}
			$input[ $key ] = $value;
		}

		return $input;
	}
}

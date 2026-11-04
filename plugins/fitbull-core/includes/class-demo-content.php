<?php
/**
 * Loads the demo content in data/demo-content.json. Used by
 * `wp fitbull seed` and by Tools → Fitbull demo content.
 *
 * Safe to run more than once: anything that already exists (matched by
 * slug) is left alone.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Demo_Content {

	public const NAV_SLUG = 'fitbull-main';

	/**
	 * @return int Number of items created.
	 */
	public static function import(): int {
		$data = json_decode(
			(string) file_get_contents( FITBULL_CORE_DIR . 'data/demo-content.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
			true,
			32,
			JSON_THROW_ON_ERROR
		);

		if ( ! get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}

		foreach ( $data['muscles'] as $name ) {
			self::term( $name, Content_Types::MUSCLE );
		}
		foreach ( $data['equipment'] as $name ) {
			self::term( $name, Content_Types::GEAR_TYPE );
		}
		foreach ( $data['diets'] as list( $slug, $name ) ) {
			self::term( $name, Content_Types::DIET, $slug );
		}
		foreach ( $data['meals'] as list( $slug, $name ) ) {
			self::term( $name, Content_Types::MEAL, $slug );
		}

		$created = 0;

		foreach ( $data['exercises'] as $item ) {
			$id = self::post( Content_Types::EXERCISE, $item['title'], $item['excerpt'], '' );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_difficulty', $item['difficulty'] );
			update_post_meta( $id, 'fb_pattern', $item['pattern'] );
			update_post_meta( $id, 'fb_timed', (bool) $item['timed'] );
			update_post_meta( $id, 'fb_steps', $item['steps'] );
			update_post_meta( $id, 'fb_mistakes', $item['mistakes'] );
			update_post_meta( $id, 'fb_secondary_muscles', $item['secondary'] );
			wp_set_object_terms( $id, $item['muscles'], Content_Types::MUSCLE );
			wp_set_object_terms( $id, $item['equipment'], Content_Types::GEAR_TYPE );
			++$created;
		}

		foreach ( $data['recipes'] as $item ) {
			$id = self::post( Content_Types::RECIPE, $item['title'], $item['excerpt'], '' );
			if ( ! $id ) {
				continue;
			}
			foreach ( array( 'calories', 'protein_g', 'carbs_g', 'fat_g', 'prep_minutes', 'servings' ) as $key ) {
				update_post_meta( $id, 'fb_' . $key, (int) $item[ $key ] );
			}
			update_post_meta( $id, 'fb_ingredients', $item['ingredients'] );
			update_post_meta( $id, 'fb_method', $item['method'] );
			wp_set_object_terms( $id, $item['diet'], Content_Types::DIET );
			wp_set_object_terms( $id, $item['meals'], Content_Types::MEAL );
			++$created;
		}

		foreach ( $data['gear'] as $item ) {
			$id = self::post( Content_Types::EQUIPMENT, $item['title'], $item['excerpt'] );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_budget_tier', $item['tier'] );
			update_post_meta( $id, 'fb_priority', (int) $item['priority'] );
			wp_set_object_terms( $id, $item['equipment'], Content_Types::GEAR_TYPE );
			++$created;
		}

		foreach ( $data['supplements'] as $item ) {
			$id = self::post( Content_Types::SUPPLEMENT, $item['title'], $item['excerpt'], self::paragraph( $item['verdict'] ) );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_evidence', $item['evidence'] );
			update_post_meta( $id, 'fb_verdict', $item['verdict'] );
			++$created;
		}

		foreach ( $data['posts'] as $item ) {
			$id = self::post( 'post', $item['title'], $item['excerpt'], $item['content'] );
			if ( ! $id ) {
				continue;
			}
			$term = self::term( $item['category'], 'category' );
			if ( $term ) {
				wp_set_post_categories( $id, array( $term ) );
			}
			++$created;
		}

		foreach ( $data['pages'] as $item ) {
			if ( get_page_by_path( $item['slug'] ) ) {
				continue;
			}
			wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $item['title'],
					'post_name'    => $item['slug'],
					'post_content' => $item['content'],
				)
			);
			++$created;
		}

		$created += self::navigation( $data['navigation'] );
		self::tidy_defaults();
		flush_rewrite_rules();

		return $created;
	}

	/**
	 * A navigation menu for the theme's header. The header's Navigation
	 * block has no hard-coded links: it shows the site's newest menu.
	 */
	private static function navigation( array $links ): int {
		if ( get_page_by_path( self::NAV_SLUG, OBJECT, 'wp_navigation' ) ) {
			return 0;
		}
		$last    = count( $links ) - 1;
		$content = '';
		foreach ( $links as $i => list( $label, $path ) ) {
			$attrs = array(
				'label' => $label,
				'url'   => home_url( $path ),
				'kind'  => 'custom',
			);
			if ( $i === $last ) {
				$attrs['className'] = 'fb-nav-cta';
			}
			$content .= '<!-- wp:navigation-link ' . wp_json_encode( $attrs ) . ' /-->';
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => __( 'Fitbull main menu', 'fitbull-core' ),
				'post_name'    => self::NAV_SLUG,
				'post_content' => $content,
			)
		);
		return $id ? 1 : 0;
	}

	/** Remove WordPress's sample content so the demo looks finished. */
	private static function tidy_defaults(): void {
		$default = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $default ) {
			wp_update_term(
				$default->term_id,
				'category',
				array(
					'name' => __( 'General', 'fitbull-core' ),
					'slug' => 'general',
				)
			);
		}
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello ) {
			wp_trash_post( $hello->ID );
		}
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample ) {
			wp_trash_post( $sample->ID );
		}
	}

	private static function term( string $name, string $taxonomy, string $slug = '' ): int {
		$existing = term_exists( $slug ?: $name, $taxonomy );
		if ( $existing ) {
			return (int) $existing['term_id'];
		}
		$result = wp_insert_term( $name, $taxonomy, $slug ? array( 'slug' => $slug ) : array() );
		return is_wp_error( $result ) ? 0 : (int) $result['term_id'];
	}

	/**
	 * Insert a published post unless one with the same slug exists.
	 * A null $content uses the excerpt as a paragraph; '' leaves the body empty.
	 *
	 * @return int New post ID, or 0 when skipped.
	 */
	private static function post( string $type, string $title, string $excerpt, ?string $content = null ): int {
		if ( get_page_by_path( sanitize_title( $title ), OBJECT, $type ) ) {
			return 0;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => $type,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_content' => $content ?? self::paragraph( $excerpt ),
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	private static function paragraph( string $text ): string {
		return '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
	}
}

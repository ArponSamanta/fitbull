<?php
/**
 * WP-CLI commands.
 *
 *   wp fitbull seed      Load demo exercises, gear, supplements, articles and pages.
 *
 * Safe to run more than once: items that already exist (by slug) are skipped.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

use WP_CLI;

class CLI {

	/**
	 * Load demo content from data/demo-content.json.
	 *
	 * ## EXAMPLES
	 *
	 *     wp fitbull seed
	 *
	 * @when after_wp_load
	 */
	public function seed(): void {
		$data = json_decode(
			(string) file_get_contents( FITBULL_CORE_DIR . 'data/demo-content.json' ),
			true,
			32,
			JSON_THROW_ON_ERROR
		);

		update_option( 'permalink_structure', '/%postname%/' );

		foreach ( $data['muscles'] as $name ) {
			$this->term( $name, Content_Types::MUSCLE );
		}
		foreach ( $data['equipment'] as $name ) {
			$this->term( $name, Content_Types::GEAR_TYPE );
		}

		$created = 0;

		foreach ( $data['exercises'] as $item ) {
			$id = $this->post( Content_Types::EXERCISE, $item['title'], $item['excerpt'], '' );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_difficulty', $item['difficulty'] );
			update_post_meta( $id, 'fb_steps', $item['steps'] );
			update_post_meta( $id, 'fb_mistakes', $item['mistakes'] );
			update_post_meta( $id, 'fb_secondary_muscles', $item['secondary'] );
			wp_set_object_terms( $id, $item['muscles'], Content_Types::MUSCLE );
			wp_set_object_terms( $id, $item['equipment'], Content_Types::GEAR_TYPE );
			++$created;
		}

		foreach ( $data['gear'] as $item ) {
			$id = $this->post( Content_Types::EQUIPMENT, $item['title'], $item['excerpt'] );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_budget_tier', $item['tier'] );
			wp_set_object_terms( $id, $item['equipment'], Content_Types::GEAR_TYPE );
			++$created;
		}

		foreach ( $data['supplements'] as $item ) {
			$id = $this->post( Content_Types::SUPPLEMENT, $item['title'], $item['excerpt'], $this->paragraph( $item['verdict'] ) );
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'fb_evidence', $item['evidence'] );
			update_post_meta( $id, 'fb_verdict', $item['verdict'] );
			++$created;
		}

		foreach ( $data['posts'] as $item ) {
			$id = $this->post( 'post', $item['title'], $item['excerpt'], $item['content'] );
			if ( ! $id ) {
				continue;
			}
			$term = $this->term( $item['category'], 'category' );
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

		// "Uncategorized" looks unfinished on a demo site.
		$default = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $default ) {
			wp_update_term( $default->term_id, 'category', array( 'name' => 'General', 'slug' => 'general' ) );
		}
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello ) {
			wp_trash_post( $hello->ID );
		}
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample ) {
			wp_trash_post( $sample->ID );
		}

		flush_rewrite_rules();
		WP_CLI::success( sprintf( 'Seeded %d items. Existing items were left alone.', $created ) );
	}

	private function term( string $name, string $taxonomy ): int {
		$existing = term_exists( $name, $taxonomy );
		if ( $existing ) {
			return (int) $existing['term_id'];
		}
		$result = wp_insert_term( $name, $taxonomy );
		return is_wp_error( $result ) ? 0 : (int) $result['term_id'];
	}

	/**
	 * Insert a published post unless one with the same slug exists.
 * A null $content uses the excerpt as a paragraph; '' leaves the body empty.
	 *
	 * @return int New post ID, or 0 when skipped.
	 */
	private function post( string $type, string $title, string $excerpt, ?string $content = null ): int {
		if ( get_page_by_path( sanitize_title( $title ), OBJECT, $type ) ) {
			return 0;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => $type,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_content' => $content ?? $this->paragraph( $excerpt ),
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	private function paragraph( string $text ): string {
		return '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
	}
}

<?php
/**
 * Training log storage: a custom table, because log rows are many, small
 * and private per user. They don't belong in wp_posts or user meta.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

class Progress_Log {

	public const DB_VERSION        = '1';
	public const DB_VERSION_OPTION = 'fitbull_core_db_version';
	public const TYPES             = array( 'weight', 'lift' );

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'fitbull_logs';
	}

	/**
	 * Create or update the table. dbDelta() compares the SQL with the
	 * existing table and only applies the differences.
	 */
	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				logged_on date NOT NULL,
				type varchar(10) NOT NULL,
				exercise_id bigint(20) unsigned NOT NULL DEFAULT 0,
				weight_kg decimal(6,2) NOT NULL,
				reps smallint(5) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY user_date (user_id,logged_on)
			) {$charset};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	/** Run install() after a plugin update that changed the schema. */
	public static function maybe_upgrade(): void {
		if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function for_user( int $user_id, int $limit = 200 ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, per-user private data.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, logged_on, type, exercise_id, weight_kg, reps FROM %i WHERE user_id = %d ORDER BY logged_on DESC, id DESC LIMIT %d',
				self::table(),
				$user_id,
				$limit
			),
			ARRAY_A
		);
		return array_map( array( __CLASS__, 'format' ), $rows ?: array() );
	}

	/**
	 * @return array<string,mixed>|null The stored row, or null on failure.
	 */
	public static function add( int $user_id, array $entry ): ?array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
		$ok = $wpdb->insert(
			self::table(),
			array(
				'user_id'     => $user_id,
				'logged_on'   => $entry['logged_on'],
				'type'        => $entry['type'],
				'exercise_id' => (int) ( $entry['exercise_id'] ?? 0 ),
				'weight_kg'   => (float) $entry['weight_kg'],
				'reps'        => (int) ( $entry['reps'] ?? 0 ),
				'created_at'  => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%d', '%f', '%d', '%s' )
		);
		if ( ! $ok ) {
			return null;
		}
		return self::format(
			array(
				'id'          => $wpdb->insert_id,
				'logged_on'   => $entry['logged_on'],
				'type'        => $entry['type'],
				'exercise_id' => (int) ( $entry['exercise_id'] ?? 0 ),
				'weight_kg'   => $entry['weight_kg'],
				'reps'        => (int) ( $entry['reps'] ?? 0 ),
			)
		);
	}

	/** Delete one entry, only if it belongs to the user. */
	public static function delete( int $user_id, int $id ): bool {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return (bool) $wpdb->delete(
			self::table(),
			array(
				'id'      => $id,
				'user_id' => $user_id,
			),
			array( '%d', '%d' )
		);
	}

	public static function delete_all_for_user( int $user_id ): int {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return (int) $wpdb->delete( self::table(), array( 'user_id' => $user_id ), array( '%d' ) );
	}

	private static function format( array $row ): array {
		$exercise_id = (int) $row['exercise_id'];
		return array(
			'id'             => (int) $row['id'],
			'date'           => $row['logged_on'],
			'type'           => $row['type'],
			'exercise_id'    => $exercise_id,
			'exercise_title' => $exercise_id ? get_the_title( $exercise_id ) : '',
			'weight_kg'      => round( (float) $row['weight_kg'], 2 ),
			'reps'           => (int) $row['reps'],
		);
	}

	/*
	 * Privacy tools (Tools → Export / Erase Personal Data).
	 */

	public static function register_exporter( array $exporters ): array {
		$exporters['fitbull-progress-log'] = array(
			'exporter_friendly_name' => __( 'Fitness Toolkit progress log', 'arponsamanta-fitness-toolkit' ),
			'callback'               => array( __CLASS__, 'export' ),
		);
		return $exporters;
	}

	public static function register_eraser( array $erasers ): array {
		$erasers['fitbull-progress-log'] = array(
			'eraser_friendly_name' => __( 'Fitness Toolkit progress log', 'arponsamanta-fitness-toolkit' ),
			'callback'             => array( __CLASS__, 'erase' ),
		);
		return $erasers;
	}

	public static function export( string $email ): array {
		$user  = get_user_by( 'email', $email );
		$items = array();
		if ( $user ) {
			foreach ( self::for_user( $user->ID, 5000 ) as $row ) {
				$items[] = array(
					'group_id'    => 'fitbull-progress-log',
					'group_label' => __( 'Fitness Toolkit progress log', 'arponsamanta-fitness-toolkit' ),
					'item_id'     => 'fitbull-log-' . $row['id'],
					'data'        => array(
						array(
							'name'  => __( 'Date', 'arponsamanta-fitness-toolkit' ),
							'value' => $row['date'],
						),
						array(
							'name'  => __( 'Type', 'arponsamanta-fitness-toolkit' ),
							'value' => $row['type'],
						),
						array(
							'name'  => __( 'Exercise', 'arponsamanta-fitness-toolkit' ),
							'value' => $row['exercise_title'],
						),
						array(
							'name'  => __( 'Weight (kg)', 'arponsamanta-fitness-toolkit' ),
							'value' => (string) $row['weight_kg'],
						),
						array(
							'name'  => __( 'Reps', 'arponsamanta-fitness-toolkit' ),
							'value' => (string) $row['reps'],
						),
					),
				);
			}
		}
		return array(
			'data' => $items,
			'done' => true,
		);
	}

	public static function erase( string $email ): array {
		$user    = get_user_by( 'email', $email );
		$removed = $user ? self::delete_all_for_user( $user->ID ) : 0;
		return array(
			'items_removed'  => $removed > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}

<?php
/**
 * Runs when the plugin is deleted from wp-admin.
 *
 * Removes the private progress log table and the plugin's options.
 * Exercises, recipes and other posts are content the site owns, so they stay.
 *
 * @package Fitbull\Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Dropping the plugin's own table.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'fitbull_logs' ) );

delete_option( 'fitbull_core_db_version' );

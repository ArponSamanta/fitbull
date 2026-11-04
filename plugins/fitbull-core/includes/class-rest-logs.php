<?php
/**
 * REST API for the signed-in user's progress log: /wp-json/fitbull/v1/logs
 *
 *   GET    /logs        The current user's entries, newest first.
 *   POST   /logs        Add a bodyweight or lift entry.
 *   DELETE /logs/<id>   Delete one of the current user's entries.
 *
 * All routes need a logged-in user. Cookie auth requires the REST nonce,
 * which apiFetch sends automatically on the front end.
 *
 * @package Fitbull\Core
 */

namespace Fitbull\Core;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Rest_Logs {

	public function register_routes(): void {
		register_rest_route(
			Rest_Controller::NAMESPACE,
			'/logs',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_entries' ),
					'permission_callback' => array( $this, 'can_use_log' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_entry' ),
					'permission_callback' => array( $this, 'can_use_log' ),
					'args'                => array(
						'type'        => array(
							'type'     => 'string',
							'enum'     => Progress_Log::TYPES,
							'required' => true,
						),
						'date'        => array(
							'type'     => 'string',
							'format'   => 'date',
							'required' => true,
						),
						'weight_kg'   => array(
							'type'     => 'number',
							'minimum'  => 1,
							'maximum'  => 500,
							'required' => true,
						),
						'reps'        => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 30,
						),
						'exercise_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
				),
			)
		);

		register_rest_route(
			Rest_Controller::NAMESPACE,
			'/logs/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_entry' ),
				'permission_callback' => array( $this, 'can_use_log' ),
				'args'                => array(
					'id' => array(
						'type'     => 'integer',
						'minimum'  => 1,
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * @return true|WP_Error
	 */
	public function can_use_log() {
		if ( is_user_logged_in() ) {
			return true;
		}
		return new WP_Error(
			'fitbull_login_required',
			__( 'Log in to use your progress log.', 'fitbull-core' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	public function list_entries(): WP_REST_Response {
		return new WP_REST_Response( Progress_Log::for_user( get_current_user_id() ) );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_entry( WP_REST_Request $request ) {
		$date = (string) $request['date'];
		if ( $date > current_time( 'Y-m-d' ) || $date < '2000-01-01' ) {
			return new WP_Error( 'fitbull_invalid_date', __( 'Pick a date between 2000 and today.', 'fitbull-core' ), array( 'status' => 400 ) );
		}

		$entry = array(
			'type'      => $request['type'],
			'logged_on' => $date,
			'weight_kg' => round( (float) $request['weight_kg'], 2 ),
		);

		if ( 'lift' === $entry['type'] ) {
			$exercise_id = (int) $request['exercise_id'];
			$valid       = $exercise_id
				&& Content_Types::EXERCISE === get_post_type( $exercise_id )
				&& 'publish' === get_post_status( $exercise_id );
			if ( ! $valid ) {
				return new WP_Error( 'fitbull_invalid_exercise', __( 'Choose an exercise from the library.', 'fitbull-core' ), array( 'status' => 400 ) );
			}
			if ( ! $request['reps'] ) {
				return new WP_Error( 'fitbull_reps_required', __( 'Enter how many reps you did.', 'fitbull-core' ), array( 'status' => 400 ) );
			}
			$entry['exercise_id'] = $exercise_id;
			$entry['reps']        = (int) $request['reps'];
		}

		$saved = Progress_Log::add( get_current_user_id(), $entry );
		if ( ! $saved ) {
			return new WP_Error( 'fitbull_save_failed', __( 'Could not save that entry.', 'fitbull-core' ), array( 'status' => 500 ) );
		}
		return new WP_REST_Response( $saved, 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_entry( WP_REST_Request $request ) {
		if ( ! Progress_Log::delete( get_current_user_id(), (int) $request['id'] ) ) {
			// Same answer whether it doesn't exist or belongs to someone else.
			return new WP_Error( 'fitbull_not_found', __( 'Entry not found.', 'fitbull-core' ), array( 'status' => 404 ) );
		}
		return new WP_REST_Response( array( 'deleted' => true ) );
	}
}

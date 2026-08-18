<?php
/**
 * Custom REST API routes.
 *
 * Namespace: agencyflow/v1
 *   GET  /wp-json/agencyflow/v1/projects
 *   POST /wp-json/agencyflow/v1/enquiries
 *
 * @package AgencyFlow_Project_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the custom REST API routes.
 */
function agencyflow_register_rest_routes() {

	register_rest_route(
		'agencyflow/v1',
		'/projects',
		array(
			'methods'             => WP_REST_Server::READABLE, // GET
			'callback'            => 'agencyflow_rest_get_projects',
			'permission_callback' => '__return_true',          // Public, read-only data.
			'args'                => array(
				'type'     => array(
					'required'          => false,
					'sanitize_callback' => 'sanitize_title',
					'description'       => 'Project type slug to filter by.',
				),
				'per_page' => array(
					'required'          => false,
					'default'           => 6,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'agencyflow_validate_per_page',
					'description'       => 'How many projects to return (1-20).',
				),
			),
		)
	);

	register_rest_route(
		'agencyflow/v1',
		'/enquiries',
		array(
			'methods'             => WP_REST_Server::CREATABLE, // POST
			'callback'            => 'agencyflow_rest_create_enquiry',
			'permission_callback' => '__return_true',           // Public form endpoint; nonce checked in callback.
		)
	);
}
add_action( 'rest_api_init', 'agencyflow_register_rest_routes' );

/**
 * Validate the projects per-page parameter.
 *
 * @param mixed $value Incoming value.
 * @return bool
 */
function agencyflow_validate_per_page( $value ) {
	$number = (int) $value;
	return $number >= 1 && $number <= 20;
}

/**
 * GET /wp-json/agencyflow/v1/projects
 *
 * Returns a JSON array of projects with the fields the front end actually
 * uses. Responds 200 with an empty array when nothing matches — an empty
 * result is not an error.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return WP_REST_Response
 */
function agencyflow_rest_get_projects( $request ) {

	$per_page = (int) $request->get_param( 'per_page' );
	$type     = $request->get_param( 'type' );

	$query_args = array(
		'post_type'      => 'project',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Only add a taxonomy query when a type was actually asked for.
	if ( ! empty( $type ) && 'all' !== $type ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'project_type',
				'field'    => 'slug',
				'terms'    => $type,
			),
		);
	}

	$query    = new WP_Query( $query_args );
	$projects = array();

	foreach ( $query->posts as $post ) {

		$terms = get_the_terms( $post->ID, 'project_type' );
		$type_names = array();
		$type_slugs = array();

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$type_names[] = $term->name;
				$type_slugs[] = $term->slug;
			}
		}

		$completion_date = get_post_meta( $post->ID, '_agencyflow_completion_date', true );

		$projects[] = array(
			'id'              => $post->ID,
			'title'           => get_the_title( $post ),
			'excerpt'         => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'permalink'       => get_permalink( $post ),
			'image'           => get_the_post_thumbnail_url( $post->ID, 'medium_large' ),
			'client'          => get_post_meta( $post->ID, '_agencyflow_client', true ),
			'technologies'    => agencyflow_split_technologies(
				get_post_meta( $post->ID, '_agencyflow_technologies', true )
			),
			'completion_date' => $completion_date,
			'completed_label' => agencyflow_format_completion_date( $completion_date ),
			'project_url'     => get_post_meta( $post->ID, '_agencyflow_project_url', true ),
			'type_names'      => $type_names,
			'type_slugs'      => $type_slugs,
		);
	}

	// Return a standard REST response.
	return rest_ensure_response( $projects );
}

/**
 * POST /wp-json/agencyflow/v1/enquiries
 *
 * @param WP_REST_Request $request The incoming request.
 * @return WP_REST_Response|WP_Error
 */
function agencyflow_rest_create_enquiry( $request ) {

	// Verify the request nonce.
	$nonce = (string) $request->get_param( 'nonce' );

	if ( ! wp_verify_nonce( $nonce, 'agencyflow_enquiry' ) ) {
		return new WP_Error(
			'agencyflow_bad_nonce',
			'Your session expired. Please reload the page and try again.',
			array( 'status' => 403 )
		);
	}

	// Sanitise submitted fields.
	$data = array(
		'name'         => sanitize_text_field( (string) $request->get_param( 'name' ) ),
		'email'        => sanitize_email( (string) $request->get_param( 'email' ) ),
		'company'      => sanitize_text_field( (string) $request->get_param( 'company' ) ),
		'project_type' => sanitize_text_field( (string) $request->get_param( 'project_type' ) ),
		'budget'       => sanitize_text_field( (string) $request->get_param( 'budget' ) ),
		'message'      => sanitize_textarea_field( (string) $request->get_param( 'message' ) ),
	);

	// Validate submitted fields.
	$errors = agencyflow_validate_enquiry( $data );

	if ( ! empty( $errors ) ) {
		// Return field-level validation errors.
		return new WP_Error(
			'agencyflow_invalid_enquiry',
			'Please check the highlighted fields.',
			array(
				'status' => 422,
				'errors' => $errors,
			)
		);
	}

	// Store the enquiry.
	$post_id = agencyflow_store_enquiry( $data );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return new WP_Error(
			'agencyflow_save_failed',
			'Something went wrong saving your enquiry. Please email us instead.',
			array( 'status' => 500 )
		);
	}

	// Send the enquiry notification.
	agencyflow_notify_new_enquiry( $post_id, $data );

	// Return the created response.
	return new WP_REST_Response(
		array(
			'success' => true,
			'message' => 'Thanks — your enquiry is with us. We usually reply within one working day.',
		),
		201
	);
}

<?php
/**
 * Project custom fields (meta box).
 *
 * @package AgencyFlow_Project_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The meta keys this plugin owns.
 *
 * They start with an underscore, which tells WordPress to hide them from
 * the generic "Custom Fields" panel — we have our own tidy box instead.
 *
 * @return array<int, string>
 */
function agencyflow_project_meta_keys() {
	return array(
		'_agencyflow_client',
		'_agencyflow_technologies',
		'_agencyflow_completion_date',
		'_agencyflow_project_url',
	);
}

/**
 * Add the Project Details box to the Project edit screen.
 */
function agencyflow_add_project_meta_box() {
	add_meta_box(
		'agencyflow_project_details',      // Unique id for the box.
		'Project Details',                 // Title the editor sees.
		'agencyflow_render_project_meta_box', // Callback that prints the HTML.
		'project',                         // Only on the Project post type.
		'normal',                          // Position: main column.
		'high'                             // Priority: near the top.
	);
}
add_action( 'add_meta_boxes', 'agencyflow_add_project_meta_box' );

/**
 * Print the meta box HTML.
 *
 * Every stored value is escaped on the way out with esc_attr(), because
 * data that came from a user must never be trusted when printed back into
 * an HTML attribute — that is how cross-site scripting happens.
 *
 * @param WP_Post $post The post currently being edited.
 */
function agencyflow_render_project_meta_box( $post ) {

	// A nonce is a one-time token. It proves this form was genuinely rendered
	// by WordPress for this user, not forged on another site.
	wp_nonce_field( 'agencyflow_save_project_meta', 'agencyflow_project_nonce' );

	$client      = get_post_meta( $post->ID, '_agencyflow_client', true );
	$tech        = get_post_meta( $post->ID, '_agencyflow_technologies', true );
	$completed   = get_post_meta( $post->ID, '_agencyflow_completion_date', true );
	$project_url = get_post_meta( $post->ID, '_agencyflow_project_url', true );
	?>
	<p>
		<label for="agencyflow_client"><strong>Client</strong></label><br>
		<input type="text" id="agencyflow_client" name="agencyflow_client"
			value="<?php echo esc_attr( $client ); ?>" class="widefat" maxlength="120">
	</p>

	<p>
		<label for="agencyflow_technologies"><strong>Technologies</strong></label><br>
		<input type="text" id="agencyflow_technologies" name="agencyflow_technologies"
			value="<?php echo esc_attr( $tech ); ?>" class="widefat"
			placeholder="WordPress, PHP, JavaScript">
		<span class="description">Comma separated.</span>
	</p>

	<p>
		<label for="agencyflow_completion_date"><strong>Completion date</strong></label><br>
		<input type="date" id="agencyflow_completion_date" name="agencyflow_completion_date"
			value="<?php echo esc_attr( $completed ); ?>">
	</p>

	<p>
		<label for="agencyflow_project_url"><strong>Project URL</strong></label><br>
		<input type="url" id="agencyflow_project_url" name="agencyflow_project_url"
			value="<?php echo esc_attr( $project_url ); ?>" class="widefat"
			placeholder="https://example.com">
	</p>
	<?php
}

/**
 * Save the meta box values.
 *
 * Four guard clauses run before anything is written. This order matters and
 * is worth being able to explain: we refuse the request unless it is a real
 * save, of the right post type, with a valid nonce, from a user who is
 * actually allowed to edit this post.
 *
 * @param int $post_id The post being saved.
 */
function agencyflow_save_project_meta( $post_id ) {

	// 1. WordPress autosaves in the background with no form data attached.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 2. Only act on Projects.
	if ( 'project' !== get_post_type( $post_id ) ) {
		return;
	}

	// 3. Nonce must be present and valid (CSRF protection).
	if ( ! isset( $_POST['agencyflow_project_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['agencyflow_project_nonce'] ) ),
			'agencyflow_save_project_meta'
		)
	) {
		return;
	}

	// 4. The current user must have permission to edit this specific post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// --- Client: plain text ------------------------------------------------
	if ( isset( $_POST['agencyflow_client'] ) ) {
		update_post_meta(
			$post_id,
			'_agencyflow_client',
			sanitize_text_field( wp_unslash( $_POST['agencyflow_client'] ) )
		);
	}

	// --- Technologies: plain text, stored as the raw comma separated list --
	if ( isset( $_POST['agencyflow_technologies'] ) ) {
		update_post_meta(
			$post_id,
			'_agencyflow_technologies',
			sanitize_text_field( wp_unslash( $_POST['agencyflow_technologies'] ) )
		);
	}

	// --- Completion date: must be YYYY-MM-DD or it is stored empty ---------
	if ( isset( $_POST['agencyflow_completion_date'] ) ) {
		$raw  = sanitize_text_field( wp_unslash( $_POST['agencyflow_completion_date'] ) );
		$safe = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
		update_post_meta( $post_id, '_agencyflow_completion_date', $safe );
	}

	// --- Project URL: esc_url_raw strips anything that is not a safe URL ---
	if ( isset( $_POST['agencyflow_project_url'] ) ) {
		update_post_meta(
			$post_id,
			'_agencyflow_project_url',
			esc_url_raw( wp_unslash( $_POST['agencyflow_project_url'] ) )
		);
	}
}
add_action( 'save_post', 'agencyflow_save_project_meta' );

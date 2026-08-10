<?php
/**
 * Enquiry storage, admin display and the automated notification.
 *
 * @package AgencyFlow_Project_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save a validated enquiry as an agf_enquiry post.
 *
 * Note wp_insert_post() and update_post_meta() rather than a hand-written
 * SQL INSERT. Using the WordPress data API means the values are escaped and
 * prepared for us, which is how this project avoids SQL injection without
 * writing any escaping code of its own.
 *
 * @param array<string, string> $data Sanitised and validated enquiry fields.
 * @return int|WP_Error New post ID, or WP_Error on failure.
 */
function agencyflow_store_enquiry( $data ) {

	$title = sprintf( '%s — %s', $data['name'], $data['project_type'] );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'agf_enquiry',
			'post_title'   => $title,
			'post_content' => $data['message'],
			'post_status'  => 'publish',
		),
		true // Return a WP_Error instead of 0 on failure.
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_agf_name', $data['name'] );
	update_post_meta( $post_id, '_agf_email', $data['email'] );
	update_post_meta( $post_id, '_agf_company', $data['company'] );
	update_post_meta( $post_id, '_agf_project_type', $data['project_type'] );
	update_post_meta( $post_id, '_agf_budget', $data['budget'] );
	update_post_meta( $post_id, '_agf_submitted_at', current_time( 'mysql' ) );

	return $post_id;
}

/**
 * The automated action: email the site administrator.
 *
 * This is the "automation" step of the project. A visitor event on the front
 * end triggers server-side processing, which triggers an action nobody had
 * to perform by hand.
 *
 * wp_mail() is WordPress's own mail function. On a local site it does not
 * reach the internet — LocalWP captures it in its built-in mail tool, which
 * is exactly what we want while developing.
 *
 * @param int                   $post_id The saved enquiry.
 * @param array<string, string> $data    The enquiry fields.
 * @return void
 */
function agencyflow_notify_new_enquiry( $post_id, $data ) {

	$to = get_option( 'admin_email' );

	$subject = sprintf(
		'[%s] New project enquiry from %s',
		get_bloginfo( 'name' ),
		$data['name']
	);

	$lines = array(
		'A new project enquiry has been submitted.',
		'',
		'Name:         ' . $data['name'],
		'Email:        ' . $data['email'],
		'Company:      ' . ( '' !== $data['company'] ? $data['company'] : '(not given)' ),
		'Project type: ' . $data['project_type'],
		'Budget:       ' . $data['budget'],
		'',
		'Message:',
		$data['message'],
		'',
		'View in admin: ' . get_edit_post_link( $post_id, '' ),
	);

	$body = implode( "\n", $lines );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	$sent = wp_mail( $to, $subject, $body, $headers );

	// If mail fails we do not fail the visitor's submission — their enquiry
	// is already safely stored. We just record it so the problem is visible.
	if ( ! $sent ) {
		update_post_meta( $post_id, '_agf_mail_failed', '1' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'AgencyFlow: wp_mail() failed for enquiry ' . $post_id );
		}
	}
}

/**
 * Show the enquiry fields on the admin edit screen.
 *
 * Enquiries only support a title and content, so without this box the
 * name, email, budget and so on would be invisible in the admin.
 */
function agencyflow_add_enquiry_meta_box() {
	add_meta_box(
		'agencyflow_enquiry_details',
		'Enquiry Details',
		'agencyflow_render_enquiry_meta_box',
		'agf_enquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'agencyflow_add_enquiry_meta_box' );

/**
 * Print the read-only enquiry details.
 *
 * Everything is escaped on output. This data came from a stranger on the
 * internet, so it is treated as hostile right up to the moment it is
 * printed — including inside the admin.
 *
 * @param WP_Post $post The enquiry being viewed.
 */
function agencyflow_render_enquiry_meta_box( $post ) {

	$fields = array(
		'Name'         => get_post_meta( $post->ID, '_agf_name', true ),
		'Email'        => get_post_meta( $post->ID, '_agf_email', true ),
		'Company'      => get_post_meta( $post->ID, '_agf_company', true ),
		'Project type' => get_post_meta( $post->ID, '_agf_project_type', true ),
		'Budget'       => get_post_meta( $post->ID, '_agf_budget', true ),
		'Submitted'    => get_post_meta( $post->ID, '_agf_submitted_at', true ),
	);
	?>
	<table class="widefat striped">
		<tbody>
		<?php foreach ( $fields as $label => $value ) : ?>
			<tr>
				<th scope="row" style="width:160px;"><?php echo esc_html( $label ); ?></th>
				<td>
					<?php
					if ( 'Email' === $label && ! empty( $value ) ) {
						printf(
							'<a href="%1$s">%2$s</a>',
							esc_url( 'mailto:' . $value ),
							esc_html( $value )
						);
					} else {
						echo esc_html( '' !== $value ? $value : '—' );
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Add an Email column to the Enquiries list screen.
 *
 * This is a FILTER, not an action. A filter receives a value, changes it,
 * and must return it. Actions do something and return nothing. Being able
 * to state that difference clearly is a standard WordPress interview
 * question.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function agencyflow_enquiry_columns( $columns ) {
	$columns['agf_email']  = 'Email';
	$columns['agf_budget'] = 'Budget';
	return $columns;
}
add_filter( 'manage_agf_enquiry_posts_columns', 'agencyflow_enquiry_columns' );

/**
 * Fill in the custom columns.
 *
 * @param string $column  Column key being rendered.
 * @param int    $post_id Current row's post ID.
 */
function agencyflow_enquiry_column_content( $column, $post_id ) {

	if ( 'agf_email' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_agf_email', true ) );
	}

	if ( 'agf_budget' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_agf_budget', true ) );
	}
}
add_action( 'manage_agf_enquiry_posts_custom_column', 'agencyflow_enquiry_column_content', 10, 2 );

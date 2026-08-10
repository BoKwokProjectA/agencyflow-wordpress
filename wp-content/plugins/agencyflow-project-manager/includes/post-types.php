<?php
/**
 * Custom post types and taxonomies.
 *
 * @package AgencyFlow_Project_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Project custom post type.
 *
 * A custom post type is how WordPress stores a kind of content that is not
 * a blog post and not a page. Projects need their own admin screen, their
 * own URL structure and their own fields, so they get their own type.
 */
function agencyflow_register_project_post_type() {

	$labels = array(
		'name'               => 'Projects',
		'singular_name'      => 'Project',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Project',
		'edit_item'          => 'Edit Project',
		'new_item'           => 'New Project',
		'view_item'          => 'View Project',
		'search_items'       => 'Search Projects',
		'not_found'          => 'No projects found',
		'not_found_in_trash' => 'No projects found in Trash',
		'all_items'          => 'All Projects',
		'menu_name'          => 'Projects',
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,               // Visible on the front end.
		'has_archive'  => true,               // Gives us the /projects/ listing page.
		'rewrite'      => array( 'slug' => 'projects' ),
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 20,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'show_in_rest' => true,               // Enables the block editor and core REST routes.
		'rest_base'    => 'projects',
	);

	register_post_type( 'project', $args );
}

/**
 * Register the Project Type taxonomy.
 *
 * A taxonomy groups content. This is the same mechanism as blog categories,
 * just attached to projects instead of posts, which is what the JavaScript
 * filter buttons on the archive page filter by.
 */
function agencyflow_register_project_type_taxonomy() {

	$labels = array(
		'name'          => 'Project Types',
		'singular_name' => 'Project Type',
		'search_items'  => 'Search Project Types',
		'all_items'     => 'All Project Types',
		'edit_item'     => 'Edit Project Type',
		'update_item'   => 'Update Project Type',
		'add_new_item'  => 'Add New Project Type',
		'new_item_name' => 'New Project Type Name',
		'menu_name'     => 'Project Types',
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,          // Behaves like categories, not tags.
		'show_admin_column' => true,          // Adds a column on the Projects list screen.
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
	);

	register_taxonomy( 'project_type', array( 'project' ), $args );
}

/**
 * Register the internal Enquiry post type.
 *
 * Enquiries are stored as posts because that gives us the admin list screen,
 * search, trash and capabilities for free rather than creating a custom
 * database table. It is not public: 'public' => false means no front-end
 * URL and no archive, so nobody can browse other people's enquiries.
 */
function agencyflow_register_enquiry_post_type() {

	$labels = array(
		'name'          => 'Enquiries',
		'singular_name' => 'Enquiry',
		'edit_item'     => 'View Enquiry',
		'all_items'     => 'All Enquiries',
		'menu_name'     => 'Enquiries',
		'not_found'     => 'No enquiries yet',
	);

	$args = array(
		'labels'          => $labels,
		'public'          => false,           // Never shown on the front end.
		'show_ui'         => true,            // But we still want an admin screen.
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 21,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array(
			'create_posts' => 'do_not_allow',  // Enquiries arrive from the form, not by hand.
		),
		'map_meta_cap'    => true,
		'show_in_rest'    => false,           // Not exposed through the REST API.
	);

	register_post_type( 'agf_enquiry', $args );
}

/**
 * Hook all three registrations onto 'init'.
 *
 * 'init' is the correct hook: it fires early on every request, after
 * WordPress has loaded but before any output, which is exactly when
 * content types need to exist.
 */
function agencyflow_register_content_types() {
	agencyflow_register_project_post_type();
	agencyflow_register_project_type_taxonomy();
	agencyflow_register_enquiry_post_type();
}
add_action( 'init', 'agencyflow_register_content_types' );

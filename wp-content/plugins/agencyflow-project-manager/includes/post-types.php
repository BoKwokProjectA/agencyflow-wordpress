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
		'public'       => true,               // Public project content.
		'has_archive'  => true,               // Enable the projects archive.
		'rewrite'      => array( 'slug' => 'projects' ),
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 20,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'show_in_rest' => true,               // Enable REST support.
		'rest_base'    => 'projects',
	);

	register_post_type( 'project', $args );
}

/**
 * Register the Project Type taxonomy.
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
		'hierarchical'      => true,          // Hierarchical project types.
		'show_admin_column' => true,          // Show project types in the admin list.
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
	);

	register_taxonomy( 'project_type', array( 'project' ), $args );
}

/**
 * Register the internal Enquiry post type.
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
		'public'          => false,           // Internal admin content.
		'show_ui'         => true,            // Show in the admin area.
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 21,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array(
			'create_posts' => 'do_not_allow',  // Enquiries are created by form submission.
		),
		'map_meta_cap'    => true,
		'show_in_rest'    => false,           // Keep enquiries out of core REST routes.
	);

	register_post_type( 'agf_enquiry', $args );
}

/**
 * Register all custom content types.
 */
function agencyflow_register_content_types() {
	agencyflow_register_project_post_type();
	agencyflow_register_project_type_taxonomy();
	agencyflow_register_enquiry_post_type();
}
add_action( 'init', 'agencyflow_register_content_types' );

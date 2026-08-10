<?php
/**
 * Plugin Name:       AgencyFlow Project Manager
 * Plugin URI:        https://github.com/YOURUSERNAME/agencyflow
 * Description:       Registers the Project content type, project metadata, a custom REST API endpoint and the project enquiry workflow for the AgencyFlow site.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Bo Kwok
 * License:           GPL-2.0-or-later
 * Text Domain:       agencyflow
 *
 * WHY THIS IS A PLUGIN AND NOT PART OF THE THEME
 * ----------------------------------------------
 * Content structure belongs in a plugin; presentation belongs in a theme.
 * If the client switches theme one day, their Projects must survive.
 * This is the single most important architectural decision in the project.
 *
 * @package AgencyFlow
 */

// Block direct access. Without this, someone could request this file
// straight from the browser and run it outside of WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin-wide constants. Defining paths once avoids repeating magic strings.
define( 'AGENCYFLOW_PM_VERSION', '1.0.0' );
define( 'AGENCYFLOW_PM_PATH', plugin_dir_path( __FILE__ ) );

/*
 * Load each responsibility from its own file.
 * Separation of concerns, without inventing a class hierarchy we do not need.
 */
require_once AGENCYFLOW_PM_PATH . 'includes/helpers.php';       // Pure PHP functions (unit tested).
require_once AGENCYFLOW_PM_PATH . 'includes/post-types.php';    // Project + Enquiry post types, taxonomy.
require_once AGENCYFLOW_PM_PATH . 'includes/meta-fields.php';   // Project detail meta box.
require_once AGENCYFLOW_PM_PATH . 'includes/rest-api.php';      // /wp-json/agencyflow/v1/ endpoints.
require_once AGENCYFLOW_PM_PATH . 'includes/enquiries.php';     // Enquiry validation, storage, notification.

/**
 * Runs once, when the plugin is activated.
 *
 * Custom post types add new URL patterns such as /projects/my-project/.
 * WordPress caches its URL rules, so we must flush them once after the
 * post types have been registered, or those URLs return a 404.
 */
function agencyflow_pm_activate() {

	// The 'init' hook has not fired yet during activation, so register the
	// content types by hand before we try to add terms to the taxonomy.
	agencyflow_register_project_post_type();
	agencyflow_register_project_type_taxonomy();
	agencyflow_register_enquiry_post_type();

	// Seed the four project types so the filter buttons have something to
	// filter by the moment the plugin is switched on.
	$default_types = array( 'Website', 'E-commerce', 'Automation', 'Web Application' );

	foreach ( $default_types as $type ) {
		if ( ! term_exists( $type, 'project_type' ) ) {
			wp_insert_term( $type, 'project_type' );
		}
	}

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'agencyflow_pm_activate' );

/**
 * Tidy up on deactivation so stale rewrite rules are not left behind.
 */
function agencyflow_pm_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'agencyflow_pm_deactivate' );

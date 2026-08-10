<?php
/**
 * AgencyFlow theme functions.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare what the theme supports.
 *
 * Hooked to 'after_setup_theme', which fires once WordPress has loaded the
 * theme but before anything is sent to the browser.
 */
function agencyflow_theme_setup() {

	// Let WordPress generate the <title> tag rather than hardcoding one.
	add_theme_support( 'title-tag' );

	// Enable featured images.
	add_theme_support( 'post-thumbnails' );

	// Ask core to output modern HTML5 markup instead of legacy XHTML.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Let WordPress manage the document title separator and feed links.
	add_theme_support( 'automatic-feed-links' );

	// One menu location, rendered in header.php.
	register_nav_menus(
		array(
			'primary' => 'Primary Menu',
		)
	);
}
add_action( 'after_setup_theme', 'agencyflow_theme_setup' );

/**
 * Register the image size used by the project cards.
 *
 * Cropping to a fixed ratio keeps the CSS Grid rows tidy regardless of the
 * shape of the image the client uploads.
 */
function agencyflow_image_sizes() {
	add_image_size( 'agencyflow-card', 800, 500, true );
}
add_action( 'after_setup_theme', 'agencyflow_image_sizes' );

/**
 * Fallback navigation, used when no menu has been assigned in the admin.
 *
 * Without this the header would simply be empty on a fresh install, which
 * looks broken. Defensive, and it costs six lines.
 */
function agencyflow_nav_fallback() {

	echo '<ul class="site-nav__list">';

	printf(
		'<li><a href="%s">Home</a></li>',
		esc_url( home_url( '/' ) )
	);

	$projects_url = get_post_type_archive_link( 'project' );
	if ( $projects_url ) {
		printf( '<li><a href="%s">Projects</a></li>', esc_url( $projects_url ) );
	}

	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		printf(
			'<li><a href="%s">Contact</a></li>',
			esc_url( get_permalink( $contact ) )
		);
	}

	echo '</ul>';
}

/**
 * Enqueue the theme's CSS and JavaScript.
 *
 * Two things worth understanding here.
 *
 * 1. Why enqueue at all rather than writing <link> and <script> tags into
 *    header.php? Because wp_enqueue_* lets WordPress deduplicate files,
 *    resolve dependency order, and append a version string for cache
 *    busting. Hardcoded tags get none of that.
 *
 * 2. Why the conditional tags? A visitor on the contact page has no use for
 *    the project filtering script. Loading only what the current page needs
 *    is the cheapest performance win available.
 */
function agencyflow_enqueue_assets() {

	$version   = wp_get_theme()->get( 'Version' );
	$theme_uri = get_template_directory_uri();

	// The theme stylesheet header lives in style.css; the actual rules live
	// in assets/css/main.css. Both are loaded.
	wp_enqueue_style(
		'agencyflow-style',
		get_stylesheet_uri(),
		array(),
		$version
	);

	wp_enqueue_style(
		'agencyflow-main',
		$theme_uri . '/assets/css/main.css',
		array( 'agencyflow-style' ), // Dependency: load after style.css.
		$version
	);

	// Data the JavaScript needs from PHP. Never hardcode a REST URL in a
	// script file — the site could live in a subdirectory, or change domain.
	$script_data = array(
		'restUrl' => esc_url_raw( rest_url( 'agencyflow/v1/' ) ),
		'nonce'   => wp_create_nonce( 'agencyflow_enquiry' ),
		'restNonce' => wp_create_nonce( 'wp_rest' ),
		'lat'     => '53.4808',  // Manchester.
		'lon'     => '-2.2426',
	);

	// --- Front page: weather strip + REST-loaded featured projects ---------
	if ( is_front_page() ) {

		wp_enqueue_script(
			'agencyflow-weather',
			$theme_uri . '/assets/js/weather.js',
			array(),
			$version,
			true // Load in the footer, after the DOM exists.
		);
		wp_localize_script( 'agencyflow-weather', 'agencyflowData', $script_data );

		wp_enqueue_script(
			'agencyflow-featured',
			$theme_uri . '/assets/js/featured-projects.js',
			array(),
			$version,
			true
		);
		wp_localize_script( 'agencyflow-featured', 'agencyflowData', $script_data );
	}

	// --- Projects archive: the filter buttons ------------------------------
	if ( is_post_type_archive( 'project' ) || is_tax( 'project_type' ) ) {

		wp_enqueue_script(
			'agencyflow-filter',
			$theme_uri . '/assets/js/filter.js',
			array(),
			$version,
			true
		);
	}

	// --- Contact page: the enquiry form -----------------------------------
	if ( is_page( 'contact' ) ) {

		wp_enqueue_script(
			'agencyflow-enquiry',
			$theme_uri . '/assets/js/enquiry.js',
			array(),
			$version,
			true
		);
		wp_localize_script( 'agencyflow-enquiry', 'agencyflowData', $script_data );
	}
}
add_action( 'wp_enqueue_scripts', 'agencyflow_enqueue_assets' );

/**
 * Trim the default excerpt length.
 *
 * This is a FILTER. It receives a value (the word count), returns a new one,
 * and WordPress uses the returned value. Compare with an action, which does
 * something and returns nothing. Knowing that distinction is a standard
 * WordPress interview question.
 *
 * @param int $length Current excerpt length in words.
 * @return int
 */
function agencyflow_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'agencyflow_excerpt_length' );

/**
 * Replace the default excerpt "[...]" with a cleaner ellipsis.
 *
 * @param string $more Current more string.
 * @return string
 */
function agencyflow_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'agencyflow_excerpt_more' );

/**
 * Helper: get a project's formatted metadata for the templates.
 *
 * Templates should read data, not gather it. Putting this in functions.php
 * keeps the template files focused on markup.
 *
 * @param int $post_id Project ID.
 * @return array<string, mixed>
 */
function agencyflow_get_project_meta( $post_id ) {

	$completion = get_post_meta( $post_id, '_agencyflow_completion_date', true );

	return array(
		'client'       => get_post_meta( $post_id, '_agencyflow_client', true ),
		'technologies' => function_exists( 'agencyflow_split_technologies' )
			? agencyflow_split_technologies( get_post_meta( $post_id, '_agencyflow_technologies', true ) )
			: array(),
		'completed'    => function_exists( 'agencyflow_format_completion_date' )
			? agencyflow_format_completion_date( $completion )
			: $completion,
		'project_url'  => get_post_meta( $post_id, '_agencyflow_project_url', true ),
	);
}

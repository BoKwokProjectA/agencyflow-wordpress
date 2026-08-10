<?php
/**
 * Site header.
 *
 * Opens the document and prints the masthead. Every template calls this via
 * get_header(), so the markup exists in exactly one place.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	/*
	 * wp_head() is essential. It is the action hook where WordPress and every
	 * plugin print the things they need in <head>: the stylesheets we
	 * enqueued, the page title, meta tags. Delete it and the site loses its
	 * CSS and half of WordPress stops working.
	 */
	wp_head();
	?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content">Skip to main content</a>

<!--
	<header> is the semantic element for introductory content. Using it
	instead of <div class="header"> means a screen reader can announce
	"banner" and a keyboard user can jump straight past it.
-->
<header class="site-header">
	<div class="wrap site-header__inner">

		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			Agency<span>Flow</span>
		</a>

		<!-- <nav> marks this as the primary navigation landmark. -->
		<nav class="site-nav" aria-label="Primary navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'depth'          => 1,
					'fallback_cb'    => 'agencyflow_nav_fallback',
				)
			);
			?>
		</nav>

	</div>
</header>

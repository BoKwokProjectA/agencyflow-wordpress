<?php
/**
 * Projects archive.
 *
 * TEMPLATE HIERARCHY: for the URL /projects/ WordPress looks for
 * archive-project.php, then archive.php, then index.php. Naming the file
 * this way is the entire mechanism — there is no router to configure.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Fetch every project type that actually has projects attached, so the filter
 * bar never shows a button that would return nothing.
 */
$agencyflow_types = get_terms(
	array(
		'taxonomy'   => 'project_type',
		'hide_empty' => true,
	)
);
?>

<main id="main-content">

	<section class="hero">
		<div class="wrap">
			<p class="eyebrow">Portfolio</p>
			<h1>Our projects</h1>
			<p>
				Websites, e-commerce builds, automations and web applications delivered
				for clients. Use the filters to narrow the list.
			</p>
		</div>
	</section>

	<section class="section">
		<div class="wrap">

			<?php if ( ! is_wp_error( $agencyflow_types ) && ! empty( $agencyflow_types ) ) : ?>
				<!--
					A <fieldset> with a <legend> groups the filter controls and
					names that group for assistive technology. The legend is
					visually hidden because the heading above already says it.

					These are real <button> elements, so they are keyboard
					focusable and respond to Enter and Space for free. A styled
					<div> would need tabindex, key handlers and a role.
				-->
				<fieldset class="filter-bar" id="project-filters">
					<legend class="screen-reader-text">Filter projects by type</legend>

					<button type="button" class="filter-button is-active"
						data-type="all" aria-pressed="true">
						All
					</button>

					<?php foreach ( $agencyflow_types as $agencyflow_type ) : ?>
						<button type="button" class="filter-button"
							data-type="<?php echo esc_attr( $agencyflow_type->slug ); ?>"
							aria-pressed="false">
							<?php echo esc_html( $agencyflow_type->name ); ?>
						</button>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>

			<!--
				role="status" means a screen reader announces the text whenever
				JavaScript changes it, so a non-sighted visitor hears "Showing
				3 of 9 projects" after clicking a filter.
			-->
			<p class="filter-status" id="filter-status" role="status"></p>

			<?php if ( have_posts() ) : ?>

				<div class="project-grid">
					<?php
					// THE LOOP.
					while ( have_posts() ) :
						the_post();

						// get_template_part() pulls in template-parts/project-card.php.
						get_template_part( 'template-parts/project-card' );

					endwhile;
					?>
				</div>

				<!-- Shown by JavaScript only when a filter matches nothing. -->
				<p class="empty-state" id="filter-empty" hidden>
					No projects match that filter yet.
				</p>

				<?php
				$agencyflow_pagination = paginate_links( array( 'type' => 'plain' ) );
				if ( $agencyflow_pagination ) :
					?>
					<nav class="pagination" aria-label="Projects pagination">
						<?php echo wp_kses_post( $agencyflow_pagination ); ?>
					</nav>
				<?php endif; ?>

			<?php else : ?>

				<p class="empty-state">
					No projects published yet. Add some in the WordPress admin under Projects.
				</p>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
get_footer();

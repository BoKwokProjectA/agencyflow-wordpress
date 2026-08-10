<?php
/**
 * Front page template.
 *
 * TEMPLATE HIERARCHY NOTE: WordPress uses front-page.php for the site's home
 * page automatically, ahead of home.php and index.php. No setting needed.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content">

	<!-- <section> groups related content and takes a heading. -->
	<section class="hero">
		<div class="wrap">
			<p class="eyebrow">Manchester digital agency</p>
			<h1>Websites and automation that earn their keep.</h1>
			<p>
				We design, build and maintain WordPress sites, web applications and the
				automations behind them for clients across the North West.
			</p>

			<div class="button-group">
				<?php
				$agencyflow_projects_url = get_post_type_archive_link( 'project' );
				if ( $agencyflow_projects_url ) :
					?>
					<a class="button" href="<?php echo esc_url( $agencyflow_projects_url ); ?>">
						See our work
					</a>
				<?php endif; ?>

				<?php
				$agencyflow_contact = get_page_by_path( 'contact' );
				if ( $agencyflow_contact ) :
					?>
					<a class="button button--ghost" href="<?php echo esc_url( get_permalink( $agencyflow_contact ) ); ?>">
						Start a project
					</a>
				<?php endif; ?>
			</div>

			<!--
				EXTERNAL API TARGET.
				The markup ships with placeholder text; weather.js replaces it
				after fetching from Open-Meteo. aria-live="polite" tells a
				screen reader to announce the update once it arrives, without
				interrupting whatever it is currently reading.
			-->
			<div class="weather" id="weather-strip" aria-live="polite" style="margin-top: 2rem;">
				<span class="weather__label">Right now in Manchester</span>
				<span class="weather__temp" id="weather-temp">…</span>
				<span id="weather-desc">Checking conditions</span>
				<span id="weather-wind"></span>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<header class="section__head">
				<h2>Recent work</h2>
				<p>
					Loaded from this site&rsquo;s own REST API after the page renders, at
					<code>/wp-json/agencyflow/v1/projects</code>.
				</p>
			</header>

			<!--
				REST API TARGET.
				Deliberately empty. featured-projects.js fetches the JSON and
				builds these cards in the browser, which is what demonstrates
				consuming an API rather than only rendering PHP.
			-->
			<div class="project-grid" id="featured-projects">
				<p class="is-loading">Loading projects…</p>
			</div>
		</div>
	</section>

	<section class="section section--paper">
		<div class="wrap">
			<header class="section__head">
				<h2>How we work</h2>
			</header>

			<div class="project-grid">
				<article class="project-card">
					<div class="project-card__body">
						<h3 class="project-card__title">Build it properly</h3>
						<p class="project-card__excerpt">
							Custom themes and plugins rather than a pile of page builders, so
							the site stays fast and you can still edit everything yourself.
						</p>
					</div>
				</article>

				<article class="project-card">
					<div class="project-card__body">
						<h3 class="project-card__title">Connect it up</h3>
						<p class="project-card__excerpt">
							Your website should talk to the tools you already use. We build and
							consume APIs so data moves without anyone retyping it.
						</p>
					</div>
				</article>

				<article class="project-card">
					<div class="project-card__body">
						<h3 class="project-card__title">Hand it over</h3>
						<p class="project-card__excerpt">
							Version control, automated checks and documentation, so the next
							developer &mdash; or you &mdash; can pick it up without a rebuild.
						</p>
					</div>
				</article>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();

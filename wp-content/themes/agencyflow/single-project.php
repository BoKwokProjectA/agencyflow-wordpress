<?php
/**
 * Single project.
 *
 * TEMPLATE HIERARCHY: for /projects/some-project/ WordPress looks for
 * single-project.php, then single.php, then singular.php, then index.php.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$agencyflow_meta  = agencyflow_get_project_meta( get_the_ID() );
	$agencyflow_terms = get_the_terms( get_the_ID(), 'project_type' );
	?>

	<main id="main-content" class="project-single">
		<div class="wrap">

			<article>

				<header class="section__head">
					<?php if ( ! is_wp_error( $agencyflow_terms ) && ! empty( $agencyflow_terms ) ) : ?>
						<div class="tag-list" style="margin-bottom: 0.75rem;">
							<?php foreach ( $agencyflow_terms as $agencyflow_term ) : ?>
								<a class="tag" href="<?php echo esc_url( get_term_link( $agencyflow_term ) ); ?>">
									<?php echo esc_html( $agencyflow_term->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<!-- One h1 per page, and it is the page's subject. -->
					<h1><?php the_title(); ?></h1>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="featured-media">
						<?php the_post_thumbnail( 'large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
					</figure>
				<?php endif; ?>

				<!--
					CSS GRID: two columns on desktop, one on mobile. Content
					and a facts panel are a genuinely two-dimensional layout,
					which is why this is Grid rather than Flexbox.
				-->
				<div class="project-layout">

					<div class="project-content">
						<?php
						/*
						 * the_content() prints the editor content. It is one of
						 * the few places we do NOT escape, because WordPress has
						 * already filtered it and the editor is a trusted admin
						 * user. Escaping here would print raw HTML tags on screen.
						 */
						the_content();
						?>
					</div>

					<!--
						<aside> is correct: this panel is related to the project
						but is not the main narrative. Using <aside> where the
						content is genuinely tangential — and not just because it
						sits on the right — is the distinction that matters.
					-->
					<aside class="project-facts">
						<h2>Project facts</h2>

						<dl>
							<?php if ( ! empty( $agencyflow_meta['client'] ) ) : ?>
								<div>
									<dt>Client</dt>
									<dd><?php echo esc_html( $agencyflow_meta['client'] ); ?></dd>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $agencyflow_meta['completed'] ) ) : ?>
								<div>
									<dt>Completed</dt>
									<dd><?php echo esc_html( $agencyflow_meta['completed'] ); ?></dd>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $agencyflow_meta['technologies'] ) ) : ?>
								<div>
									<dt>Technologies</dt>
									<dd>
										<div class="tag-list">
											<?php foreach ( $agencyflow_meta['technologies'] as $agencyflow_tech ) : ?>
												<span class="tag"><?php echo esc_html( $agencyflow_tech ); ?></span>
											<?php endforeach; ?>
										</div>
									</dd>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $agencyflow_meta['project_url'] ) ) : ?>
								<div>
									<dt>Live site</dt>
									<dd>
										<?php
										/*
										 * esc_url() on output, on top of esc_url_raw() when it
										 * was saved. Escaping happens at the point of output
										 * because that is where the context is known.
										 */
										?>
										<a href="<?php echo esc_url( $agencyflow_meta['project_url'] ); ?>"
											rel="noopener noreferrer" target="_blank">
											Visit site
										</a>
									</dd>
								</div>
							<?php endif; ?>
						</dl>
					</aside>

				</div>

			</article>

			<nav class="button-group" aria-label="Project navigation">
				<?php
				$agencyflow_archive = get_post_type_archive_link( 'project' );
				if ( $agencyflow_archive ) :
					?>
					<a class="button button--ghost" href="<?php echo esc_url( $agencyflow_archive ); ?>">
						&larr; All projects
					</a>
				<?php endif; ?>
			</nav>

		</div>
	</main>

	<?php
endwhile;

get_footer();

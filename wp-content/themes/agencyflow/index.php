<?php
/**
 * Fallback template.
 *
 * WordPress uses this whenever no more specific template matches the
 * request. It is the last stop in the template hierarchy, and every theme
 * is required to have one.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content" class="section">
	<div class="wrap">

		<header class="section__head">
			<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>

			<div class="project-grid">
				<?php
				// THE LOOP. have_posts() asks "is there another post in the
				// results?"; the_post() moves to it and sets up the global
				// post data that the_title(), the_excerpt() and friends read.
				while ( have_posts() ) :
					the_post();
					?>
					<article class="project-card">
						<div class="project-card__body">
							<h2 class="project-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							<?php the_excerpt(); ?>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<nav class="pagination" aria-label="Pagination">
				<?php echo wp_kses_post( paginate_links() ); ?>
			</nav>

		<?php else : ?>
			<p class="empty-state">Nothing published here yet.</p>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();

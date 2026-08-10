<?php
/**
 * Site footer.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- <footer> is the semantic element for closing content: credits, contact. -->
<footer class="site-footer">
	<div class="wrap site-footer__inner">

		<p>
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
			A portfolio project by Bo Kwok.
		</p>

		<p class="eyebrow">Built with WordPress, PHP and vanilla JavaScript</p>

	</div>
</footer>

<?php
/*
 * wp_footer() is the counterpart to wp_head(). Every script enqueued with
 * the 'in footer' flag is printed here. Without it, none of the JavaScript
 * on this site would load.
 */
wp_footer();
?>
</body>
</html>

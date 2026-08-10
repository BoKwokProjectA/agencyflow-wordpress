<?php
/**
 * Contact page.
 *
 * TEMPLATE HIERARCHY: WordPress uses page-{slug}.php for a Page whose slug
 * matches. So a Page with the slug "contact" automatically renders through
 * this file. If the slug is anything else, WordPress falls back to page.php
 * or index.php and the form will not appear — that is the single most likely
 * setup mistake with this template.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$agencyflow_types   = function_exists( 'agencyflow_allowed_project_types' )
	? agencyflow_allowed_project_types()
	: array( 'Website', 'E-commerce', 'Automation', 'Web Application' );

$agencyflow_budgets = function_exists( 'agencyflow_allowed_budgets' )
	? agencyflow_allowed_budgets()
	: array( 'Under £5,000', '£5,000 - £15,000', '£15,000 - £50,000', 'Over £50,000' );
?>

<main id="main-content">

	<section class="hero">
		<div class="wrap">
			<p class="eyebrow">Contact</p>
			<h1><?php the_title(); ?></h1>
			<p>
				Tell us what you are trying to build. We read every enquiry and reply
				within one working day.
			</p>
		</div>
	</section>

	<section class="section">
		<div class="wrap">

			<?php
			// Any content typed into the Page in the admin appears here.
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>

			<h2>Project enquiry</h2>

			<!--
				FORM NOTES worth being able to explain:

				* Every input has a real <label for="..."> pointing at the
				  input's id. That is what lets a screen reader announce the
				  field, and what makes clicking the label focus the input.
				  Placeholder text is not a label — it vanishes on typing.

				* novalidate turns off the browser's own bubble messages so our
				  JavaScript controls the messaging consistently. The inputs keep
				  their type="email" etc. because those still give phones the
				  right keyboard.

				* aria-describedby links each input to its error slot, so the
				  message is read out with the field.

				* required is on the fields that are required, which is
				  information for assistive technology, not just for the browser.
			-->
			<form id="enquiry-form" class="enquiry-form" novalidate>

				<!-- role="status" so changes are announced politely. -->
				<p class="form-status" id="form-status" role="status" aria-live="polite"></p>

				<div class="field-row">
					<div class="field">
						<label for="enquiry-name">Your name</label>
						<input type="text" id="enquiry-name" name="name"
							autocomplete="name" required
							aria-describedby="error-name">
						<span class="field__error" id="error-name"></span>
					</div>

					<div class="field">
						<label for="enquiry-email">Email address</label>
						<input type="email" id="enquiry-email" name="email"
							autocomplete="email" required
							aria-describedby="error-email">
						<span class="field__error" id="error-email"></span>
					</div>
				</div>

				<div class="field">
					<label for="enquiry-company">Company <span class="hint">(optional)</span></label>
					<input type="text" id="enquiry-company" name="company"
						autocomplete="organization"
						aria-describedby="error-company">
					<span class="field__error" id="error-company"></span>
				</div>

				<div class="field-row">
					<div class="field">
						<label for="enquiry-type">Project type</label>
						<select id="enquiry-type" name="project_type" required
							aria-describedby="error-project_type">
							<option value="">Please choose…</option>
							<?php foreach ( $agencyflow_types as $agencyflow_type ) : ?>
								<option value="<?php echo esc_attr( $agencyflow_type ); ?>">
									<?php echo esc_html( $agencyflow_type ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span class="field__error" id="error-project_type"></span>
					</div>

					<div class="field">
						<label for="enquiry-budget">Budget range</label>
						<select id="enquiry-budget" name="budget" required
							aria-describedby="error-budget">
							<option value="">Please choose…</option>
							<?php foreach ( $agencyflow_budgets as $agencyflow_budget ) : ?>
								<option value="<?php echo esc_attr( $agencyflow_budget ); ?>">
									<?php echo esc_html( $agencyflow_budget ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span class="field__error" id="error-budget"></span>
					</div>
				</div>

				<div class="field">
					<label for="enquiry-message">About your project</label>
					<textarea id="enquiry-message" name="message" required
						aria-describedby="error-message enquiry-message-hint"></textarea>
					<span class="hint" id="enquiry-message-hint">
						What are you building, and what does success look like? At least 20 characters.
					</span>
					<span class="field__error" id="error-message"></span>
				</div>

				<div>
					<button type="submit" class="button">Send enquiry</button>
				</div>

			</form>

		</div>
	</section>

</main>

<?php
get_footer();

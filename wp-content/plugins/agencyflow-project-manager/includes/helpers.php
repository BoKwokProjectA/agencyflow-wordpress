<?php
/**
 * Pure helper functions.
 *
 * IMPORTANT: nothing in this file may call a WordPress function.
 * That is deliberate — it means these functions can be loaded and tested
 * by PHPUnit without booting WordPress, which keeps the test suite fast
 * and simple. Everything that needs WordPress lives in the other includes.
 *
 * @package AgencyFlow_Project_Manager
 */

/**
 * The project types a visitor is allowed to choose in the enquiry form.
 *
 * Kept in one place so the form, the validator and the admin all agree.
 *
 * @return array<int, string>
 */
function agencyflow_allowed_project_types() {
	return array( 'Website', 'E-commerce', 'Automation', 'Web Application' );
}

/**
 * The budget ranges a visitor is allowed to choose.
 *
 * @return array<int, string>
 */
function agencyflow_allowed_budgets() {
	return array( 'Under £5,000', '£5,000 - £15,000', '£15,000 - £50,000', 'Over £50,000' );
}

/**
 * Turn a comma separated technologies string into a clean array.
 *
 * "WordPress, PHP,  , JavaScript" becomes array( 'WordPress', 'PHP', 'JavaScript' ).
 *
 * @param string $raw Raw comma separated value from the meta field.
 * @return array<int, string>
 */
function agencyflow_split_technologies( $raw ) {

	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return array();
	}

	$parts = explode( ',', $raw );
	$clean = array();

	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( '' !== $part ) {
			$clean[] = $part;
		}
	}

	return $clean;
}

/**
 * Format a stored YYYY-MM-DD date as a human readable "March 2025".
 *
 * Returns an empty string for anything that is not a valid date, so the
 * templates never print half-parsed rubbish to the page.
 *
 * @param string $date Date in YYYY-MM-DD format.
 * @return string
 */
function agencyflow_format_completion_date( $date ) {

	if ( ! is_string( $date ) || '' === trim( $date ) ) {
		return '';
	}

	// Must look exactly like YYYY-MM-DD before we trust it.
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches ) ) {
		return '';
	}

	$year  = (int) $matches[1];
	$month = (int) $matches[2];
	$day   = (int) $matches[3];

	// checkdate() rejects things like 2025-02-30.
	if ( ! checkdate( $month, $day, $year ) ) {
		return '';
	}

	$month_names = array(
		1  => 'January',
		2  => 'February',
		3  => 'March',
		4  => 'April',
		5  => 'May',
		6  => 'June',
		7  => 'July',
		8  => 'August',
		9  => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	);

	return $month_names[ $month ] . ' ' . $year;
}

/**
 * Validate an enquiry submission.
 *
 * Returns an array of field name => error message. An empty array means
 * the submission is valid. This runs on the server, after the browser's
 * own JavaScript checks, because client-side validation can be skipped
 * entirely by anyone sending a request directly to the endpoint.
 *
 * @param array<string, string> $data Already-sanitised enquiry fields.
 * @return array<string, string>
 */
function agencyflow_validate_enquiry( $data ) {

	$errors = array();

	$name = isset( $data['name'] ) ? trim( $data['name'] ) : '';
	if ( '' === $name ) {
		$errors['name'] = 'Enter your name.';
	} elseif ( strlen( $name ) < 2 ) {
		$errors['name'] = 'Your name needs at least 2 characters.';
	}

	$email = isset( $data['email'] ) ? trim( $data['email'] ) : '';
	if ( '' === $email ) {
		$errors['email'] = 'Enter your email address.';
	} elseif ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		$errors['email'] = 'Enter a valid email address, for example name@company.com.';
	}

	$project_type = isset( $data['project_type'] ) ? trim( $data['project_type'] ) : '';
	if ( '' === $project_type ) {
		$errors['project_type'] = 'Choose a project type.';
	} elseif ( ! in_array( $project_type, agencyflow_allowed_project_types(), true ) ) {
		$errors['project_type'] = 'Choose a project type from the list.';
	}

	$budget = isset( $data['budget'] ) ? trim( $data['budget'] ) : '';
	if ( '' === $budget ) {
		$errors['budget'] = 'Choose a budget range.';
	} elseif ( ! in_array( $budget, agencyflow_allowed_budgets(), true ) ) {
		$errors['budget'] = 'Choose a budget range from the list.';
	}

	$message = isset( $data['message'] ) ? trim( $data['message'] ) : '';
	if ( '' === $message ) {
		$errors['message'] = 'Tell us about your project.';
	} elseif ( strlen( $message ) < 20 ) {
		$errors['message'] = 'Please give us a little more detail — at least 20 characters.';
	}

	// Company is optional, so it is only length-checked when present.
	$company = isset( $data['company'] ) ? trim( $data['company'] ) : '';
	if ( '' !== $company && strlen( $company ) > 100 ) {
		$errors['company'] = 'Company name is too long.';
	}

	return $errors;
}

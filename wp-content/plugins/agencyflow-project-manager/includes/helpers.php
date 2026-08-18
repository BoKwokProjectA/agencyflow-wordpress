<?php
/**
 * Pure helper functions with no WordPress dependencies.
 *
 * @package AgencyFlow_Project_Manager
 */

/**
 * Return the allowed enquiry project types.
 *
 * @return array<int, string>
 */
function agencyflow_allowed_project_types() {
	return array( 'Website', 'E-commerce', 'Automation', 'Web Application' );
}

/**
 * Return the allowed enquiry budget ranges.
 *
 * @return array<int, string>
 */
function agencyflow_allowed_budgets() {
	return array( 'Under £5,000', '£5,000 - £15,000', '£15,000 - £50,000', 'Over £50,000' );
}

/**
 * Split a comma-separated technologies value into a clean array.
 *
 * @param string $raw Raw comma-separated value from the meta field.
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
 * Format a stored YYYY-MM-DD date as a month and year.
 *
 * @param string $date Date in YYYY-MM-DD format.
 * @return string
 */
function agencyflow_format_completion_date( $date ) {

	if ( ! is_string( $date ) || '' === trim( $date ) ) {
		return '';
	}

	// Require the stored YYYY-MM-DD format.
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches ) ) {
		return '';
	}

	$year  = (int) $matches[1];
	$month = (int) $matches[2];
	$day   = (int) $matches[3];

	// Reject invalid calendar dates.
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

	// Validate the optional company name when provided.
	$company = isset( $data['company'] ) ? trim( $data['company'] ) : '';
	if ( '' !== $company && strlen( $company ) > 100 ) {
		$errors['company'] = 'Company name is too long.';
	}

	return $errors;
}

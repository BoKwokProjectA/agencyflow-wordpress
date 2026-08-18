<?php
/**
 * Unit tests for AgencyFlow helper functions.
 *
 * @package AgencyFlow
 */

use PHPUnit\Framework\TestCase;

/**
 * Class HelpersTest
 */
final class HelpersTest extends TestCase {

	/* ------------------------------------------------------------------
	 * agencyflow_split_technologies()
	 * ---------------------------------------------------------------- */

	public function test_split_technologies_returns_trimmed_array() {
		$this->assertSame(
			array( 'WordPress', 'PHP', 'JavaScript' ),
			agencyflow_split_technologies( 'WordPress, PHP,   JavaScript' )
		);
	}

	public function test_split_technologies_drops_empty_entries() {
		$this->assertSame(
			array( 'WordPress', 'PHP' ),
			agencyflow_split_technologies( 'WordPress, , PHP,,' )
		);
	}

	public function test_split_technologies_handles_empty_input() {
		$this->assertSame( array(), agencyflow_split_technologies( '' ) );
		$this->assertSame( array(), agencyflow_split_technologies( '   ' ) );
	}

	public function test_split_technologies_handles_single_value() {
		$this->assertSame( array( 'WordPress' ), agencyflow_split_technologies( 'WordPress' ) );
	}

	/* ------------------------------------------------------------------
	 * agencyflow_format_completion_date()
	 * ---------------------------------------------------------------- */

	public function test_format_completion_date_formats_a_valid_date() {
		$this->assertSame( 'March 2025', agencyflow_format_completion_date( '2025-03-14' ) );
	}

	public function test_format_completion_date_handles_january_and_december() {
		$this->assertSame( 'January 2024', agencyflow_format_completion_date( '2024-01-01' ) );
		$this->assertSame( 'December 2026', agencyflow_format_completion_date( '2026-12-31' ) );
	}

	public function test_format_completion_date_rejects_wrong_format() {
		$this->assertSame( '', agencyflow_format_completion_date( '14/03/2025' ) );
		$this->assertSame( '', agencyflow_format_completion_date( '2025-3-4' ) );
		$this->assertSame( '', agencyflow_format_completion_date( 'not a date' ) );
	}

	public function test_format_completion_date_rejects_impossible_dates() {
		// Reject impossible calendar dates.
		$this->assertSame( '', agencyflow_format_completion_date( '2025-02-30' ) );
		$this->assertSame( '', agencyflow_format_completion_date( '2025-13-01' ) );
	}

	public function test_format_completion_date_handles_empty_input() {
		$this->assertSame( '', agencyflow_format_completion_date( '' ) );
	}

	/* ------------------------------------------------------------------
	 * agencyflow_validate_enquiry()
	 * ---------------------------------------------------------------- */

	/**
	 * A complete, valid submission.
	 *
	 * @return array<string, string>
	 */
	private function valid_enquiry() {
		return array(
			'name'         => 'Bo Kwok',
			'email'        => 'bo@example.com',
			'company'      => 'Example Ltd',
			'project_type' => 'Website',
			'budget'       => 'Under £5,000',
			'message'      => 'We need a new brochure site with a booking form and a blog.',
		);
	}

	public function test_valid_enquiry_produces_no_errors() {
		$this->assertSame( array(), agencyflow_validate_enquiry( $this->valid_enquiry() ) );
	}

	public function test_company_is_optional() {
		$data            = $this->valid_enquiry();
		$data['company'] = '';

		$this->assertSame( array(), agencyflow_validate_enquiry( $data ) );
	}

	public function test_missing_name_is_reported() {
		$data         = $this->valid_enquiry();
		$data['name'] = '';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'name', $errors );
	}

	public function test_malformed_email_is_reported() {
		$data          = $this->valid_enquiry();
		$data['email'] = 'notanemail';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'email', $errors );
	}

	public function test_project_type_outside_the_allowed_list_is_rejected() {
		// Reject values outside the allowed project types.
		$data                 = $this->valid_enquiry();
		$data['project_type'] = 'Something I invented';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'project_type', $errors );
	}

	public function test_budget_outside_the_allowed_list_is_rejected() {
		$data           = $this->valid_enquiry();
		$data['budget'] = 'Free please';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'budget', $errors );
	}

	public function test_short_message_is_reported() {
		$data            = $this->valid_enquiry();
		$data['message'] = 'Too short';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'message', $errors );
	}

	public function test_completely_empty_submission_reports_every_required_field() {
		$errors = agencyflow_validate_enquiry( array() );

		$this->assertArrayHasKey( 'name', $errors );
		$this->assertArrayHasKey( 'email', $errors );
		$this->assertArrayHasKey( 'project_type', $errors );
		$this->assertArrayHasKey( 'budget', $errors );
		$this->assertArrayHasKey( 'message', $errors );
		$this->assertCount( 5, $errors );
	}

	public function test_whitespace_only_name_is_treated_as_empty() {
		$data         = $this->valid_enquiry();
		$data['name'] = '     ';

		$errors = agencyflow_validate_enquiry( $data );

		$this->assertArrayHasKey( 'name', $errors );
	}
}

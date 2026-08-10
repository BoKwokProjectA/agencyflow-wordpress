<?php
/**
 * PHPUnit bootstrap.
 *
 * The helpers file guards itself with `if ( ! defined( 'ABSPATH' ) ) exit;`
 * in the other plugin files, but helpers.php deliberately has no such guard
 * and no WordPress calls, which is what lets us load it here on its own.
 *
 * Defining ABSPATH keeps us consistent with how WordPress would load it.
 *
 * @package AgencyFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/../wp-content/plugins/agencyflow-project-manager/includes/helpers.php';

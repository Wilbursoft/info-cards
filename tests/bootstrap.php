<?php
/**
 * PHPUnit bootstrap file
 *
 * @package info_cards
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 * Note: exclude from coverage 
 */
// @codeCoverageIgnoreStart
function _manually_load_plugin() {
	
	// Ensure the plugins needed are activated
	$plugins_to_active = array(
		"wp-info-cards/info-cards.php"
	);

	update_option( 'active_plugins', $plugins_to_active );
}
// @codeCoverageIgnoreEnd


tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

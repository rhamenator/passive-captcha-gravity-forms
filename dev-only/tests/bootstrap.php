<?php
/**
 * PHPUnit bootstrap for Passive CAPTCHA plugin.
 */

// Allow this to be overridden by environment variable in CI.
// (phpunit.xml already set WP_TESTS_DIR and WP_TESTS_CONFIG_FILE_PATH)
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Load WordPress test functions
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load our plugin before the rest of WP is loaded.
 */
tests_add_filter( 'muplugins_loaded', function() {
    // Adjust path if your plugin lives in a subdirectory.
    require dirname( dirname( __FILE__ ) ) . '/../passive-captcha.php';
} );

// Boot up WordPress testing environment
require_once $_tests_dir . '/includes/bootstrap.php';

<?php
/**
 * PHPUnit bootstrap file for Passive CAPTCHA Hardened plugin.
 */

$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress/tests/phpunit';

// Load the WordPress test functions.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _load_passive_captcha_plugin() {
    require dirname(__DIR__) . '/passive-captcha-hardened.php';
}
tests_add_filter('muplugins_loaded', '_load_passive_captcha_plugin');

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Ensure plugin activation.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_slug = 'passive-captcha-hardened/passive-captcha-hardened.php';

if (!is_plugin_active($plugin_slug)) {
    activate_plugin($plugin_slug);
    if (!is_plugin_active($plugin_slug)) {
        echo "Error: Failed to activate plugin {$plugin_slug}\n";
        exit(1);
    }
}

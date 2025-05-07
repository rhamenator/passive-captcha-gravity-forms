<?php
/**
 * Basic PHPUnit tests for Passive CAPTCHA plugin.
 *
 * @package PassiveCaptcha
 */

class Tests_Passive_Captcha_Plugin extends WP_UnitTestCase {

    /**
     * Plugin main file loads without fatal error.
     */
    public function test_plugin_loaded() {
        $this->assertTrue( function_exists( 'pch_register_settings' ), 'pch_register_settings() should be defined.' );
        $this->assertTrue( function_exists( 'pch_enqueue_passive_captcha_script' ), 'pch_enqueue_passive_captcha_script() should be defined.' );
    }

    /**
     * Activation hook should register default settings option.
     */
    public function test_default_settings_option_exists() {
        // Simulate activation to ensure defaults are created.
        // If you have register_activation_hook that seeds defaults, call it here:
        // activate_plugin( 'passive-captcha/passive-captcha.php' );

        $opts = get_option( 'pch_settings' );
        $this->assertIsArray( $opts, 'pch_settings option should be an array.' );

        // Check for a couple of expected default keys
        $this->assertArrayHasKey( 'rate_limit', $opts, 'rate_limit should be present in settings.' );
        $this->assertArrayHasKey( 'min_time_threshold', $opts, 'min_time_threshold should be present in settings.' );
    }

    /**
     * The settings page should be registered under the “options” menu.
     */
    public function test_settings_page_registered() {
        global $submenu;

        // Look for our “pch-settings” submenu under options-general.php
        $found = false;
        if ( isset( $submenu['options-general.php'] ) ) {
            foreach ( $submenu['options-general.php'] as $item ) {
                if ( isset( $item[2] ) && 'pch-settings' === $item[2] ) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue( $found, 'Passive CAPTCHA settings page should be added under Settings→Passive CAPTCHA.' );
    }

    /**
     * Front-end script should be enqueued on the front.
     */
    public function test_frontend_script_enqueued() {
        // Simulate front-end request
        do_action( 'wp_enqueue_scripts' );

        // Our handle is 'passive-captcha' as per index.php Registration
        $this->assertTrue( wp_script_is( 'passive-captcha', 'registered' ), 'passive-captcha script should be registered.' );
        $this->assertTrue( wp_script_is( 'passive-captcha', 'enqueued' ), 'passive-captcha script should be enqueued.' );
    }
}

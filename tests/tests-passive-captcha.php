<?php
// tests/tests-passive-captcha.php - Gravity Forms Version - Configurable

use Yoast\PHPUnitPolyfills\TestCases\TestCase; // Or ensure WP_UnitTestCase is available

class PassiveCaptchaTest extends WP_UnitTestCase { // Or extends TestCase

    private $mock_ip = '192.0.2.5';
    private $mock_ua = 'Mozilla/5.0 Test Agent';
    private $mock_ja3 = 'mock-ja3-fingerprint-string';
    private $hidden_field_id = 10; // Example field ID for the hidden CAPTCHA token field

    // Store original SERVER state
    private $original_server;

    public function setUp(): void {
        parent::setUp();
        // Store original $_SERVER
        $this->original_server = $_SERVER;

        // Reset options relevant to tests before each run
        $options_to_reset = [
            'pch_rate_limit_threshold', 'pch_ban_duration', 'pch_ip_whitelist',
            'pch_ip_blacklist', 'pch_webhook_url', 'pch_webhook_hmac_key',
            'pch_min_time_threshold', 'pch_min_hash_length', 'pch_session_lifetime', // New options
            'pch_custom_ip_header', 'pch_enable_webgl', 'pch_enable_math'             // New options
        ];
        foreach ($options_to_reset as $option) {
            // Use multi-site aware delete function if available
            if (function_exists('delete_site_option')) {
                delete_site_option($option);
            } else {
                delete_option($option);
            }
        }

        // Clear potentially lingering failure transient for mock IP
        delete_transient('pch_fail_' . md5($this->mock_ip));

        // Mock server variables - essential for the function
        $_SERVER['REMOTE_ADDR'] = $this->mock_ip;
        $_SERVER['HTTP_USER_AGENT'] = $this->mock_ua;
        $_SERVER['HTTP_X_JA3_FINGERPRINT'] = $this->mock_ja3; // Matches key in pch_validate_passive_captcha

        // Mock rgpost if Gravity Forms isn't fully loaded in test env
        // This allows tests to simulate submitted data via $_POST
        if (!function_exists('rgpost')) {
            function rgpost($key) {
                // Basic mock: assumes rgpost simply reads from $_POST
                return $_POST[$key] ?? null;
            }
        }
        // Clear POST data at the start of each test
        $_POST = [];
    }

    public function tearDown(): void {
        // Restore original $_SERVER
        $_SERVER = $this->original_server;
        // Clean up POST data
        $_POST = [];
        parent::tearDown();
    }

    // Helper to create a mock form structure for GF tests
    private function create_mock_gf_form() {
        $form = [
            'id' => 1, // Example form ID
            'fields' => [
                (object)[ // Simulate a GF_Field object structure
                    'id' => $this->hidden_field_id,
                    'type' => 'hidden',
                    'label' => 'CAPTCHA Token', // Label must contain 'CAPTCHA Token'
                    // Properties the function might set
                    'failed_validation' => null, // Start as null
                    'validation_message' => null, // Start as null
                ]
                // Add other mock fields if your tests require them
            ]
        ];
        return $form;
    }

    // --- Helper Function Tests ---

    public function testRateLimitDefaults() {
        $this->assertEquals(5, pch_get_option('pch_rate_limit_threshold', 5));
        $this->assertEquals(3600, pch_get_option('pch_ban_duration', 3600));
    }

    public function testRateLimitIncrements() {
        $ip = '192.0.2.1';
        delete_transient('pch_fail_' . md5($ip));
        $this->assertFalse(pch_check_rate_limit($ip));
        pch_update_option('pch_rate_limit_threshold', 3);
        pch_update_option('pch_ban_duration', 60);
        pch_register_failure($ip); // 1
        $this->assertFalse(pch_check_rate_limit($ip));
        pch_register_failure($ip); // 2
        $this->assertFalse(pch_check_rate_limit($ip));
        pch_register_failure($ip); // 3
        $this->assertTrue(pch_check_rate_limit($ip));
        delete_transient('pch_fail_' . md5($ip));
    }

    public function testIpWhitelistBlacklistFunctions() {
        $whitelisted_ip = '192.0.2.100';
        $blacklisted_ip = '203.0.113.1';
        $other_ip = '8.8.8.8';
        pch_update_option('pch_ip_whitelist', $whitelisted_ip . "\n198.51.100.1");
        pch_update_option('pch_ip_blacklist', $blacklisted_ip . "\n198.51.100.99");
        $this->assertTrue(pch_is_ip_whitelisted($whitelisted_ip));
        $this->assertFalse(pch_is_ip_whitelisted($other_ip));
        $this->assertTrue(pch_is_ip_blacklisted($blacklisted_ip));
        $this->assertFalse(pch_is_ip_blacklisted($other_ip));
    }

    public function testWebhookSignatureGeneration() {
        pch_update_option('pch_webhook_hmac_key', 'testsecret');
        $payload = ['event' => 'test', 'ip' => '127.0.0.1'];
        $body = wp_json_encode($payload);
        $expected_hmac = hash_hmac('sha256', $body, 'testsecret');
        $this->assertEquals($expected_hmac, hash_hmac('sha256', $body, pch_get_option('pch_webhook_hmac_key')));
    }

    // --- Test for pch_get_visitor_ip() ---
    public function testGetVisitorIpPrioritization() {
        // Test Case 1: Standard X-Forwarded-For
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1, 192.168.1.1';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1'; // Proxy IP
        $this->assertEquals('1.1.1.1', pch_get_visitor_ip(), 'Should get first public IP from XFF');

        // Test Case 2: Cloudflare Header
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '2.2.2.2';
        $this->assertEquals('2.2.2.2', pch_get_visitor_ip(), 'Should prioritize CF header');
        unset($_SERVER['HTTP_CF_CONNECTING_IP']); // Clean up for next test

        // Test Case 3: X-Real-IP Header
        $_SERVER['HTTP_X_REAL_IP'] = '3.3.3.3';
        $this->assertEquals('3.3.3.3', pch_get_visitor_ip(), 'Should prioritize X-Real-IP');
        unset($_SERVER['HTTP_X_REAL_IP']);

        // Test Case 4: Custom Header Set in Options
        $custom_header = 'HTTP_X_MY_CUSTOM_IP';
        pch_update_option('pch_custom_ip_header', $custom_header);
        $_SERVER[$custom_header] = '4.4.4.4';
        $this->assertEquals('4.4.4.4', pch_get_visitor_ip(), 'Should prioritize Custom header from options');
        unset($_SERVER[$custom_header]);
        pch_update_option('pch_custom_ip_header', ''); // Reset option

        // Test Case 5: Fallback to REMOTE_ADDR
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->assertEquals('10.0.0.1', pch_get_visitor_ip(), 'Should fall back to REMOTE_ADDR');
    }


    // --- VALIDATION TEST CASES for pch_validate_passive_captcha() ---

    public function testValidationPassesWithValidData() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 12 * HOUR_IN_SECONDS); // Use default long lifetime
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // > default 3000ms
        $nav_hash = 'valid_navigator_data_long_enough'; // > default 10 chars
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        // Simulate submitted POST data for rgpost()
        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_gf_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertNull($result_field->failed_validation, 'Validation should pass');
        $this->assertNull($result_field->validation_message, 'Validation message should be null');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient deleted on success');
    }

    // --- Standard Failure Tests ---
    // (These test the core logic paths, assuming default thresholds)

    public function testValidationFailsOnBlacklistedIp() {
        pch_update_option('pch_ip_blacklist', $this->mock_ip);
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertStringContainsStringIgnoringCase('blacklisted', $result_field->validation_message);
    }

    public function testValidationSucceedsOnWhitelistedIp() {
        pch_update_option('pch_ip_whitelist', $this->mock_ip);
        $_POST['pch_nonce'] = 'invalid'; // This would normally fail
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertNull($result_field->failed_validation, 'Whitelist should bypass other checks');
    }

     public function testValidationFailsOnRateLimitExceeded() {
        pch_update_option('pch_rate_limit_threshold', 1);
        pch_register_failure($this->mock_ip);
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertStringContainsStringIgnoringCase('blocked', $result_field->validation_message);
    }

    public function testValidationFailsOnInvalidNonce() {
        $session_token = 's'; set_transient('pch_'.$session_token, time(), 60);
        $_POST['pch_nonce'] = 'invalid'; $_POST['pch_session'] = $session_token;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message); // Check generic message
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }

    public function testValidationFailsOnExpiredSession() {
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = 'expired';
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }

    public function testValidationFailsOnIpUaMismatch() {
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60);
        $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = 'badhash';
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_'.$session_token));
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }

    public function testValidationFailsOnNoInteractionValue() {
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60); $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $_POST['input_' . $this->hidden_field_id] = 'no_interaction'; $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = $ip_hash;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_'.$session_token));
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }

     public function testValidationFailsOnEmptyTokenValue() {
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60); $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $_POST['input_' . $this->hidden_field_id] = ''; $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = $ip_hash;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_'.$session_token));
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }

    public function testValidationFailsOnInvalidTokenFormat() {
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60); $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $_POST['input_' . $this->hidden_field_id] = '!!!'; $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = $ip_hash;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_'.$session_token));
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }


    // --- Tests for Configurable Thresholds ---

    public function testValidationFailsOnTimingFailureWithCustomThreshold() {
        pch_update_option('pch_min_time_threshold', 5000); // Set custom threshold
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60); $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 4000; // FAILS custom threshold
        $nav_hash = 'valid_navigator_hash_long_enough';
        $token_value = base64_encode($time_spent . ':' . $nav_hash);
        $_POST['input_' . $this->hidden_field_id] = $token_value; $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = $ip_hash;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on custom timing');
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_' . $session_token));
    }

     public function testValidationFailsOnFingerprintHashFailureWithCustomThreshold() {
        pch_update_option('pch_min_hash_length', 20); // Set custom threshold
        $nonce = wp_create_nonce('pch_captcha_nonce'); $session_token = 's'; set_transient('pch_'.$session_token, time(), 60); $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // Valid time
        $nav_hash = 'short_hash_15'; // FAILS custom threshold
        $token_value = base64_encode($time_spent . ':' . $nav_hash);
        $_POST['input_' . $this->hidden_field_id] = $token_value; $_POST['pch_nonce'] = $nonce; $_POST['pch_session'] = $session_token; $_POST['pch_iphash'] = $ip_hash;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on custom short hash');
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_' . $session_token));
    }

} // End class PassiveCaptchaTest

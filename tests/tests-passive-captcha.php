<?php
// tests/tests-passive-captcha.php

use Yoast\PHPUnitPolyfills\TestCases\TestCase; // Use this if WP_UnitTestCase isn't found directly, or ensure WP test env provides it.

// If WP_UnitTestCase is globally available via bootstrap, you might not need the 'use' statement above.
// Ensure your bootstrap correctly loads the WordPress test environment.

class PassiveCaptchaTest extends WP_UnitTestCase { // Or extends TestCase if WP_UnitTestCase isn't resolved

    private $mock_ip = '192.0.2.5';
    private $mock_ua = 'Mozilla/5.0 Test Agent';
    private $mock_ja3 = 'mock-ja3-fingerprint-string'; // Example valid JA3
    private $hidden_field_id = 10; // Example field ID for the hidden CAPTCHA token field

    public function setUp(): void {
        parent::setUp();
        // Reset options relevant to tests before each run
        // Use multi-site aware functions if testing in multisite context
        if (function_exists('delete_site_option')) {
             delete_site_option('pch_rate_limit_threshold');
             delete_site_option('pch_ban_duration');
             delete_site_option('pch_ip_whitelist');
             delete_site_option('pch_ip_blacklist');
             delete_site_option('pch_webhook_url');
             delete_site_option('pch_webhook_hmac_key');
        } else {
             delete_option('pch_rate_limit_threshold');
             delete_option('pch_ban_duration');
             delete_option('pch_ip_whitelist');
             delete_option('pch_ip_blacklist');
             delete_option('pch_webhook_url');
             delete_option('pch_webhook_hmac_key');
        }


        // Clear potentially lingering failure transient for mock IP
        delete_transient('pch_fail_' . md5($this->mock_ip));

        // Mock server variables - essential for the function
        $_SERVER['REMOTE_ADDR'] = $this->mock_ip;
        $_SERVER['HTTP_USER_AGENT'] = $this->mock_ua;
        // Ensure the JA3 header key matches the one used in pch_validate_passive_captcha
        // Remember NGINX/Apache often prefix headers with HTTP_ and use underscores
        $_SERVER['HTTP_X_JA3_FINGERPRINT'] = $this->mock_ja3;

        // Ensure Gravity Forms rgpost function is available or mock it if necessary
        if (!function_exists('rgpost')) {
            // Basic mock for testing if GF isn't fully loaded in test env
            function rgpost($key) {
                return $_POST[$key] ?? null;
            }
        }
         // Clear POST data at the start of each test
        $_POST = [];
    }

    public function tearDown(): void {
        // Clean up mocked globals
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SERVER['HTTP_USER_AGENT']);
        unset($_SERVER['HTTP_X_JA3_FINGERPRINT']);
        // Clean up POST data
        $_POST = [];
        parent::tearDown();
    }

    // Helper to create a mock form structure
    private function create_mock_form() {
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

    // --- Original Test Cases ---

    public function testRateLimitDefaults() {
        $this->assertEquals(5, pch_get_option('pch_rate_limit_threshold', 5));
        $this->assertEquals(3600, pch_get_option('pch_ban_duration', 3600));
    }

    public function testRateLimitIncrements() {
        $ip = '192.0.2.1'; // Use a different IP for this specific test if needed
        delete_transient('pch_fail_' . md5($ip)); // Ensure clean slate

        $this->assertFalse(pch_check_rate_limit($ip), 'Rate limit should initially be false');

        // Set threshold for test clarity
        pch_update_option('pch_rate_limit_threshold', 3);
        pch_update_option('pch_ban_duration', 60);

        pch_register_failure($ip); // 1st failure
        $this->assertFalse(pch_check_rate_limit($ip), 'Rate limit should be false after 1 failure');
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($ip)));

        pch_register_failure($ip); // 2nd failure
        $this->assertFalse(pch_check_rate_limit($ip), 'Rate limit should be false after 2 failures');
        $this->assertEquals(2, (int) get_transient('pch_fail_' . md5($ip)));

        pch_register_failure($ip); // 3rd failure - should trigger ban
        $this->assertTrue(pch_check_rate_limit($ip), 'Rate limit should be true after 3 failures');
        $this->assertEquals(3, (int) get_transient('pch_fail_' . md5($ip)));

         // Clean up transient for this IP
        delete_transient('pch_fail_' . md5($ip));
    }

    public function testIpWhitelistBlacklist() {
        $whitelisted_ip = '192.0.2.100';
        $blacklisted_ip = '203.0.113.1';
        $other_ip = '8.8.8.8';

        pch_update_option('pch_ip_whitelist', $whitelisted_ip . "\n198.51.100.1");
        pch_update_option('pch_ip_blacklist', $blacklisted_ip . "\n198.51.100.99");

        $this->assertTrue(pch_is_ip_whitelisted($whitelisted_ip));
        $this->assertFalse(pch_is_ip_whitelisted($other_ip));
        $this->assertFalse(pch_is_ip_whitelisted($blacklisted_ip)); // Ensure blacklist doesn't affect whitelist check

        $this->assertTrue(pch_is_ip_blacklisted($blacklisted_ip));
        $this->assertFalse(pch_is_ip_blacklisted($other_ip));
        $this->assertFalse(pch_is_ip_blacklisted($whitelisted_ip)); // Ensure whitelist doesn't affect blacklist check
    }

    public function testWebhookSignatureGeneration() {
        pch_update_option('pch_webhook_hmac_key', 'testsecret');
        $payload = ['event' => 'test', 'ip' => '127.0.0.1'];
        $body = json_encode($payload);
        $expected_hmac = hash_hmac('sha256', $body, 'testsecret');

        // We can't directly test wp_remote_post easily here,
        // but we can test the HMAC generation which is part of pch_send_webhook logic.
        // A more advanced test could mock wp_remote_post.
        $this->assertEquals($expected_hmac, hash_hmac('sha256', $body, pch_get_option('pch_webhook_hmac_key')), 'Generated HMAC should match expected');
    }


    // --- NEW VALIDATION TEST CASES ---

    public function testValidationPassesWithValidData() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600); // Valid session
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // Valid time
        $nav_hash = 'valid_navigator_data_long_enough'; // Valid fingerprint hash string
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        // Mock submitted POST data
        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertNull($result_field->failed_validation, 'Validation should pass (failed_validation is null)');
        $this->assertNull($result_field->validation_message, 'Validation message should be null on pass');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted on success');
        // Failure count should remain 0
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(0, (int) get_transient($fail_key), 'Failure count should be 0 on success');
    }

    public function testValidationFailsOnInvalidNonce() {
        // 1. Arrange
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $token_value = base64_encode('5000:validhashlongenough');

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = 'invalid-nonce-value'; // Incorrect nonce
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail (failed_validation is true)');
        $this->assertStringContainsStringIgnoringCase('nonce', $result_field->validation_message, 'Validation message should mention nonce');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        // Session SHOULD be deleted even on nonce failure if it exists
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on nonce failure');
    }

    public function testValidationFailsOnMissingJA3() {
        // 1. Arrange
        unset($_SERVER['HTTP_X_JA3_FINGERPRINT']); // Simulate missing header
        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on missing JA3');
        $this->assertStringContainsStringIgnoringCase('JA3', $result_field->validation_message, 'Validation message should mention JA3');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP due to JA3 fail');
    }

     public function testValidationFailsOnExpiredSession() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'expired_session_' . uniqid();
        // Do not set the transient, simulating expiry/invalidity
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $token_value = base64_encode('5000:validhashlongenough');

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token; // Session token submitted, but no valid transient exists
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on expired/missing session');
        $this->assertStringContainsStringIgnoringCase('session', $result_field->validation_message, 'Validation message should mention session');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        // Ensure the non-existent transient is still "false" after check
        $this->assertFalse(get_transient('pch_' . $session_token), 'Expired/non-existent session transient should remain false');
    }

    public function testValidationFailsOnBlacklistedIp() {
        // 1. Arrange
        pch_update_option('pch_ip_blacklist', $this->mock_ip . "\n1.2.3.4");
        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail for blacklisted IP');
        $this->assertStringContainsStringIgnoringCase('blacklisted', $result_field->validation_message, 'Validation message should mention blacklist');
        // Failure count shouldn't increase here as it fails before that logic
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(0, (int) get_transient($fail_key), 'Failure count should be 0 for blacklist fail');
    }

    public function testValidationSkipsChecksOnWhitelistedIp() {
        // 1. Arrange
        pch_update_option('pch_ip_whitelist', "8.8.8.8\n" . $this->mock_ip);
        $form = $this->create_mock_form();
        // Provide data that would fail later checks
        $_POST['input_' . $this->hidden_field_id] = 'invalid-token';
        $_POST['pch_nonce'] = 'invalid-nonce';
        // Unset JA3 to ensure it's skipped
        unset($_SERVER['HTTP_X_JA3_FINGERPRINT']);

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertNull($result_field->failed_validation, 'Validation should be skipped (null) for whitelisted IP');
        $this->assertNull($result_field->validation_message, 'Validation message should be null for whitelisted IP');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(0, (int) get_transient($fail_key), 'Failure count should be 0 for whitelist success');
    }

    public function testValidationFailsOnRateLimitExceeded() {
        // 1. Arrange
        pch_update_option('pch_rate_limit_threshold', 2);
        pch_update_option('pch_ban_duration', 60);
        pch_register_failure($this->mock_ip);
        pch_register_failure($this->mock_ip);
        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail when rate limit exceeded');
        $this->assertStringContainsStringIgnoringCase('blocked', $result_field->validation_message, 'Validation message should mention blocking');
        // Failure count should remain at the threshold
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(2, (int) get_transient($fail_key), 'Failure count should remain 2 for rate limit fail');
    }

    public function testValidationFailsOnIpUaMismatch() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $correct_ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $incorrect_ip_hash = sha1('different_ip_or_ua' . $this->mock_ua); // Mismatched hash
        $token_value = base64_encode('5000:validhashlongenough');

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $incorrect_ip_hash; // Submit incorrect hash

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on IP/UA mismatch');
        $this->assertStringContainsStringIgnoringCase('mismatch', $result_field->validation_message, 'Validation message should mention mismatch');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on IP/UA failure');
    }

     public function testValidationFailsOnNoInteractionValue() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);

        $_POST['input_' . $this->hidden_field_id] = 'no_interaction'; // Explicit failure value
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail for "no_interaction" value');
        $this->assertStringContainsStringIgnoringCase('bot verification failed', $result_field->validation_message, 'Validation message should indicate bot failure');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on interaction failure');
    }

     public function testValidationFailsOnEmptyTokenValue() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);

        $_POST['input_' . $this->hidden_field_id] = ''; // Empty value
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail for empty token value');
        $this->assertStringContainsStringIgnoringCase('bot verification failed', $result_field->validation_message, 'Validation message should indicate bot failure');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on empty token failure');
    }

    public function testValidationFailsOnInvalidTokenFormat() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $fail_key = 'pch_fail_' . md5($this->mock_ip);

        // --- Test 1: Not base64 ---
        $_POST['input_' . $this->hidden_field_id] = '!!!@@@$$$%%%'; // Invalid base64 chars
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;
        $form1 = $this->create_mock_form();

        // 2. Act
        $result_form_1 = pch_validate_passive_captcha($form1);

        // 3. Assert
        $result_field_1 = $result_form_1['fields'][0];
        $this->assertTrue($result_field_1->failed_validation, 'Validation should fail for invalid base64 format');
        $this->assertStringContainsStringIgnoringCase('invalid captcha token', $result_field_1->validation_message, 'Validation message should indicate invalid token (base64)');
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 after base64 fail');
        // Need to re-set transient for next part of test if it gets deleted on failure
        set_transient('pch_' . $session_token, time(), 600);


        // --- Test 2: Base64 but no colon ---
        $_POST['input_' . $this->hidden_field_id] = base64_encode('juststringnocolon');
        // Nonce, session, iphash remain the same
        $form2 = $this->create_mock_form(); // Get fresh field state

        // 2. Act
        $result_form_2 = pch_validate_passive_captcha($form2);

        // 3. Assert
        $result_field_2 = $result_form_2['fields'][0];
        $this->assertTrue($result_field_2->failed_validation, 'Validation should fail for token missing colon');
        $this->assertStringContainsStringIgnoringCase('invalid captcha token', $result_field_2->validation_message, 'Validation message should indicate invalid token (colon)');
        $this->assertEquals(2, (int) get_transient($fail_key), 'Failure count should be 2 after colon fail');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on token format failure');
    }

    public function testValidationFailsOnTimingFailure() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 1500; // Too fast (< 3000)
        $nav_hash = 'valid_navigator_hash_long_enough';
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on timing < 3000ms');
        $this->assertStringContainsStringIgnoringCase('suspicious timing', $result_field->validation_message, 'Validation message should mention timing');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on timing failure');
    }

     public function testValidationFailsOnFingerprintHashFailure() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // Valid time
        $nav_hash = 'short'; // Too short (strlen < 10)
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on short navigator hash');
        $this->assertStringContainsStringIgnoringCase('fingerprint mismatch', $result_field->validation_message, 'Validation message should mention fingerprint');
        $fail_key = 'pch_fail_' . md5($this->mock_ip);
        $this->assertEquals(1, (int) get_transient($fail_key), 'Failure count should be 1 for the IP');
        $this->assertFalse(get_transient('pch_' . $session_token), 'Session transient should be deleted even on fingerprint failure');
    }

} // End class PassiveCaptchaTest  

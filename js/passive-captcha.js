/**
 * Passive CAPTCHA Hardened - Client-Side Logic (Gravity Forms Version - Configurable)
 * Version: 3.2
 */
document.addEventListener('DOMContentLoaded', function() {

    // --- Configuration ---
    // Field selector targets Gravity Forms hidden field by label
    const captchaFieldSelector = 'input[name^="input_"][name$="captcha_token"]';
    const minTimeMs = 3000; // Base minimum time (server-side check is now configurable)

    // --- Find the target field ---
    // Find the field based on its name pattern (which GF generates based on label)
    // Note: This assumes the label 'CAPTCHA Token' is used as instructed.
    let field = null;
    const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach(input => {
        // Find the associated label (GF often puts it in a preceding sibling div)
        let labelElement = input.previousElementSibling;
        if (labelElement && labelElement.tagName === 'LABEL') {
            // Direct label sibling
        } else if (input.closest('.gfield')) {
            // Find label within the parent .gfield container
            labelElement = input.closest('.gfield').querySelector('.gfield_label');
        }

        if (labelElement && labelElement.textContent.includes('CAPTCHA Token')) {
            field = input;
        }
    });


    // Exit if the field isn't found or if the necessary PHP data isn't available
    // pchData should now include enableWebGL and enableMath flags
    if (!field || typeof pchData === 'undefined') {
        // console.warn('Passive CAPTCHA field (labeled "CAPTCHA Token") or pchData not found.');
        return;
    }

    // --- Initialization ---
    const startTime = Date.now();
    let interacted = false;

    // --- Interaction Detection ---
    ['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt =>
        document.addEventListener(evt, () => interacted = true, { once: true, passive: true })
    );

    // --- Bot Detection Functions ---
    function isHeadless() { /* ... same as generic version ... */
        return navigator.webdriver ||
               /HeadlessChrome/.test(navigator.userAgent) ||
               /slimerjs/i.test(navigator.userAgent) ||
               /phantomjs/i.test(navigator.userAgent) ||
               !('chrome' in window) ||
               ('languages' in navigator && navigator.languages.length === 0);
    }
    function hasMissingNavigatorProps() { /* ... same as generic version ... */
        return !navigator.plugins || navigator.plugins.length === 0 ||
               !navigator.languages || navigator.languages.length === 0;
    }

    // --- Conditionally Enabled Functions ---
    function getWebGLFingerprint() {
        if (!pchData.enableWebGL) { return 'webgl_disabled'; }
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) { return 'no_webgl_support'; }
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            const vendor = debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'unknown_vendor';
            const renderer = debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unknown_renderer';
            return btoa(vendor + '|' + renderer);
        } catch (e) { return 'webgl_error'; }
    }

    function invisibleMathChallenge() {
        if (!pchData.enableMath) { return 'math_disabled'; }
        const a = Math.floor(Math.random() * 10) + 1;
        const b = Math.floor(Math.random() * 10) + 1;
        return (a * b).toString();
    }

    // --- Hash Building ---
    function buildNavigatorHash() {
        const data = [
            navigator.userAgent, navigator.language,
            navigator.languages ? navigator.languages.join(',') : '', navigator.platform,
            getWebGLFingerprint(), invisibleMathChallenge()
        ].join('|');
        return btoa(data);
    }

    // --- Token Generation and Field Update ---
    setTimeout(() => {
        if (!interacted || isHeadless() || hasMissingNavigatorProps()) {
            field.value = 'no_interaction'; return;
        }
        const timeSpent = Date.now() - startTime;
        if (timeSpent < minTimeMs) { field.value = 'no_interaction'; return; }

        const navHash = buildNavigatorHash();
        const token = btoa(timeSpent.toString() + ':' + navHash);
        field.value = token;

        const form = field.closest('form');
        if (form) {
            // Inject other fields needed for server validation
            // Check if fields already exist before adding (less likely needed here)
            if (!form.querySelector('input[name="pch_nonce"]')) {
                 form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_nonce" value="${pchData.nonce}">`);
            }
            if (!form.querySelector('input[name="pch_session"]')) {
                 form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_session" value="${pchData.sessionToken}">`);
            }
             if (!form.querySelector('input[name="pch_iphash"]')) {
                 form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_iphash" value="${pchData.ipHash}">`);
            }
        } else {
            // console.warn('Passive CAPTCHA field is not inside a <form> element.');
        }
    }, minTimeMs);

});
```

---

**3. Updated Test File (Gravity Forms Version)**

This adds tests for the new settings affecting the `pch_validate_passive_captcha` hook.


```php
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
            'pch_min_time_threshold', 'pch_min_hash_length', 'pch_session_lifetime',
            'pch_custom_ip_header', 'pch_enable_webgl', 'pch_enable_math'
        ];
        foreach ($options_to_reset as $option) {
            if (function_exists('delete_site_option')) { delete_site_option($option); }
            else { delete_option($option); }
        }

        // Clear potentially lingering failure transient for mock IP
        delete_transient('pch_fail_' . md5($this->mock_ip));

        // Mock server variables
        $_SERVER['REMOTE_ADDR'] = $this->mock_ip;
        $_SERVER['HTTP_USER_AGENT'] = $this->mock_ua;
        $_SERVER['HTTP_X_JA3_FINGERPRINT'] = $this->mock_ja3;

        // Mock rgpost if Gravity Forms isn't fully loaded in test env
        if (!function_exists('rgpost')) {
            function rgpost($key) { return $_POST[$key] ?? null; }
        }
        // Clear POST data
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
            'id' => 1,
            'fields' => [
                (object)[
                    'id' => $this->hidden_field_id,
                    'type' => 'hidden',
                    'label' => 'CAPTCHA Token', // Label is key for finding the field
                    'failed_validation' => null,
                    'validation_message' => null,
                ]
            ]
        ];
        return $form;
    }

    // --- Helper Function Tests ---
    // (testRateLimitDefaults, testRateLimitIncrements, testIpWhitelistBlacklistFunctions, testWebhookSignatureGeneration - same as generic)
    // ... include these tests ...
    public function testRateLimitDefaults() { $this->assertEquals(5, pch_get_option('pch_rate_limit_threshold', 5)); $this->assertEquals(3600, pch_get_option('pch_ban_duration', 3600)); }
    public function testRateLimitIncrements() { $ip = '192.0.2.1'; delete_transient('pch_fail_' . md5($ip)); $this->assertFalse(pch_check_rate_limit($ip)); pch_update_option('pch_rate_limit_threshold', 3); pch_update_option('pch_ban_duration', 60); pch_register_failure($ip); $this->assertFalse(pch_check_rate_limit($ip)); pch_register_failure($ip); $this->assertFalse(pch_check_rate_limit($ip)); pch_register_failure($ip); $this->assertTrue(pch_check_rate_limit($ip)); delete_transient('pch_fail_' . md5($ip)); }
    public function testIpWhitelistBlacklistFunctions() { $whitelisted_ip = '192.0.2.100'; $blacklisted_ip = '203.0.113.1'; $other_ip = '8.8.8.8'; pch_update_option('pch_ip_whitelist', $whitelisted_ip . "\n198.51.100.1"); pch_update_option('pch_ip_blacklist', $blacklisted_ip . "\n198.51.100.99"); $this->assertTrue(pch_is_ip_whitelisted($whitelisted_ip)); $this->assertFalse(pch_is_ip_whitelisted($other_ip)); $this->assertTrue(pch_is_ip_blacklisted($blacklisted_ip)); $this->assertFalse(pch_is_ip_blacklisted($other_ip)); }
    public function testWebhookSignatureGeneration() { pch_update_option('pch_webhook_hmac_key', 'testsecret'); $payload = ['event' => 'test', 'ip' => '127.0.0.1']; $body = wp_json_encode($payload); $expected_hmac = hash_hmac('sha256', $body, 'testsecret'); $this->assertEquals($expected_hmac, hash_hmac('sha256', $body, pch_get_option('pch_webhook_hmac_key'))); }
    public function testGetVisitorIpPrioritization() { /* ... same as generic test ... */ $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1, 192.168.1.1'; $_SERVER['REMOTE_ADDR'] = '10.0.0.1'; $this->assertEquals('1.1.1.1', pch_get_visitor_ip()); $_SERVER['HTTP_CF_CONNECTING_IP'] = '2.2.2.2'; $this->assertEquals('2.2.2.2', pch_get_visitor_ip()); unset($_SERVER['HTTP_CF_CONNECTING_IP']); $_SERVER['HTTP_X_REAL_IP'] = '3.3.3.3'; $this->assertEquals('3.3.3.3', pch_get_visitor_ip()); unset($_SERVER['HTTP_X_REAL_IP']); $custom_header = 'HTTP_X_MY_CUSTOM_IP'; pch_update_option('pch_custom_ip_header', $custom_header); $_SERVER[$custom_header] = '4.4.4.4'; $this->assertEquals('4.4.4.4', pch_get_visitor_ip()); unset($_SERVER[$custom_header]); pch_update_option('pch_custom_ip_header', ''); unset($_SERVER['HTTP_X_FORWARDED_FOR']); $this->assertEquals('10.0.0.1', pch_get_visitor_ip()); }


    // --- VALIDATION TEST CASES for pch_validate_passive_captcha() ---

    public function testValidationPassesWithValidData() {
        // 1. Arrange
        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 12 * HOUR_IN_SECONDS);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // > default 3000ms
        $nav_hash = 'valid_navigator_data_long_enough'; // > default 10 chars
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

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

    // (Tests for Blacklist, Whitelist, Rate Limit, Nonce, Session, IP/UA Mismatch, No Interaction, Empty Token, Invalid Token Format remain largely the same)
    // ... include those tests here, asserting $result_field->failed_validation and $result_field->validation_message ...
    // Example: Nonce Failure
    public function testValidationFailsOnInvalidNonce() {
        $session_token = 's'; set_transient('pch_'.$session_token, time(), 60);
        $_POST['pch_nonce'] = 'invalid'; $_POST['pch_session'] = $session_token;
        $form = $this->create_mock_gf_form();
        $result_form = pch_validate_passive_captcha($form);
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation);
        $this->assertNotNull($result_field->validation_message);
        $this->assertEquals(1, (int) get_transient('pch_fail_' . md5($this->mock_ip)));
    }
    // ... (Include other similar failure tests) ...


    // --- Updated Tests for Configurable Thresholds ---

    public function testValidationFailsOnTimingFailureWithCustomThreshold() {
        // 1. Arrange
        pch_update_option('pch_min_time_threshold', 5000); // Set custom threshold

        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 4000; // FAILS custom threshold
        $nav_hash = 'valid_navigator_hash_long_enough';
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_gf_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on timing');
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_' . $session_token));
    }

     public function testValidationFailsOnFingerprintHashFailureWithCustomThreshold() {
        // 1. Arrange
        pch_update_option('pch_min_hash_length', 20); // Set custom threshold

        $nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = 'valid_session_' . uniqid();
        set_transient('pch_' . $session_token, time(), 600);
        $ip_hash = sha1($this->mock_ip . $this->mock_ua);
        $time_spent = 5000; // Valid time
        $nav_hash = 'short_hash_15'; // FAILS custom threshold
        $token_value = base64_encode($time_spent . ':' . $nav_hash);

        $_POST['input_' . $this->hidden_field_id] = $token_value;
        $_POST['pch_nonce'] = $nonce;
        $_POST['pch_session'] = $session_token;
        $_POST['pch_iphash'] = $ip_hash;

        $form = $this->create_mock_gf_form();

        // 2. Act
        $result_form = pch_validate_passive_captcha($form);

        // 3. Assert
        $result_field = $result_form['fields'][0];
        $this->assertTrue($result_field->failed_validation, 'Validation should fail on short hash');
        $this->assertNotNull($result_field->validation_message);
        $this->assertFalse(get_transient('pch_' . $session_token));
    }

} // End class PassiveCaptchaTest

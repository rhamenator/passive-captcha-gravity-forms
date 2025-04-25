<?php
/**
 * Plugin Name: Passive CAPTCHA Hardened for Gravity Forms (Multisite Ready)
 * Description: Passive CAPTCHA with timing, nonce, JA3 fingerprinting, webhook escalation, multi-site support, and automated tests.
 * Version: 3.0
 * Author: Rich Hamilton
 * Author URI: https:\\www.github.com\rhamenator
 * Network: true
 */

if (!defined('ABSPATH')) {
    exit;
}

// Multi-site aware get_option wrapper
function pch_get_option($option, $default = '') {
    return is_multisite() ? get_site_option($option, $default) : get_option($option, $default);
}

function pch_update_option($option, $value) {
    return is_multisite() ? update_site_option($option, $value) : update_option($option, $value);
}

// Enqueue JS and session setup
function pch_enqueue_scripts() {
    if (is_page() || is_singular()) {
        $token_nonce = wp_create_nonce('pch_captcha_nonce');
        $session_token = bin2hex(random_bytes(16));
        set_transient('pch_' . $session_token, time(), 10 * MINUTE_IN_SECONDS);
        wp_enqueue_script('passive-captcha-hardened', plugin_dir_url(__FILE__) . 'js/passive-captcha.js', [], null, true);
        wp_localize_script('passive-captcha-hardened', 'pchData', [
            'nonce' => $token_nonce,
            'sessionToken' => $session_token,
            'ipHash' => sha1($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'pch_enqueue_scripts');

// Rate limiting helpers
function pch_check_rate_limit($ip) {
    $limit = pch_get_option('pch_rate_limit_threshold', 5);
    $ban_duration = pch_get_option('pch_ban_duration', 3600);
    $key = 'pch_fail_' . md5($ip);
    $fails = (int) get_transient($key);
    return $fails >= $limit;
}

function pch_register_failure($ip) {
    $key = 'pch_fail_' . md5($ip);
    $fails = (int) get_transient($key);
    set_transient($key, $fails + 1, pch_get_option('pch_ban_duration', 3600));
}

// IP whitelist/blacklist
function pch_is_ip_whitelisted($ip) {
    $list = pch_get_option('pch_ip_whitelist', '');
    $ips = array_map('trim', explode("\n", $list));
    return in_array($ip, $ips);
}

function pch_is_ip_blacklisted($ip) {
    $list = pch_get_option('pch_ip_blacklist', '');
    $ips = array_map('trim', explode("\n", $list));
    return in_array($ip, $ips);
}

// Webhook with HMAC signing
function pch_send_webhook($payload) {
    $url = pch_get_option('pch_webhook_url');
    $key = pch_get_option('pch_webhook_hmac_key');
    if (!$url || !$key) return;
    $body = json_encode($payload);
    $hmac = hash_hmac('sha256', $body, $key);
    wp_remote_post($url, [
        'timeout' => 5,
        'headers' => ['Content-Type' => 'application/json', 'X-Signature' => $hmac],
        'body'    => $body,
    ]);
}

// Validation logic for Gravity Forms
add_filter('gform_pre_validation', 'pch_validate_passive_captcha');
function pch_validate_passive_captcha($form) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; // Use default IP if not set
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';   // Get User Agent

    // --- JA3 Fingerprint Retrieval ---
    // Retrieve JA3 fingerprint header sent by NGINX/webserver
    // Ensure your webserver configuration (like the NGINX+Lua setup discussed)
    // correctly sets this header, e.g., 'HTTP_X_JA3_FINGERPRINT'
    $ja3_header_key = 'HTTP_X_JA3_FINGERPRINT'; // Adjust if your server uses a different key
    $ja3_fingerprint = $_SERVER[$ja3_header_key] ?? ''; // Get header if it exists, otherwise empty string

    foreach ($form['fields'] as &$field) {
        // Find the hidden field labelled 'CAPTCHA Token'
        if ($field->type === 'hidden' && isset($field->label) && strpos($field->label, 'CAPTCHA Token') !== false) {

            // --- IP Blacklist Check ---
            if (pch_is_ip_blacklisted($ip)) {
                $field->failed_validation = true;
                $field->validation_message = __('Your IP is blacklisted.', 'passive-captcha-hardened');
                // Optional: Send webhook here if desired
                continue; // Skip other checks for this field
            }

            // --- IP Whitelist Check ---
            if (pch_is_ip_whitelisted($ip)) {
                // IP is whitelisted, skip all other checks for this field
                continue;
            }

            // --- Conditional JA3 Fingerprint Check (Modified) ---
            // Only perform the check if the JA3 fingerprint header is NOT empty.
            // If the header is empty (e.g., server not configured), skip this specific check.
            if (!empty($ja3_fingerprint)) {
                // The header exists, now check if it's potentially invalid (e.g., too short)
                // You could add more sophisticated checks here later if needed (e.g., known bad fingerprints)
                $min_ja3_len = 10; // Minimum plausible length for a JA3 hash
                if (strlen($ja3_fingerprint) < $min_ja3_len) {
                     $field->failed_validation = true;
                     $field->validation_message = __('Security validation failed (JA3 Format).', 'passive-captcha-hardened'); // Adjusted message
                     pch_register_failure($ip);
                     pch_send_webhook([
                         'event' => 'ja3_invalid_format', // More specific event
                         'ip' => $ip,
                         'user_agent' => $ua,
                         'ja3' => $ja3_fingerprint,
                         'timestamp' => time(),
                         'form_id' => $form['id'] ?? 'N/A', // Include form ID if available
                     ]);
                     continue; // Stop further checks for this field
                }
                // If JA3 header exists and has minimum length, it passes this basic check.
            }
            // --- End Conditional JA3 Check ---


            // --- Rate Limit Check ---
            if (pch_check_rate_limit($ip)) {
                $field->failed_validation = true;
                $field->validation_message = __('Access temporarily blocked.', 'passive-captcha-hardened');
                // Webhook for submissions after ban is handled by pch_after_submission_alert
                continue;
            }

            // --- Retrieve Submitted Values ---
            // Use rgpost() for Gravity Forms context
            $submitted_value = rgpost('input_' . $field->id);
            $submitted_nonce = rgpost('pch_nonce');
            $submitted_session = rgpost('pch_session');
            $submitted_iphash = rgpost('pch_iphash');

            // --- Nonce Verification ---
            if (!wp_verify_nonce($submitted_nonce, 'pch_captcha_nonce')) {
                $field->failed_validation = true;
                $field->validation_message = __('Security check failed (Code: N).', 'passive-captcha-hardened');
                pch_register_failure($ip);
                // Optional: Send webhook for nonce failure
                continue;
            }

            // --- Session Token Verification ---
            $session_transient_key = 'pch_' . $submitted_session;
            if (empty($submitted_session) || !get_transient($session_transient_key)) {
                $field->failed_validation = true;
                $field->validation_message = __('Session expired (Code: S).', 'passive-captcha-hardened');
                if ($submitted_session) delete_transient($session_transient_key); // Clean up if exists
                pch_register_failure($ip);
                // Optional: Send webhook for session failure
                continue;
            }

            // --- IP/User-Agent Hash Verification ---
            $expected_iphash = sha1($ip . $ua);
            if ($submitted_iphash !== $expected_iphash) {
                $field->failed_validation = true;
                $field->validation_message = __('Security check failed (Code: M).', 'passive-captcha-hardened');
                delete_transient($session_transient_key); // Delete transient on failure
                pch_register_failure($ip);
                // Optional: Send webhook for IP/UA mismatch
                continue;
            }

            // --- Interaction / JS Execution Check ---
            if (empty($submitted_value) || $submitted_value === 'no_interaction') {
                $field->failed_validation = true;
                $field->validation_message = __('Bot verification failed (Code: I).', 'passive-captcha-hardened');
                delete_transient($session_transient_key); // Delete transient on failure
                pch_register_failure($ip);
                // Optional: Send webhook for interaction failure
                continue;
            }

            // --- Token Decoding and Basic Format Check ---
            $decoded = base64_decode($submitted_value, true); // Use strict decoding
            if ($decoded === false || strpos($decoded, ':') === false) {
                $field->failed_validation = true;
                $field->validation_message = __('Invalid CAPTCHA token (Code: F).', 'passive-captcha-hardened');
                delete_transient($session_transient_key); // Delete transient on failure
                pch_register_failure($ip);
                // Optional: Send webhook for invalid token format
                continue;
            }

            // --- Token Content Verification (Timing & Fingerprint Hash) ---
            list($timeSpent, $navigatorHash) = explode(':', $decoded, 2); // Limit split
            $min_time = 3000; // Configurable?
            $min_hash_len = 10; // Configurable?
            if (!is_numeric($timeSpent) || $timeSpent < $min_time || strlen($navigatorHash) < $min_hash_len) {
                $field->failed_validation = true;
                $field->validation_message = __('Security check failed (Code: T/H).', 'passive-captcha-hardened');
                delete_transient($session_transient_key); // Delete transient on failure
                pch_register_failure($ip);
                // Optional: Send webhook for timing/fingerprint failure
                continue;
            }

            // --- ALL CHECKS PASSED for this field ---
            delete_transient($session_transient_key); // Delete the used session transient *only* on full success

            // Since checks passed for the CAPTCHA field, we can break the loop
            // if we assume only one such field per form. If multiple are possible, remove break.
            break;
        }
    }
    return $form; // Return the potentially modified form object
}

// Submission escalation hook
add_action('gform_after_submission', 'pch_after_submission_alert', 10, 2);
function pch_after_submission_alert($entry, $form) {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (pch_check_rate_limit($ip)) {
        pch_send_webhook([
            'event' => 'submission_after_ban',
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => time(),
            'form_id' => $form['id'],
        ]);
    }
}

// Admin UI
// In passive-captcha-hardened.php
// Hook into the network admin menu instead of the regular admin menu
add_action('network_admin_menu', 'pch_add_admin_menu');
function pch_add_admin_menu() {
    // Use add_submenu_page under Network Admin -> Settings instead of Options -> page
    // This requires changing the hook to 'network_admin_menu'
     add_submenu_page(
        'settings.php',             // Parent slug (Network Admin -> Settings)
        'Passive CAPTCHA Settings', // Page Title
        'Passive CAPTCHA',          // Menu Title
        'manage_network_options',   // <-- Changed Capability
        'pch-settings',             // Menu Slug
        'pch_settings_page'         // Callback Function
     );
}

function pch_settings_page() {
    if (isset($_POST['pch_settings'])) {
        pch_update_option('pch_rate_limit_threshold', intval($_POST['rate_limit']));
        pch_update_option('pch_ban_duration', intval($_POST['ban_duration']));
        pch_update_option('pch_webhook_url', sanitize_text_field($_POST['webhook_url']));
        pch_update_option('pch_webhook_hmac_key', sanitize_text_field($_POST['hmac_key']));
        pch_update_option('pch_ip_whitelist', sanitize_textarea_field($_POST['ip_whitelist']));
        pch_update_option('pch_ip_blacklist', sanitize_textarea_field($_POST['ip_blacklist']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Passive CAPTCHA Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr><th>Rate Limit Threshold</th><td><input type="number" name="rate_limit" value="<?php echo esc_attr(pch_get_option('pch_rate_limit_threshold', 5)); ?>"></td></tr>
                <tr><th>Ban Duration (seconds)</th><td><input type="number" name="ban_duration" value="<?php echo esc_attr(pch_get_option('pch_ban_duration', 3600)); ?>"></td></tr>
                <tr><th>Webhook URL</th><td><input type="url" name="webhook_url" value="<?php echo esc_attr(pch_get_option('pch_webhook_url')); ?>" size="50"></td></tr>
                <tr><th>Webhook HMAC Key</th><td><input type="text" name="hmac_key" value="<?php echo esc_attr(pch_get_option('pch_webhook_hmac_key')); ?>" size="40"></td></tr>
                <tr><th>IP Whitelist (one per line)</th><td><textarea name="ip_whitelist" rows="5" cols="50"><?php echo esc_textarea(pch_get_option('pch_ip_whitelist')); ?></textarea></td></tr>
                <tr><th>IP Blacklist (one per line)</th><td><textarea name="ip_blacklist" rows="5" cols="50"><?php echo esc_textarea(pch_get_option('pch_ip_blacklist')); ?></textarea></td></tr>
            </table>
            <p class="submit"><input type="submit" name="pch_settings" class="button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php
}

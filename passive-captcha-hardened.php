<?php
/**
 * Plugin Name: Passive CAPTCHA Hardened for Gravity Forms (Multisite Ready)
 * Description: Passive CAPTCHA with timing, nonce, JA3 fingerprinting, webhook escalation, multi-site support, and automated tests.
 * Version: 3.0
 * Author: Your Name
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
    $ip = $_SERVER['REMOTE_ADDR'];
    // Retrieve JA3 fingerprint header sent by NGINX/webserver
    // Ensure your webserver configuration (like the NGINX+Lua setup discussed)
    // correctly sets this header, e.g., 'HTTP_X_JA3_FINGERPRINT'
    $ja3_header_key = 'HTTP_X_JA3_FINGERPRINT'; // Adjust if your NGINX uses a different key
    $ja3_fingerprint = $_SERVER[$ja3_header_key] ?? '';

    foreach ($form['fields'] as &$field) {
        if ($field->type === 'hidden' && strpos($field->label, 'CAPTCHA Token') !== false) {

            // --- IP Blacklist Check ---
            if (pch_is_ip_blacklisted($ip)) {
                $field->failed_validation = true;
                $field->validation_message = 'Your IP is blacklisted.';
                // Note: Consider sending a webhook here too if desired
                continue;
            }

            // --- IP Whitelist Check ---
            if (pch_is_ip_whitelisted($ip)) {
                // IP is whitelisted, skip all other checks for this field
                continue;
            }

            // --- JA3 Fingerprint Check (New) ---
            // Check if the JA3 fingerprint header is missing or suspiciously short.
            // You might enhance this check based on known bad fingerprints if needed.
            if (empty($ja3_fingerprint) || strlen($ja3_fingerprint) < 10) { // Check length as per chat example [cite: 145]
                 $field->failed_validation = true;
                 $field->validation_message = 'Security validation failed (JA3).'; // User-friendly message
                 pch_register_failure($ip);
                 pch_send_webhook([ // Send webhook as per chat example [cite: 146]
                     'event' => 'ja3_missing_or_invalid',
                     'ip' => $ip,
                     'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                     'ja3' => $ja3_fingerprint, // Include the received (or empty) JA3
                     'timestamp' => time(),
                     'form_id' => $form['id'], // Add form ID for context
                 ]);
                 continue; // Stop further checks for this field
            }


            // --- Rate Limit Check ---
            if (pch_check_rate_limit($ip)) {
                $field->failed_validation = true;
                $field->validation_message = 'Access temporarily blocked.';
                // Webhook for submissions after ban is handled by pch_after_submission_alert
                continue;
            }

            // --- Retrieve Submitted Values ---
            $submitted_value = rgpost('input_' . $field->id);
            $submitted_nonce = rgpost('pch_nonce');
            $submitted_session = rgpost('pch_session');
            $submitted_iphash = rgpost('pch_iphash');

            // --- Nonce Verification ---
            if (!wp_verify_nonce($submitted_nonce, 'pch_captcha_nonce')) {
                $field->failed_validation = true;
                $field->validation_message = 'Security check failed (nonce).';
                pch_register_failure($ip);
                // Consider sending webhook for nonce failure too
                continue;
            }

            // --- Session Token Verification ---
            if (!$submitted_session || !get_transient('pch_' . $submitted_session)) {
                $field->failed_validation = true;
                $field->validation_message = 'Session expired.';
                // Ensure transient is deleted even if it exists but other checks fail later
                if ($submitted_session) {
                    delete_transient('pch_' . $submitted_session);
                }
                pch_register_failure($ip);
                 // Consider sending webhook for session failure
                continue;
            }
            // Delete the transient *after* all checks pass for this attempt,
            // or definitely delete if validation fails here.
            delete_transient('pch_' . $submitted_session);


            // --- IP/User-Agent Hash Verification ---
            if ($submitted_iphash !== sha1($ip . ($_SERVER['HTTP_USER_AGENT'] ?? ''))) {
                $field->failed_validation = true;
                $field->validation_message = 'IP/User-Agent mismatch.';
                pch_register_failure($ip);
                // Consider sending webhook for IP/UA mismatch
                continue;
            }

            // --- Interaction / JS Execution Check ---
            if (empty($submitted_value) || $submitted_value === 'no_interaction') {
                $field->failed_validation = true;
                $field->validation_message = 'Bot verification failed.';
                pch_register_failure($ip);
                // Consider sending webhook for interaction failure
                continue;
            }

            // --- Token Decoding and Basic Format Check ---
            $decoded = base64_decode($submitted_value);
            if (!$decoded || strpos($decoded, ':') === false) {
                $field->failed_validation = true;
                $field->validation_message = 'Invalid CAPTCHA token.';
                pch_register_failure($ip);
                 // Consider sending webhook for invalid token format
                continue;
            }

            // --- Token Content Verification (Timing & Fingerprint Hash) ---
            list($timeSpent, $navigatorHash) = explode(':', $decoded, 2); // Limit split to 2 parts
            if (!is_numeric($timeSpent) || $timeSpent < 3000 || strlen($navigatorHash) < 10) { // Ensure timeSpent is numeric
                $field->failed_validation = true;
                $field->validation_message = 'Suspicious timing or fingerprint mismatch.';
                pch_register_failure($ip);
                 // Consider sending webhook for timing/fingerprint failure
                continue;
            }

            // If we reach here, all checks passed for this field
        }
    }
    return $form;
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

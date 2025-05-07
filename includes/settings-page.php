<?php
if (!defined('ABSPATH')) exit;

// Ensure user has capability
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Optional logging warning
$log_file = PASSIVE_CAPTCHA_LOG_FILE;
$log_writable = is_writable(dirname($log_file));
$footer_hooked = did_action('wp_footer');

if (!$log_writable || !$footer_hooked): ?>
    <div class="notice notice-warning is-dismissible">
        <p><strong>Passive CAPTCHA Warning:</strong>
            <?php if (!$log_writable): ?>
                Cannot write to the log file at <code><?php echo esc_html($log_file); ?></code>. Check file permissions or plugin settings.
            <?php endif; ?>
            <?php if (!$footer_hooked): ?>
                The <code>wp_footer()</code> hook was not detected in your theme. JavaScript may not execute properly.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<div class="wrap">
    <h1>Passive CAPTCHA Plugin Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields('passive_captcha_settings'); ?>
        <?php do_settings_sections('passive-captcha'); ?>

        <h2>1. Instructions</h2>
        <p><strong>Ensure this plugin is activated</strong> (use Network Activate for multisite).</p>
        <ol>
            <li>Edit your Gravity Form and add a <strong>Hidden Field</strong> (from Standard Fields).</li>
            <li>Set its <strong>Field Label</strong> to exactly <code>CAPTCHA Token</code>.</li>
            <li>Adjust the configuration options below as needed.</li>
        </ol>

        <hr>

        <h2>2. Behavior Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rate_limit_threshold">Rate Limit Threshold</label></th>
                <td><input type="number" id="rate_limit_threshold" name="passive_captcha_settings[rate_limit_threshold]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['rate_limit_threshold'] ?? 5); ?>" class="small-text"> <span class="description">Failed attempts from an IP before banning (0 = disable).</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="ban_duration">Ban Duration (seconds)</label></th>
                <td>input type="number" id="ban_duration" name="passive_captcha_settings[ban_duration]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['ban_duration'] ?? 3600); ?>" class="small-text"> <span class="description">How long banned IPs are blocked (e.g., 3600 = 1 hour).</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="session_token_lifetime">Session Token Lifetime (seconds)</label></th>
                <td><input type="number" id="session_token_lifetime" name="passive_captcha_settings[session_token_lifetime]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['session_token_lifetime'] ?? 43200); ?>" class="small-text"> <span class="description">How long a token is valid (default: 43200 = 12 hours).</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="min_time_threshold">Minimum Time Threshold (ms)</label></th>
                <td><input type="number" id="min_time_threshold" name="passive_captcha_settings[min_time_threshold]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['min_time_threshold'] ?? 3000); ?>" class="small-text"> <span class="description">Minimum milliseconds user must remain on page before submission.</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="min_hash_length">Minimum Fingerprint Hash Length</label></th>
                <td><input type="number" id="min_hash_length" name="passive_captcha_settings[min_hash_length]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['min_hash_length'] ?? 10); ?>" class="small-text"> <span class="description">Expected minimum length of client-side fingerprint hash.</span></td>
            </tr>
            <tr>
                <th scope="row">Client-Side Checks</th>
                <td>
                    <label><input type="checkbox" name="passive_captcha_settings[use_webgl]" value="1" <?php checked(1, get_option('passive_captcha_settings')['use_webgl'] ?? 1); ?>> Include WebGL Fingerprint</label><br>
                    <label><input type="checkbox" name="passive_captcha_settings[use_math_challenge]" value="1" <?php checked(1, get_option('passive_captcha_settings')['use_math_challenge'] ?? 1); ?>> Include Invisible Math Challenge</label>
                </td>
            </tr>
        </table>

        <hr>

        <h2>3. Webhook Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="webhook_url">Webhook URL</label></th>
                <td><input type="url" id="webhook_url" name="passive_captcha_settings[webhook_url]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['webhook_url'] ?? ''); ?>" class="regular-text"> <span class="description">POST JSON to this URL. Leave blank to disable.</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="webhook_key">Webhook HMAC Key</label></th>
                <td><input type="text" id="webhook_key" name="passive_captcha_settings[webhook_key]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['webhook_key'] ?? ''); ?>" class="regular-text"> <span class="description">Used for SHA256 signature of payload.</span></td>
            </tr>
        </table>

        <hr>

        <h2>4. IP Address Management</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="ip_header">Custom IP Header (Advanced)</label></th>
                <td><input type="text" id="ip_header" name="passive_captcha_settings[ip_header]" value="<?php echo esc_attr(get_option('passive_captcha_settings')['ip_header'] ?? ''); ?>" class="regular-text"> <span class="description">Optional header (e.g., HTTP_CF_CONNECTING_IP) for visitor IP.</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="ip_whitelist">IP Whitelist</label></th>
                <td><textarea id="ip_whitelist" name="passive_captcha_settings[ip_whitelist]" rows="5" cols="60"><?php echo esc_textarea(get_option('passive_captcha_settings')['ip_whitelist'] ?? ''); ?></textarea><br><span class="description">One IP per line. Always allowed.</span></td>
            </tr>
            <tr>
                <th scope="row"><label for="ip_blacklist">IP Blacklist</label></th>
                <td><textarea id="ip_blacklist" name="passive_captcha_settings[ip_blacklist]" rows="5" cols="60"><?php echo esc_textarea(get_option('passive_captcha_settings')['ip_blacklist'] ?? ''); ?></textarea><br><span class="description">One IP per line. Always blocked.</span></td>
            </tr>
        </table>

        <p><input type="submit" value="Save All Settings" class="button-primary"></p>
    </form>

    <hr>

    <h2>5. Rate Limit Management</h2>
    <form method="post">
        <?php submit_button('Clear All Rate Limit Bans', 'secondary', 'clear_rate_limits', false); ?>
    </form>
    <p><em>Consider adding UI to manage individual bans, depending on plugin performance.</em></p>

    <hr>

    <h2>6. Recent Log Entries</h2>
    <form method="post">
        <?php submit_button('Clear Recent Log Entries', 'secondary', 'clear_logs', false); ?>
    </form>
    <div style="max-height: 300px; overflow-y: scroll; background: #f9f9f9; border: 1px solid #ccc; padding: 10px; font-family: monospace;">
        <?php
        if (file_exists($log_file)) {
            $lines = array_slice(file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50);
            foreach ($lines as $line) {
                echo esc_html($line) . "<br>";
            }
        } else {
            echo "Log file not found or empty.";
        }
        ?>
    </div>
</div>

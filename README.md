# Passive CAPTCHA Hardened (Generic Form Support - Managed Hosting Ready)

**Version:** 4.1
**Author:** Rich Hamilton
**Requires at least:** 5.0 (Assumed based on modern WP functions used)
**Tested up to:** 6.8
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Advanced passive, non-interactive CAPTCHA for *any* WordPress form. Blocks bots without user interaction using multiple layers of checks, including improved support for managed hosting environments like Pressable. Requires manual integration into your form handling code.

## Features

* **Passive Validation:** No visible challenge for users.
* **Client-Side Checks:** Analyzes timing, user interaction (mouse, keyboard, scroll), headless browser signatures, navigator properties, and WebGL fingerprinting.
* **Server-Side Checks:** Verifies WordPress nonces, session tokens (with longer lifespan for better cache compatibility), and IP/User-Agent consistency via a callable PHP function (`pch_verify_submission`).
* **Robust IP Detection:** Attempts to identify the real visitor IP behind reverse proxies and CDNs (checks `HTTP_CF_CONNECTING_IP`, `HTTP_X_REAL_IP`, `HTTP_X_FORWARDED_FOR` before `REMOTE_ADDR`).
* **Conditional JA3 Check:** Validates TLS fingerprint via `HTTP_X_JA3_FINGERPRINT` header *only if* provided by the webserver (requires compatible server setup, skipped otherwise).
* **Rate Limiting:** Temporarily blocks IPs after configurable repeated validation failures using WordPress transients.
* **IP Whitelisting/Blacklisting:** Allows specific IPs to bypass checks or be blocked outright via plugin settings.
* **Webhook Notifications:** Sends alerts (with optional HMAC signature) to a specified URL upon validation failures.
* **Multisite Compatible:** Settings are managed network-wide.
* **Improved Logging:** Logs specific failure reasons to the PHP error log for easier debugging.
* **User-Friendly Errors:** Returns `WP_Error` objects with generic user messages upon failure.

## Installation

1.  **Download:** Obtain the plugin zip file or directory (`passive-captcha-hardened`).
2.  **Upload:** Via WordPress Admin (Plugins -> Add New -> Upload) or SFTP/FTP (`/wp-content/plugins/`).
3.  **Activate:**
    * **Single Site:** Activate via Plugins -> Installed Plugins.
    * **Multisite:** Network Activate via Network Admin -> Plugins.

## Configuration & Usage (Manual Integration Required)

1.  **Add Hidden Field to Your Form:**
    * In the HTML source of any form you want to protect, add:
        ```html
        <input type="hidden" name="pch_captcha_token" value="">
        ```
    * The plugin's JS finds this field by its `name` attribute.

2.  **Call Verification Function in PHP:**
    * In your PHP code that handles the form submission (theme `functions.php`, another plugin, etc.), call `pch_verify_submission()` **before** processing form data.
    * Check the return value: `true` means PASS, `WP_Error` means FAIL.
    * **Example PHP Handler:**
        ```php
        if (function_exists('pch_verify_submission')) {
            $captcha_result = pch_verify_submission();
            if (is_wp_error($captcha_result)) {
                // FAIL: Stop processing, show error message
                wp_die('CAPTCHA check failed: ' . esc_html($captcha_result->get_error_message()));
                return;
            }
            // PASS: Continue processing form...
        } else {
            // Plugin inactive? Handle error.
            wp_die('Security component inactive.');
            return;
        }
        // ... process rest of form ...
        ```

3.  **Configure Plugin Settings:**
    * Navigate to **Settings -> Passive CAPTCHA** (found under Network Admin -> Settings on multisite).
    * Adjust Rate Limit, Ban Duration, Webhook URL/Key, and IP Whitelist/Blacklist settings.
    * Save Changes.

## Optional Advanced Setup

### JA3 TLS Fingerprinting

* Requires server-level configuration (e.g., NGINX+Lua) to capture the JA3 fingerprint and pass it via the `X-JA3-FINGERPRINT` HTTP header.
* The plugin will automatically use this header for validation *if it is present*. If not present (like on standard managed hosting), the check is skipped.

## For Developers: Running Tests

* Includes PHPUnit tests (`tests/` directory).
* Requires a WordPress test environment setup (manual or Docker). See `phpunit.xml` and `tests/bootstrap.php`. Docker setup files (`Dockerfile`, `docker-compose.yml`, `Makefile`) are included for containerized testing.

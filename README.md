# Passive CAPTCHA Hardened for Gravity Forms (Managed Hosting Ready)

**Version:** 3.2
**Author:** Your Name
**Requires at least:** 5.0 (Assumed based on modern WP functions used)
**Tested up to:** (Specify latest tested WP version)
**License:** GPLv2 or later
**License URI:** <https://www.gnu.org/licenses/gpl-2.0.html>

Configurable, passive, non-interactive CAPTCHA protection designed specifically for Gravity Forms. Blocks bot submissions without user interaction using multiple layers of checks, including improved support for managed hosting environments.

## Features

* **Passive Validation:** No visible challenge for users.
* **Client-Side Checks:** Analyzes timing, user interaction, headless browser signatures, navigator properties. Optionally includes WebGL fingerprinting and an invisible math challenge (configurable).
* **Server-Side Checks:** Verifies WordPress nonces, session tokens (configurable lifetime), and IP/User-Agent consistency. Uses configurable thresholds for timing and fingerprint hash length.
* **Robust IP Detection:** Attempts to identify the real visitor IP behind reverse proxies/CDNs (checks standard headers + optional custom header).
* **Conditional JA3 Check:** Validates TLS fingerprint via `HTTP_X_JA3_FINGERPRINT` header *only if* provided by the webserver (skipped otherwise).
* **Rate Limiting:** Temporarily blocks IPs after configurable repeated validation failures.
* **IP Whitelisting/Blacklisting:** Allows specific IPs to bypass checks or be blocked outright.
* **Webhook Notifications:** Sends alerts (with optional HMAC signature) on validation failures or submissions from banned IPs.
* **Multisite Compatible:** Settings are managed network-wide.
* **Improved Logging:** Logs specific failure reasons to the PHP error log.
* **User-Friendly Errors:** Displays generic error messages to users upon failure.

## Installation

1. **Download:** Obtain the plugin zip file or directory (`passive-captcha-hardened`).
2. **Upload:** Via WordPress Admin (Plugins -> Add New -> Upload) or SFTP/FTP (`/wp-content/plugins/`).
3. **Activate:**
    * **Single Site:** Activate via Plugins -> Installed Plugins.
    * **Multisite:** Network Activate via Network Admin -> Plugins.

## Configuration

1. **Add Hidden Field to Gravity Forms:**
    * Edit the target Gravity Form.
    * Add a **Hidden** field (Standard Fields).
    * Set its **Field Label** to exactly: `CAPTCHA Token`.
    * Save the form.

2. **Configure Plugin Settings:**
    * Navigate to **Settings -> Passive CAPTCHA** (found under Network Admin -> Settings on multisite).
    * Adjust the following settings:
        * **Behavior:** Rate Limit Threshold, Ban Duration, Session Token Lifetime.
        * **Validation Thresholds:** Minimum Time (ms), Minimum Fingerprint Hash Length.
        * **Client-Side Checks:** Enable/disable WebGL Fingerprint and Invisible Math Challenge inclusion in the client hash.
        * **Webhook:** URL and HMAC Key for failure notifications.
        * **IP Management:** Optional Custom IP Header (for proxy environments), IP Whitelist, IP Blacklist.
    * Save Changes.

## Usage

Once installed, activated, configured, and the hidden field is added, protection is automatic for the selected Gravity Form(s).

## Optional Advanced Setup

### JA3 TLS Fingerprinting

* Requires server-level configuration (e.g., NGINX+Lua) to capture the JA3 fingerprint and pass it via the `X-JA3_FINGERPRINT` HTTP header.
* The plugin will automatically use this header for validation *if it is present*.

## For Developers: Running Tests

* Includes PHPUnit tests (`tests/` directory).
* Requires a WordPress test environment setup (manual or Docker). See `phpunit.xml` and `tests/bootstrap.php`. Docker setup files (`Dockerfile`, `docker-compose.yml`, `Makefile`) are included for containerized testing.

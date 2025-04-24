# Passive CAPTCHA Hardened for Gravity Forms (Multisite Ready)

**Version:** 3.0
**Author:** Your Name
**Requires at least:** 5.0 (Assumed based on modern WP functions used)
**Tested up to:** (Specify latest tested WP version)
**License:** GPLv2 or later
**License URI:** <https://www.gnu.org/licenses/gpl-2.0.html>

Passive, non-interactive CAPTCHA protection designed specifically for Gravity Forms. Includes advanced bot detection, rate limiting, webhook escalation, multi-site support, and automated tests.

## Features

This plugin protects your Gravity Forms submissions using a multi-layered approach with no visible challenge to the user:

* **Timing Analysis:** Checks time spent on the page before submission.
* **JavaScript Execution:** Verifies the client can execute JavaScript to generate a token.
* **Interaction Detection:** Checks for basic user interactions like mouse movement or key presses.
* **Headless Browser Detection:** Identifies common headless browser signatures (`navigator.webdriver`, etc.).
* **Navigator Property Analysis:** Checks for inconsistencies in browser properties often found in bots.
* **WebGL Fingerprinting:** Generates a hash based on the browser's WebGL rendering capabilities.
* **Nonce Validation:** Prevents replay attacks using WordPress nonces tied to the user session.
* **Session Tying:** Uses temporary server-side transients to validate session lifetime.
* **IP Address + User Agent Binding:** Ensures the token is submitted from the same IP/UA combination that generated it.
* **JA3 TLS Fingerprinting Integration:** Validates the TLS handshake signature passed via a webserver header (requires server setup).
* **Rate Limiting & Auto-Ban:** Temporarily blocks IPs after repeated validation failures.
* **IP Whitelisting/Blacklisting:** Allows specific IPs to bypass checks or be blocked outright.
* **Webhook Escalation:** Sends notifications (with HMAC signature) to a specified URL upon validation failures or banned submissions.
* **Multisite Compatible:** Settings are managed network-wide.

## Installation

1. **Download:** Obtain the plugin zip file or directory (`passive-captcha-hardened`).
2. **Upload:**
    * Go to your WordPress Admin Dashboard -> Plugins -> Add New -> Upload Plugin. Choose the zip file.
    * OR, upload the `passive-captcha-hardened` directory to your `/wp-content/plugins/` directory via SFTP/FTP.
3. **Activate:**
    * **Single Site:** Go to Plugins -> Installed Plugins and click "Activate" for "Passive CAPTCHA Hardened...".
    * **Multisite Network:** Go to Network Admin -> Plugins and click "Network Activate" for "Passive CAPTCHA Hardened...".

## Configuration

1. **Add Hidden Field to Gravity Forms:**
    * Edit the Gravity Form(s) you want to protect.
    * Add a **Hidden Field** from the Standard Fields panel.
    * Set the **Field Label** to exactly: `CAPTCHA Token`. The plugin specifically looks for this label to identify the target field[cite: 30, 91].
    * Save your form.
    *(The plugin's JavaScript will automatically find this field and populate its value)*.

2. **Configure Plugin Settings:**
    * Go to your **Network Admin** dashboard (if multisite) or regular Admin dashboard (if single site).
    * Navigate to **Settings -> Passive CAPTCHA**. [Note: On Multisite, this appears under the Network Admin's Settings menu](cite: 196, 264).
    * Adjust the following settings as needed:
        * **Rate Limit Threshold:** Number of failures before an IP is temporarily banned (Default: 5).
        * **Ban Duration (seconds):** How long an IP remains banned (Default: 3600 = 1 hour).
        * **Webhook URL:** The URL to send failure notifications to (optional).
        * **Webhook HMAC Key:** A secret key used to sign webhook payloads for verification (required if Webhook URL is set).
        * **IP Whitelist:** IPs (one per line) that bypass checks.
        * **IP Blacklist:** IPs (one per line) that are always blocked.
    * Click "Save Changes".

## Usage

Once installed, activated, configured (Settings page), and the hidden field is added to your Gravity Form(s), the CAPTCHA protection works automatically in the background. No further steps are needed for basic operation with Gravity Forms.

## Optional Advanced Setup

### JA3 TLS Fingerprinting

* This feature requires server-level configuration.
* You need to set up your webserver (e.g., NGINX with the `ngx_http_lua_module` and `lua-resty-ja3`) to capture the client's JA3 fingerprint during the TLS handshake[cite: 144].
* The webserver must pass this fingerprint to PHP via an HTTP header. The plugin defaults to checking `$_SERVER['HTTP_X_JA3_FINGERPRINT']`[cite: 145]. If this header is missing or invalid (and the IP isn't whitelisted), validation will fail[cite: 146].

## For Developers: Running Tests

This plugin includes automated PHPUnit tests to verify its server-side functionality.

### Manual Test Environment Setup

* Requires PHPUnit, a dedicated test database (MySQL/MariaDB), and a copy of the WordPress development files (`wordpress-tests-lib`).
* Follow the detailed setup instructions provided in the development chat log.
* Once set up, navigate to the plugin directory (`passive-captcha-hardened`) in your terminal and run the `phpunit` command.

### Docker-Based Test Environment Setup

This method uses Docker and Docker Compose to run tests in an isolated environment without needing local PHP/MySQL setup. Assumes Docker and Docker Compose are installed. The necessary `Dockerfile`, `docker-compose.yml`, and `Makefile` should be included in the plugin's root directory[cite: 246, 252].

1. **Build Containers:**

    ```bash
    docker-compose build
    ```

    *(Alternatively, run: `make build`)*

2. **Start Services:** Bring up the WordPress and Database containers in the background.

    ```bash
    docker-compose up -d wordpress db
    ```

    *(Alternatively, run: `make up`)*

3. **Install WP Test Library & Configure:** Run this command once to download the WordPress test suite files into the container and configure the database connection.

    ```bash
    docker-compose run --rm phpunit bash -c "\
        if [ ! -d /tmp/wordpress ]; then \
            git clone [https://github.com/WordPress/wordpress-develop.git](https://github.com/WordPress/wordpress-develop.git) /tmp/wordpress; \
            cd /tmp/wordpress; \
            npm install; \
            npm run build; \
            cp wp-tests-config-sample.php wp-tests-config.php; \
            sed -i \"s/youremptytestdbnamehere/wordpress_test/\" wp-tests-config.php; \
            sed -i \"s/yourusernamehere/wp_test/\" wp-tests-config.php; \
            sed -i \"s/yourpasswordhere/password/\" wp-tests-config.php; \
            sed -i \"s/localhost/db/\" wp-tests-config.php; \
        fi"
    ```

    *(Alternatively, run: `make install-tests`)*

4. **Reset DB & Activate Plugin (Optional but recommended before testing):** Prepare the test database and ensure the plugin is active within the test WordPress instance.

    ```bash
    docker-compose run --rm phpunit bash -c "\
        wp db reset --yes && \
        wp core install --url='http://localhost:8080' --title='Passive CAPTCHA Test' --admin_user='admin' --admin_password='password' --admin_email='admin@example.com' && \
        wp plugin activate passive-captcha-hardened"
    ```

    *(Alternatively, run: `make db-reset activate-plugin`)*

5. **Run Tests:** Execute the PHPUnit tests inside the `phpunit` container.

    ```bash
    docker-compose run --rm phpunit phpunit
    ```

    *(Alternatively, run: `make test`)*

6. **Combined Reset & Test (Recommended Workflow):** To ensure a clean environment for each test run:

    ```bash
    make test-reset
    ```

    *(This runs `down`, `up`, `install-tests`, `db-reset`, `activate-plugin`, and `test`)*

7. **Clean Up:** Stop and remove the containers when finished.

    ```bash
    docker-compose down
    ```

    *(Alternatively, run: `make down`)*

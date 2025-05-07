# **Deployment Readiness Checklist:** #

* **PHP and JavaScript Integration**

  * Confirm that `passive-captcha.php` fully registers and enqueues all JS scripts.

  * Verify the database options and logging functionality are properly initialized.

* **JavaScript Modules**

  * Ensure all ES modules (`index.js`, `fingerprint.js`, `mathChallenge.js`, etc.) are loading and communicating correctly.

  * Test that the AJAX endpoints respond as expected and correctly log events.

* **Docker and Infrastructure**

  * Confirm that `docker-compose.yml` correctly orchestrates services (NGINX, PHP-FPM, MySQL).

  * Verify your local Docker environment matches your production/test environment (PHP version, database settings).

* **NGINX Configuration**

  * Confirm `nginx.conf` includes all security headers and proxy settings necessary for JA3 header handling.

  * Ensure instructions for deployment and NGINX configuration are clear.

* **Makefile and Testing Automation**

  * Run `make test` locally to validate that all tests pass.

  * Execute the workflow in GitHub Actions manually to ensure the configuration is correct and tests/linting/build pass.

* **GitHub Actions Workflows**

  * Confirm that `lint.yml`, `test.yml`, and `build.yml` are correctly set up for manual dispatch.

  * Test manual invocation from GitHub to ensure the workflow triggers correctly.

* **Documentation**

  * Verify that all Markdown files (`README.md`, `architecture.md`, `flowchart.md`, `sequence.md`) accurately reflect current functionality.

  * Ensure diagrams in Markdown files render correctly and clearly represent logic and structure.

* **Final Sanity Check**

  * Deploy to a staging/test environment and perform comprehensive manual and automated testing of the entire passive CAPTCHA flow.

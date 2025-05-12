# **Passive CAPTCHA for Gravity Forms**

A lightweight, passive CAPTCHA solution for Gravity Forms that transparently protects your forms against bots by analyzing user interactions and environmental characteristics.

## **Features**

* **Passive Bot Detection** – No visible challenges; uses timing, interaction, WebGL fingerprinting, and invisible math to distinguish humans from bots.

* **JA3 Integration** – Optionally leverage server‑side JA3 TLS fingerprints via nginx or other proxies.

* **Flexible Thresholds** – Configure rate limits, ban durations, session lifetimes, and minimum interaction times.

* **Customizable Checks** – Enable or disable WebGL fingerprinting and invisible math challenges for privacy compliance.

* **Webhook Alerts** – Receive JSON‑formatted POST notifications on validation failures or post‑ban submissions.

* **IP Management** – Whitelist and blacklist specific IPs; specify custom IP headers (e.g., `HTTP_CF_CONNECTING_IP`).

* **Detailed Logging** – Records all validation events to a dedicated log file with fallback to DB logging if file access is restricted.

* **Settings UI** – Full-featured admin page under **Settings → Passive CAPTCHA**.

* **Easy Integration** – Automatically enqueues ES‑module bundles (index.js \+ feature modules) and injects necessary hidden fields.

* **Graceful Fallbacks** – Warns admins if `footer.php` is missing or scripts fail to load; attempts alternative injection points.

## **Installation**

1. Upload the `passive-captcha-gravity-forms` folder to your `/wp-content/plugins/` directory.

2. Activate **Passive CAPTCHA** from the **Plugins** menu in WordPress.

3. Under **Settings → Passive CAPTCHA**, configure your preferred options and save.

4. Edit your Gravity Form, add a **Hidden** field, and set the **Field Label** to `CAPTCHA Token`.

**Note:** The plugin attempts to inject its script after the theme’s `wp_footer()`; ensure your theme includes `footer.php` and calls `wp_footer()`.

## **Folder Structure**

passive-captcha-gravity-forms/  
├── js/  
│   ├── index.js  
│   ├── debug-logger.js  
│   ├── fingerprint.js  
│   ├── ja3Integration.js  
│   ├── logger.js  
│   ├── mathChallenge.js  
│   ├── session.js  
│   └── token-handler.js  
├── includes/  
│   └── settings-page.php  
├── dev-only/  
│   ├── Dockerfile.node  
│   ├── Dockerfile.phpunit  
│   ├── docker-compose.yml  
│   ├── lint.yml  
│   ├── test.yml  
│   └── nginx.conf  
├── tests/  
│   └── tests-passive-captcha.php  
├── architecture.md  
├── flowchart.md  
├── sequence.md  
├── Dockerfile (in recycle bin)  
├── Makefile  
├── phpunit.xml  
├── phpcs.xml.dist  
├── README.md  
├── readme.txt  
├── sequence.md  
└── passive-captcha.php

## **Usage**

1. Fill out the settings to control rate limits, time thresholds, and feature toggles.

2. Place a hidden field labeled `CAPTCHA Token` in any Gravity Form to automatically enable passive CAPTCHA on that form.

3. Monitor the **Recent Log Entries** on the settings page or check `wp-content/uploads/pch-logs.log` for detailed debug output.

## **Development & Testing**

* **Build**: `make build`

* **Lint**: `make lint`

* **Test**: `make test`

* **Docker Compose** (dev): `docker-compose -f dev-only/docker-compose.yml up`

Refer to `dev-only/` for CI workflows, Docker configurations, and Nginx hardening guidelines.

## **License**

GPL v2 or later. See `LICENSE` for details.


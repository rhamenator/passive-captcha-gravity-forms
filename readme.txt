=== Passive CAPTCHA for Gravity Forms ===
Contributors: yourusername
Tags: captcha, gravity forms, anti-spam, passive captcha, bot detection
Requires at least: 5.8
Tested up to: 6.8.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Adds an invisible passive CAPTCHA system to Gravity Forms that detects bots using subtle JavaScript fingerprinting, hidden fields, and session entropy — all without disrupting the user experience.

Designed to detect headless browsers, scraping tools, and scripted form submissions with minimal friction for legitimate users.

== Installation ==

1. Upload the `passive-captcha-gravity-forms` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Edit your Gravity Form and add a hidden field:
   - Label: `CAPTCHA Token`
   - Parameter Name: `captcha_token`

The plugin will automatically find this field and inject a token into it on page load.

== Frequently Asked Questions ==

= Do I need to configure anything? =
No, the plugin works automatically. However, if you want to enable debug mode or customize fingerprinting behavior, you can edit the plugin source.

= What happens if my theme doesn't have footer.php? =
The plugin will attempt to fall back to other injection points (like the header) and will log a warning in the admin area if it cannot reliably inject JavaScript.

= Is this plugin GDPR-compliant? =
No personal data is collected. If you log IP hashes or other data, ensure your privacy policy reflects this.

== Changelog ==

= 1.0.0 =
* Initial release.
* JavaScript fingerprinting, fallback logic, logging, and admin warning features added.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install to silently detect suspicious form submissions.

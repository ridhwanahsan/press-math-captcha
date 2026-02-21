=== Press Math Captcha ===
Contributors: ridhwanahsann
Tags: captcha, math, login, contact form 7, woocommerce
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight math CAPTCHA for WordPress login, Contact Form 7, and WooCommerce forms.

== Description ==

Press Math Captcha adds a GDPR-friendly, math-based CAPTCHA to protect your forms from spam and brute force. No external services, no tracking, and fully WordPress.org compliant.

Features:

* WordPress login protection (captcha on wp-login.php)
* Contact Form 7 integration via [pmcMathcaptcha] / [pmcMathcaptcha*]
* WooCommerce login and registration protection
* Configurable difficulty and operations (Addition/Subtraction/Multiplication/Random)
* Hide captcha for logged-in users
* Custom error message support
* Optional rate limiting and honeypot
* Failed login log + logged user list (admin pages)
* Translation-ready

== Installation ==

1. Upload the `press-math-captcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings → Press Math Captcha to configure.

== Frequently Asked Questions ==

= How do I add it to Contact Form 7? =
Use the form tag `[pmcMathcaptcha]` or `[pmcMathcaptcha*]` in your CF7 form.

= Does it work for logged-in users? =
You can hide CAPTCHA for logged-in users via the settings page.

= Does it keep logs? =
Yes. It provides separate admin pages for Logged User List and Failed Login Log with a clear button for each list.

== Screenshots ==

1. Settings page.
2. Logged User List.
3. Failed Login Log.

== Changelog ==

= 1.0.0 =
* Initial release.

# Press Math Captcha

Lightweight, GDPR‑friendly math CAPTCHA for WordPress login, Contact Form 7, and WooCommerce forms. No external services, no tracking, and WordPress.org compliant.

## ✨ Features

- ✅ WordPress login protection (captcha on `wp-login.php`)
- ✅ Contact Form 7 integration via `[pmcMathcaptcha]` / `[pmcMathcaptcha*]`
- ✅ WooCommerce login + registration protection
- ✅ Configurable difficulty and operations (Addition/Subtraction/Multiplication/Random)
- ✅ Hide captcha for logged-in users
- ✅ Custom error message support
- ✅ Optional rate limiting + honeypot
- ✅ Failed login log + logged user list (admin pages)
- ✅ Translation ready

## 📦 Installation

1. Upload the `press-math-captcha` folder to `/wp-content/plugins/`.
2. Activate the plugin in **Plugins**.
3. Configure via **Settings → Press Math Captcha**.

## ⚙️ Configuration

- Enable/disable per form type
- Difficulty: Easy / Medium / Hard
- Operation: Addition / Subtraction / Multiplication / Random
- Hide captcha for logged-in users
- Custom error message
- Rate limiting (max attempts + block duration)
- Admin logs: logged user list + failed login log

## 🧩 Contact Form 7 Usage

Add a captcha field to any CF7 form:

```
[pmcMathcaptcha]   — optional
[pmcMathcaptcha*]  — required
```

## 🛡️ Security Notes

- Nonce verification on all form submissions
- Transient-based answer storage (auto-expire)
- IPs are hashed before storage
- Honeypot field for bot detection

## 🗂️ File Structure (Core)

```
press-math-captcha/
├── press-math-captcha.php
├── includes/
│   ├── class-pmc-loader.php
│   ├── class-pmc-captcha.php
│   ├── class-pmc-admin.php
│   ├── class-pmc-login.php
│   ├── class-pmc-cf7.php
│   ├── class-pmc-woocommerce.php
│   └── class-pmc-security.php
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
└── languages/press-math-captcha.pot
```

## ✅ Requirements

- WordPress 5.5+
- PHP 7.4+

## 📜 License

GPLv2 or later

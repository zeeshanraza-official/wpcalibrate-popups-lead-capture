=== WPCalibrate Popups & Lead Capture ===
Contributors: wpcalibrate
Donate link: https://wpcalibrate.com
Tags: popup, popups, lead capture, modal, optin
Requires at least: 6.5
Tested up to: 7.1.3
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, accessible WordPress popup builder and lead capture system with native block editing, page targeting, and frequency controls.

== Description ==

WPCalibrate Popups & Lead Capture provides a modern, high-performance popup and lead capture system for WordPress sites. Designed from the ground up for speed, user privacy, and accessibility (WCAG 2.2 AA compliant modal semantics), it leverages the native WordPress block editor for popup design rather than bundling heavy proprietary page builders.

= Core Features =
* **Native Block Editor Design**: Build beautiful popups using core WordPress blocks and our first-party Lead Form block.
* **Lightweight Vanilla Runtime**: Zero jQuery dependency; modern, deferred JavaScript runtime that loads only when eligible popups apply.
* **Comprehensive Lead Capture Form Block**: 14 field types including Name, Email, Telephone, Dropdown, Radio, Checkboxes, Consent checkbox, and Hidden fields with server-side validation.
* **Smart Triggers**: Page load / time delay, scroll percentage, and custom CSS selector click triggers.
* **Targeting Rules**: Target entire site, specific pages, exclude pages, post types, logged-in vs guest visitors, and device types.
* **Visitor Frequency Controls**: Show every visit, once per browser session, or once every X days. Suppress automatically after successful lead conversion.
* **Lead Management & CSV Export**: Browse, search, filter, and export captured leads with spreadsheet formula injection protection.
* **Privacy-First & GDPR Friendly**: No raw IP storage by default, no cross-site fingerprinting, automated retention cleanup, and full integration with WordPress Personal Data Export and Erase tools.
* **Built-in Operational Analytics**: Track impressions, conversions, and conversion rates without external third-party tracking scripts.

= Extend with Pro =
WPCalibrate Popups & Lead Capture Pro extends the core engine with:
* Exit-intent desktop trigger
* User inactivity trigger
* Multi-page view counter trigger
* Advanced URL query & UTM parameter targeting
* Traffic referrer & direct traffic targeting
* User role targeting
* Date/time scheduling in your site's timezone
* WooCommerce product, category, and cart targeting (HPOS compatible)
* Generic outgoing webhooks with SSRF defense

== Installation ==

1. Upload the `wpcalibrate-popups-lead-capture` directory to `/wp-content/plugins/` or install via the WordPress Plugins menu.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **WPCalibrate > Popups & Lead Capture** to create your first popup campaign.
4. Customize appearance, trigger, targeting, and lead settings.
5. Publish your campaign.

== Frequently Asked Questions ==

= Does this plugin require jQuery or a heavy framework? =
No. The frontend runtime is built using pure vanilla JavaScript with zero external runtime dependencies.

= Does this plugin affect page caching? =
No. Modals are rendered into the footer and display conditions (such as session-based frequency) are evaluated in the visitor's browser, maintaining full compatibility with server-side and plugin-based page caching.

= Does the Free Core plugin require WooCommerce? =
No. The Free Core plugin operates independently of WooCommerce. WooCommerce targeting features are provided via the optional Pro add-on and only load when WooCommerce is active.

= Does the plugin collect visitor IP addresses? =
No. By default, raw IP addresses are not stored in the leads database table. Rate-limiting uses one-way anonymized hashes.

== Screenshots ==

1. Popups list overview and performance metrics.
2. Designing popup campaigns with the native WordPress block editor.
3. Form builder with customizable field types and validation.
4. Lead management dashboard with search and CSV export.
5. Daily impressions and conversion analytics.

== Changelog ==

= 1.0.0 =
* Initial production release.
* Native Block Editor campaign workflow.
* Lead Form block with 14 field types.
* Time-delay, scroll depth, and click triggers.
* Site, page, device, and authentication targeting rules.
* Visitor frequency controls and conversion suppression.
* Custom database tables for leads and daily analytics.
* Secure CSV export with formula injection protection.
* Email notifications with header-injection defense.
* WordPress Personal Data Exporter and Eraser integrations.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.

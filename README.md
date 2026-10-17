# WPCalibrate Popups & Lead Capture

[![PHP Version](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-blue.svg)](https://php.net)
[![WordPress Version](https://img.shields.io/badge/WordPress-6.5%20--%207.1.3-blue.svg)](https://wordpress.org)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Release](https://img.shields.io/github/v/release/zeeshanraza-official/wpcalibrate-popups-lead-capture?color=orange)](https://github.com/zeeshanraza-official/wpcalibrate-popups-lead-capture/releases)

A lightweight, accessible, high-performance popup builder, lead capture system, targeting engine, visitor frequency controller, and analytics manager for WordPress.

Built with modern WordPress engineering standards: zero jQuery runtime, native Block Editor campaign workflow, dedicated database tables, formula injection protection, and built-in GitHub dashboard updates.

---

## Table of Contents

- [About the Plugin](#about-the-plugin)
- [Key Features](#key-features)
- [Architecture & Directory Structure](#architecture--directory-structure)
- [Documentation & Setup Guide](#documentation--setup-guide)
- [In-Dashboard Updates](#in-dashboard-updates)
- [Developer Extensibility & Hooks](#developer-extensibility--hooks)
- [Changelog](#changelog)
- [License & Credits](#license--credits)

---

## About the Plugin

**WPCalibrate Popups & Lead Capture** is engineered for digital marketers, website owners, and developers who require powerful modal marketing without compromising site speed, security, or accessibility.

### Why Choose WPCalibrate Popups?
* **Zero jQuery Footprint:** Pure vanilla ES6+ deferred JavaScript (~20KB unminified, ~5KB gzipped).
* **Conditional Asset Loading:** Zero styles or scripts are enqueued on pages where no popups are targeted to trigger.
* **Native Gutenberg Editing:** Craft popup content visually using standard WordPress blocks plus the first-party WPCalibrate Lead Form block.
* **Full Accessibility Compliance:** Fully conforms to WCAG 2.1 Level AA with accessible dialog focus trapping, `<dialog aria-modal="true">` semantics, ESC-key dismissal, and `prefers-reduced-motion` animation suppression.
* **Enterprise Security:** Nonce protection, speed-sanity verification against bots, honeypot spam protection, CSV formula injection defense, and automated GDPR compliance.

---

## Key Features

### 1. Trigger Engine
* **Time Delay:** Display popup after visitor has been on page for *N* seconds.
* **Scroll Depth:** Fire dynamically when visitor scrolls past *N*% of the page height.
* **Click Selector:** Open modals immediately upon clicking any CSS class or ID (e.g. `.open-promo-modal`).
* **Manual JavaScript API:** Open, close, and inspect popups programmatically using `window.WPCalibratePopups.open(id)`.

### 2. Targeting & Audience Segmentation
* **Sitewide or Page-Specific:** Target entire site, home page, single posts, pages, or custom URLs.
* **User Authentication State:** Segment specifically between logged-in users and anonymous guests.
* **Device Targeting:** Target desktop, tablet, or mobile browsers independently.

### 3. Smart Frequency & Suppression Controls
* **Every Visit:** Show on every qualifying page load.
* **Once Per Session:** Limit to once during visitor browser session.
* **Cooldown Interval:** Show once every *X* days using persistent local storage.
* **Conversion Suppression:** Automatically stop displaying once visitor successfully converts.

### 4. Native Lead Capture & Form Builder
* **First-Party Lead Form Block:** Integrated directly into the WordPress Block Editor with 14 field types.
* **Dedicated Database Storage:** High-volume leads stored in indexed `wp_wpcal_popup_leads` custom database table.
* **Instant Email Notifications:** Asynchronous submission alerts sent to site administrators with strict header injection sanitization.
* **CSV Lead Export:** 1-click export protected against spreadsheet formula injection attacks (`=`, `+`, `-`, `@`, `\t`, `\r`, `%`).

### 5. High-Performance Daily Analytics
* **Atomic Daily Metrics:** Aggregated impressions, submissions, and conversion rates stored in atomic daily rows (`wp_wpcal_popup_stats_daily`).
* **Zero Dashboard Lag:** Calculations execute in sub-millisecond queries with divide-by-zero math guards.

---

## Architecture & Directory Structure

```text
wpcalibrate-popups-lead-capture/
├── assets/
│   ├── admin/css/admin-style.css          # Design system, tabs, status badges
│   ├── editor/                            # Block editor sidebar controls
│   └── frontend/                          # Zero-jQuery modal runtime
├── blocks/
│   └── lead-form/                         # First-party Gutenberg lead form block
├── branding/                              # Official SVG & PNG logos and icons
├── includes/
│   ├── Activation/                        # Database migration & deactivation lifecycle
│   ├── Admin/                             # Admin menu, WP_List_Table controllers
│   ├── Analytics/                         # Aggregation & metrics manager
│   ├── Campaigns/                         # Campaign post type & repository
│   ├── Capabilities/                      # Fine-grained WordPress capabilities
│   ├── Database/                          # dbDelta migration schemas
│   ├── Frontend/                          # Runtime modal injection & HTML rendering
│   ├── Leads/                             # Lead repository & CSV exporter
│   ├── Notifications/                     # Email notifications with CRLF defense
│   ├── Privacy/                           # GDPR Personal Data Exporter & Eraser
│   ├── Rest/                              # REST API submission & analytics endpoints
│   ├── Rules/                             # Targeting rule registry & evaluators
│   ├── Security/                          # Rate limiter & formula sanitizer
│   ├── Settings/                          # Tabbed plugin configuration
│   └── Support/                           # Logger, SharedMenu & GitHubUpdater
├── templates/                             # Admin & frontend layout views
├── readme.txt                             # WordPress.org standard metadata
├── uninstall.php                          # Clean uninstall data purge handler
├── LICENSE                                # GNU General Public License v2
└── wpcalibrate-popups-lead-capture.php    # Main plugin bootstrap & constants
```

---

## Documentation & Setup Guide

### 1. Installation
1. Download the latest `wpcalibrate-popups-lead-capture-1.0.0.zip` from [Releases](https://github.com/zeeshanraza-official/wpcalibrate-popups-lead-capture/releases).
2. Inside your WordPress admin dashboard, navigate to **Plugins → Add New Plugin → Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### 2. Creating Your First Campaign (5-Step Method)
1. In the WordPress sidebar, open **WPCalibrate → Campaigns**.
2. Click **Create New Campaign**.
3. **Design Modal Content:** Use the Block Editor to arrange headings, images, discount codes, and insert the **WPCalibrate Lead Form** block.
4. **Configure Triggers & Targeting:** In the right sidebar **Campaign Settings** panel:
   * Select your trigger (Time Delay, Scroll Depth, or Click Selector).
   * Define targeting (Sitewide, specific pages, desktop/mobile).
   * Set frequency (e.g., Once per session or every 7 days).
5. **Publish & Enable:** Toggle **Enable Campaign** to Active and click **Publish**.

### 3. Reviewing Leads & Analytics
* **Captured Leads:** Navigate to **WPCalibrate → Captured Leads** to view submitted contact information, filter by campaign, or click **Export CSV**.
* **Performance Analytics:** Navigate to **WPCalibrate → Analytics** for 30-day impression counts, conversions, and conversion percentages.

---

## In-Dashboard Updates

This plugin includes native support for automated in-dashboard updates directly from this GitHub repository.

### How It Works:
1. **Native Update Checker:** The plugin includes `WPCalibrate\Popups\Support\GitHubUpdater` which checks GitHub releases via transient-cached API calls every 12 hours.
2. **WordPress Dashboard Prompts:** When a new release tag is published on GitHub, WordPress will display the standard native update notification on your **Plugins** page:
   > *There is a new version of WPCalibrate Popups & Lead Capture available. View version details or update now.*
3. **1-Click Update:** Clicking **Update Now** automatically downloads and installs the packaged release archive cleanly without overwriting custom configurations.
4. **Git Updater Compatibility:** Standard `GitHub Plugin URI: https://github.com/zeeshanraza-official/wpcalibrate-popups-lead-capture` headers are also present for sites running the [Git Updater](https://git-updater.com/) plugin.

---

## Developer Extensibility & Hooks

### Actions
* `wpcal_popups_loaded` — Fires after core container is fully booted:
  ```php
  add_action('wpcal_popups_loaded', function($plugin) {
      // Access core repositories or services
  });
  ```
* `wpcal_popups_register_rule` — Register custom targeting rules:
  ```php
  add_action('wpcal_popups_register_rule', function($registry) {
      $registry->register(new MyCustomRule());
  });
  ```
* `wpcal_lead_captured` — Fires immediately upon valid lead submission:
  ```php
  add_action('wpcal_lead_captured', function($lead, $campaign) {
      // Push lead to external CRM or mailing list
  }, 10, 2);
  ```

### Filters
* `wpcal_popup_should_render` — Override popup display evaluation:
  ```php
  add_filter('wpcal_popup_should_render', function(bool $shouldRender, $campaign) {
      return $shouldRender;
  }, 10, 2);
  ```
* `wpcal_export_csv_headers` — Customize lead CSV export column headers.
* `wpcal_notification_recipients` — Filter admin notification email addresses.

---

## Changelog

### [1.1.2] - 2026-10-17
* **Fixed:** Resolved fatal namespace error (`CampaignPostType not found in CampaignListTable.php`) on Campaigns admin screen.
* **Added:** Native GitHub dashboard updater integration (`GitHubUpdater.php`) for automatic updates directly from repository releases.
* **Enhanced:** Unit test coverage expanded to 40 passing assertions.

### [1.1.1] - 2026-10-17
* **Fixed:** Campaigns list table column headers rendering bug on custom admin screens.
* **Added:** Post status filter tabs (`All`, `Published`, `Drafts`, `Trash`) with real-time post counts.
* **Added:** Dedicated trash and bulk trash actions.

### [1.1.0] - 2026-10-17
* **Added:** Dedicated Overview tab (`wpcal-overview`) detailing architecture, purpose, system diagnostics, and 5-step methodology.
* **Added:** Unified modern administrative design system (`admin-style.css`) and shared navigation header (`header.php`).
* **Fixed:** 400x400 menu icon scaling in WordPress admin sidebar.

### [1.0.0] - 2026-10-17
* **Initial Production Release:** Free Core plugin with block-editor campaigns, 14-field lead form block, triggers, targeting, frequency controls, CSV export, and daily analytics.

---

## License & Credits

* **Author:** WPCalibrate ([wpcalibrate.com](https://wpcalibrate.com))
* **Support:** support@wpcalibrate.com | +44 7474 795976
* **Marketplace:** [marketplace.wpcalibrate.com](https://marketplace.wpcalibrate.com/)
* **License:** GNU General Public License v2 or later ([GPL-2.0-or-later](LICENSE))

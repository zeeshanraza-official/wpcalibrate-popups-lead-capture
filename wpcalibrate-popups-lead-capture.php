<?php
/**
 * Plugin Name:       WPCalibrate Popups & Lead Capture
 * Plugin URI:        https://marketplace.wpcalibrate.com/
 * Description:       A lightweight, high-performance WordPress popup builder, lead capture system, targeting engine, frequency controller, and analytics manager.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            WPCalibrate
 * Author URI:        https://wpcalibrate.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcalibrate-popups-lead-capture
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/zeeshanraza-official/wpcalibrate-popups-lead-capture
 * Primary Branch:    main
 * Release Asset:     true
 */

declare(strict_types=1);

namespace WPCalibrate\Popups;

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants.
define('WPCAL_POPUPS_VERSION', '1.0.0');
define('WPCAL_POPUPS_MIN_PHP_VERSION', '8.2.0');
define('WPCAL_POPUPS_MIN_WP_VERSION', '6.5.0');
define('WPCAL_POPUPS_FILE', __FILE__);
define('WPCAL_POPUPS_PATH', plugin_dir_path(__FILE__));
define('WPCAL_POPUPS_URL', plugin_dir_url(__FILE__));
define('WPCAL_POPUPS_BASENAME', plugin_basename(__FILE__));

// Compatibility and environment check.
if (version_compare(PHP_VERSION, WPCAL_POPUPS_MIN_PHP_VERSION, '<')) {
    add_action('admin_notices', static function (): void {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php esc_html_e('WPCalibrate Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'); ?></strong>:
                <?php
                printf(
                    /* translators: 1: Current PHP version, 2: Required PHP version */
                    esc_html__('Your server is running PHP version %1$s, but this plugin requires at least PHP %2$s. Please upgrade your PHP installation.', 'wpcalibrate-popups-lead-capture'),
                    esc_html(PHP_VERSION),
                    esc_html(WPCAL_POPUPS_MIN_PHP_VERSION)
                );
                ?>
            </p>
        </div>
        <?php
    });
    return;
}

global $wp_version;
if (isset($wp_version) && version_compare($wp_version, WPCAL_POPUPS_MIN_WP_VERSION, '<')) {
    add_action('admin_notices', static function (): void {
        global $wp_version;
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php esc_html_e('WPCalibrate Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'); ?></strong>:
                <?php
                printf(
                    /* translators: 1: Current WordPress version, 2: Required WordPress version */
                    esc_html__('Your site is running WordPress %1$s, but this plugin requires at least WordPress %2$s. Please update WordPress.', 'wpcalibrate-popups-lead-capture'),
                    esc_html($wp_version),
                    esc_html(WPCAL_POPUPS_MIN_WP_VERSION)
                );
                ?>
            </p>
        </div>
        <?php
    });
    return;
}

// Require PSR-4 Autoloader.
require_once WPCAL_POPUPS_PATH . 'includes/Autoloader.php';
Autoloader::register();

// Register activation and deactivation hooks.
register_activation_hook(__FILE__, static function (): void {
    Activation\Activator::activate();
});

register_deactivation_hook(__FILE__, static function (): void {
    Activation\Deactivator::deactivate();
});

// Boot the plugin.
add_action('plugins_loaded', static function (): void {
    Bootstrap::init();
}, 10);

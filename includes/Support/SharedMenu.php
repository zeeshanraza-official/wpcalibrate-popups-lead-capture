<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared Menu Manager for WPCalibrate plugins.
 * Ensures a single, non-colliding top-level "WPCalibrate" menu regardless of plugin load order.
 */
final class SharedMenu
{
    public const PARENT_SLUG = 'wpcalibrate-dashboard';

    /**
     * Registers the shared parent menu if not already registered by another WPCalibrate plugin.
     */
    public static function registerParentMenu(): void
    {
        global $menu;

        // Check if already registered by any WPCalibrate plugin in current request.
        if (!empty($GLOBALS['wpcalibrate_parent_menu_registered'])) {
            return;
        }

        // Check if menu slug already exists in global $menu array.
        if (is_array($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && $item[2] === self::PARENT_SLUG) {
                    $GLOBALS['wpcalibrate_parent_menu_registered'] = true;
                    return;
                }
            }
        }

        $iconUrl = self::getMenuIconUrl();

        add_menu_page(
            __('WPCalibrate', 'wpcalibrate-popups-lead-capture'),
            __('WPCalibrate', 'wpcalibrate-popups-lead-capture'),
            'manage_options',
            self::PARENT_SLUG,
            [self::class, 'renderSharedDashboard'],
            $iconUrl,
            59
        );

        add_action('admin_head', [self::class, 'outputMenuIconStyles']);

        $GLOBALS['wpcalibrate_parent_menu_registered'] = true;
    }

    /**
     * Injects custom CSS into admin head so the 400x400 branding icon scales
     * perfectly to 20x20 px with crisp centering and hover micro-interaction.
     */
    public static function outputMenuIconStyles(): void
    {
        ?>
        <style id="wpcal-menu-icon-css">
            #adminmenu .toplevel_page_wpcalibrate-dashboard .wp-menu-image {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            #adminmenu .toplevel_page_wpcalibrate-dashboard .wp-menu-image img {
                max-width: 20px !important;
                max-height: 20px !important;
                width: 20px !important;
                height: 20px !important;
                padding: 0 !important;
                margin: 0 !important;
                object-fit: contain !important;
                opacity: 0.88;
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            #adminmenu .toplevel_page_wpcalibrate-dashboard:hover .wp-menu-image img,
            #adminmenu .toplevel_page_wpcalibrate-dashboard.wp-has-current-submenu .wp-menu-image img {
                opacity: 1;
                transform: scale(1.08);
            }
        </style>
        <?php
    }

    /**
     * Get menu icon URL, using supplied branding assets.
     */
    public static function getMenuIconUrl(): string
    {
        $iconPath = WPCAL_POPUPS_PATH . 'branding/icon-white.png';
        if (file_exists($iconPath)) {
            return WPCAL_POPUPS_URL . 'branding/icon-white.png';
        }

        return 'dashicons-megaphone';
    }

    /**
     * Renders the shared WPCalibrate overview page if accessed directly.
     */
    public static function renderSharedDashboard(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        $logoDarkPath = WPCAL_POPUPS_PATH . 'branding/icon-dark.png';
        $logoDarkUrl  = file_exists($logoDarkPath) ? WPCAL_POPUPS_URL . 'branding/icon-dark.png' : '';
        $isProActive  = defined('WPCAL_POPUPS_PRO_VERSION');

        ?>
        <div class="wrap wpcal-admin-wrap wpcal-shared-dashboard">
            <div class="wpcal-dashboard-hero">
                <div class="wpcal-hero-brand">
                    <?php if ($logoDarkUrl !== ''): ?>
                        <img src="<?php echo esc_url($logoDarkUrl); ?>" alt="<?php esc_attr_e('WPCalibrate Logo', 'wpcalibrate-popups-lead-capture'); ?>" class="wpcal-hero-logo" />
                    <?php endif; ?>
                    <div>
                        <h1 class="wpcal-hero-title"><?php esc_html_e('WPCalibrate Ecosystem', 'wpcalibrate-popups-lead-capture'); ?></h1>
                        <p class="wpcal-hero-desc">
                            <?php esc_html_e('High-performance, accessibility-focused WordPress tools engineered for enterprise calibration, conversion, and performance.', 'wpcalibrate-popups-lead-capture'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="wpcal-card-grid">
                <!-- Popups & Lead Capture Card -->
                <div class="wpcal-card wpcal-card-featured">
                    <div class="wpcal-card-header">
                        <div class="wpcal-card-badge-row">
                            <span class="wpcal-badge wpcal-badge-primary"><?php esc_html_e('Active Module', 'wpcalibrate-popups-lead-capture'); ?></span>
                            <?php if ($isProActive): ?>
                                <span class="wpcal-badge wpcal-badge-pro"><?php esc_html_e('Pro Active', 'wpcalibrate-popups-lead-capture'); ?></span>
                            <?php else: ?>
                                <span class="wpcal-badge wpcal-badge-core"><?php esc_html_e('Core v1.0.0', 'wpcalibrate-popups-lead-capture'); ?></span>
                            <?php endif; ?>
                        </div>
                        <h2 class="wpcal-card-title"><?php esc_html_e('Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'); ?></h2>
                    </div>
                    <p class="wpcal-card-text">
                        <?php esc_html_e('Lightweight popup builder with native Gutenberg block editing, granular triggers (delay, scroll, click, exit-intent), 14 field types lead forms, and formula-safe CSV export.', 'wpcalibrate-popups-lead-capture'); ?>
                    </p>
                    <div class="wpcal-card-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-overview')); ?>" class="button button-primary">
                            <?php esc_html_e('Open Overview', 'wpcalibrate-popups-lead-capture'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-popups')); ?>" class="button button-secondary">
                            <?php esc_html_e('Campaigns', 'wpcalibrate-popups-lead-capture'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-leads')); ?>" class="button button-secondary">
                            <?php esc_html_e('Leads', 'wpcalibrate-popups-lead-capture'); ?>
                        </a>
                    </div>
                </div>

                <!-- Ecosystem Info Card -->
                <div class="wpcal-card">
                    <div class="wpcal-card-header">
                        <span class="wpcal-badge wpcal-badge-info"><?php esc_html_e('Standards & Quality', 'wpcalibrate-popups-lead-capture'); ?></span>
                        <h2 class="wpcal-card-title"><?php esc_html_e('Built for Production', 'wpcalibrate-popups-lead-capture'); ?></h2>
                    </div>
                    <ul class="wpcal-feature-list">
                        <li><strong><?php esc_html_e('Zero Bloat:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Vanilla ES6+ runtime without jQuery dependencies.', 'wpcalibrate-popups-lead-capture'); ?></li>
                        <li><strong><?php esc_html_e('WCAG 2.2 AA:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Focus-trapped dialogs, escape keys, and reduced-motion support.', 'wpcalibrate-popups-lead-capture'); ?></li>
                        <li><strong><?php esc_html_e('Security Hardened:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('SSRF protection, rate-limiting, and CSV formula injection defense.', 'wpcalibrate-popups-lead-capture'); ?></li>
                    </ul>
                    <div class="wpcal-card-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings')); ?>" class="button button-secondary">
                            <?php esc_html_e('Plugin Settings', 'wpcalibrate-popups-lead-capture'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}


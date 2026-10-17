<?php
declare(strict_types=1);

/**
 * Admin Overview Page Template: How to Use, Purpose & Method.
 *
 * @var array $systemStatus
 */

if (!defined('ABSPATH')) {
    exit;
}

$activeTab = 'overview';
$isProActive = defined('WPCAL_POPUPS_PRO_VERSION');
$newCampaignUrl = admin_url('post-new.php?post_type=wpcal_popup');
?>
<div class="wrap wpcal-admin-wrap">
    <?php include WPCAL_POPUPS_PATH . 'templates/admin/header.php'; ?>

    <!-- Welcome & Purpose Hero -->
    <div class="wpcal-dashboard-hero">
        <h2 class="wpcal-hero-title"><?php esc_html_e('Welcome to WPCalibrate Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'); ?></h2>
        <p class="wpcal-hero-desc">
            <?php esc_html_e('A production-engineered, lightweight WordPress popup builder and lead generation engine. Designed with native Gutenberg block editing, zero jQuery dependencies, strict WCAG 2.2 AA accessibility, formula-safe CSV exports, and dedicated custom database tables for maximum scale and privacy.', 'wpcalibrate-popups-lead-capture'); ?>
        </p>
    </div>

    <!-- Core Purpose & Method Architecture Cards -->
    <div class="wpcal-card-grid">
        <div class="wpcal-card wpcal-card-featured">
            <div class="wpcal-card-header">
                <span class="wpcal-badge wpcal-badge-primary"><?php esc_html_e('Purpose & Architecture', 'wpcalibrate-popups-lead-capture'); ?></span>
                <h3 class="wpcal-card-title"><?php esc_html_e('Native Block Content', 'wpcalibrate-popups-lead-capture'); ?></h3>
            </div>
            <p class="wpcal-card-text">
                <?php esc_html_e('Unlike bulky popup builders that load massive proprietary drag-and-drop engines, WPCalibrate uses the native WordPress block editor. Popups render standard WordPress blocks seamlessly with zero bloat and complete theme harmony.', 'wpcalibrate-popups-lead-capture'); ?>
            </p>
            <ul class="wpcal-feature-list">
                <li><?php esc_html_e('Zero jQuery — vanilla ES6+ runtime (~15KB deferred)', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><?php esc_html_e('WCAG 2.2 AA focus trap, escape dismiss, and reduced-motion support', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><?php esc_html_e('Zero assets enqueued globally on pages where no popup applies', 'wpcalibrate-popups-lead-capture'); ?></li>
            </ul>
        </div>

        <div class="wpcal-card">
            <div class="wpcal-card-header">
                <span class="wpcal-badge wpcal-badge-info"><?php esc_html_e('Trigger Engine', 'wpcalibrate-popups-lead-capture'); ?></span>
                <h3 class="wpcal-card-title"><?php esc_html_e('Granular Firing Rules', 'wpcalibrate-popups-lead-capture'); ?></h3>
            </div>
            <p class="wpcal-card-text">
                <?php esc_html_e('Control exactly when visitors see your campaign without annoying intrusive interruptions.', 'wpcalibrate-popups-lead-capture'); ?>
            </p>
            <ul class="wpcal-feature-list">
                <li><strong><?php esc_html_e('Page Load / Delay:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Fires smoothly after configurable milliseconds.', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><strong><?php esc_html_e('Scroll Depth:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Triggers when user reaches 0–100% of page depth.', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><strong><?php esc_html_e('Click / CSS Selector:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Binds to any button, link, or CTA element.', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><strong><?php esc_html_e('Pro Triggers:', 'wpcalibrate-popups-lead-capture'); ?></strong> <?php esc_html_e('Desktop exit-intent, inactivity, and page-views count.', 'wpcalibrate-popups-lead-capture'); ?></li>
            </ul>
        </div>

        <div class="wpcal-card">
            <div class="wpcal-card-header">
                <span class="wpcal-badge wpcal-badge-info"><?php esc_html_e('Lead Capture & Security', 'wpcalibrate-popups-lead-capture'); ?></span>
                <h3 class="wpcal-card-title"><?php esc_html_e('Enterprise Lead Pipeline', 'wpcalibrate-popups-lead-capture'); ?></h3>
            </div>
            <p class="wpcal-card-text">
                <?php esc_html_e('Capture, validate, store, and export visitor submissions with bank-grade defense.', 'wpcalibrate-popups-lead-capture'); ?>
            </p>
            <ul class="wpcal-feature-list">
                <li><?php esc_html_e('14 supported field types with client and server validation', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><?php esc_html_e('Honeypot anti-spam, sub-second speed check & rate limiting', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><?php esc_html_e('Formula injection neutralization on all CSV streaming exports', 'wpcalibrate-popups-lead-capture'); ?></li>
                <li><?php esc_html_e('GDPR personal data export and eraser tool integration', 'wpcalibrate-popups-lead-capture'); ?></li>
            </ul>
        </div>
    </div>

    <!-- Step-by-Step "How to Use" Method Workflow -->
    <div style="margin-top: 32px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
            <?php esc_html_e('How to Use: 5-Step Workflow', 'wpcalibrate-popups-lead-capture'); ?>
        </h2>
        <p style="color: #64748b; font-size: 14px; margin-top: 0;">
            <?php esc_html_e('Follow this structured methodology to build and launch high-converting popup campaigns.', 'wpcalibrate-popups-lead-capture'); ?>
        </p>

        <div class="wpcal-step-grid">
            <div class="wpcal-step-card">
                <div class="wpcal-step-number">1</div>
                <h3><?php esc_html_e('Create Campaign', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p><?php esc_html_e('Navigate to Campaigns > Add New. Give your popup a title and build your visual layout using any WordPress block.', 'wpcalibrate-popups-lead-capture'); ?></p>
            </div>

            <div class="wpcal-step-card">
                <div class="wpcal-step-number">2</div>
                <h3><?php esc_html_e('Insert Lead Form', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p><?php esc_html_e('Add the Lead Form block. Choose from 14 field types (email, name, telephone, consent), placeholders, and submit button.', 'wpcalibrate-popups-lead-capture'); ?></p>
            </div>

            <div class="wpcal-step-card">
                <div class="wpcal-step-number">3</div>
                <h3><?php esc_html_e('Configure Rules', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p><?php esc_html_e('In the sidebar panel, configure your trigger (delay, scroll, click), targeting rules (pages, devices, login state), and frequency.', 'wpcalibrate-popups-lead-capture'); ?></p>
            </div>

            <div class="wpcal-step-card">
                <div class="wpcal-step-number">4</div>
                <h3><?php esc_html_e('Preview Safely', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p><?php esc_html_e('Use the Admin Preview link to verify popup design and interactions without skewing production analytics or cookies.', 'wpcalibrate-popups-lead-capture'); ?></p>
            </div>

            <div class="wpcal-step-card">
                <div class="wpcal-step-number">5</div>
                <h3><?php esc_html_e('Publish & Track', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p><?php esc_html_e('Publish the campaign. Monitor impressions, conversions, and conversion rates, and export captured leads to CSV.', 'wpcalibrate-popups-lead-capture'); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Action Launchpad -->
    <div style="margin-top: 32px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">
            <?php esc_html_e('Quick Actions', 'wpcalibrate-popups-lead-capture'); ?>
        </h2>
        <div class="wpcal-card-grid">
            <div class="wpcal-card">
                <h3 class="wpcal-card-title"><?php esc_html_e('Create a Popup', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p class="wpcal-card-text"><?php esc_html_e('Design an announcement, lead magnet, coupon, or lead capture campaign.', 'wpcalibrate-popups-lead-capture'); ?></p>
                <div class="wpcal-card-actions">
                    <a href="<?php echo esc_url($newCampaignUrl); ?>" class="button button-primary"><?php esc_html_e('+ New Campaign', 'wpcalibrate-popups-lead-capture'); ?></a>
                </div>
            </div>

            <div class="wpcal-card">
                <h3 class="wpcal-card-title"><?php esc_html_e('Review Captured Leads', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p class="wpcal-card-text"><?php esc_html_e('View submissions, filter by campaign/date, search contacts, and export to CSV.', 'wpcalibrate-popups-lead-capture'); ?></p>
                <div class="wpcal-card-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-leads')); ?>" class="button button-secondary"><?php esc_html_e('Open Leads', 'wpcalibrate-popups-lead-capture'); ?></a>
                </div>
            </div>

            <div class="wpcal-card">
                <h3 class="wpcal-card-title"><?php esc_html_e('Performance Analytics', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p class="wpcal-card-text"><?php esc_html_e('Analyze daily impressions, conversions, and top-performing campaigns.', 'wpcalibrate-popups-lead-capture'); ?></p>
                <div class="wpcal-card-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-analytics')); ?>" class="button button-secondary"><?php esc_html_e('View Analytics', 'wpcalibrate-popups-lead-capture'); ?></a>
                </div>
            </div>

            <div class="wpcal-card">
                <h3 class="wpcal-card-title"><?php esc_html_e('System Settings & Privacy', 'wpcalibrate-popups-lead-capture'); ?></h3>
                <p class="wpcal-card-text"><?php esc_html_e('Configure email notifications, data retention schedules, webhooks, and license.', 'wpcalibrate-popups-lead-capture'); ?></p>
                <div class="wpcal-card-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings')); ?>" class="button button-secondary"><?php esc_html_e('Manage Settings', 'wpcalibrate-popups-lead-capture'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health & Environment Diagnostics -->
    <div style="margin-top: 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0;">
            <?php esc_html_e('System & Architecture Health', 'wpcalibrate-popups-lead-capture'); ?>
        </h2>
        <table class="wpcal-system-table">
            <tbody>
                <tr>
                    <th><?php esc_html_e('Free Core Version', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><code>v<?php echo esc_html(WPCAL_POPUPS_VERSION); ?></code> <span class="wpcal-badge wpcal-badge-success"><?php esc_html_e('Active', 'wpcalibrate-popups-lead-capture'); ?></span></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Pro Add-on Version', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php if ($isProActive): ?>
                            <code>v<?php echo esc_html(WPCAL_POPUPS_PRO_VERSION); ?></code> <span class="wpcal-badge wpcal-badge-pro"><?php esc_html_e('Active', 'wpcalibrate-popups-lead-capture'); ?></span>
                        <?php else: ?>
                            <span style="color: #64748b;"><?php esc_html_e('Not active', 'wpcalibrate-popups-lead-capture'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('PHP Version', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><code><?php echo esc_html(PHP_VERSION); ?></code> (<?php esc_html_e('Minimum required: 8.2', 'wpcalibrate-popups-lead-capture'); ?>)</td>
                </tr>
                <tr>
                    <th><?php esc_html_e('WordPress Version', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><code><?php echo esc_html(get_bloginfo('version')); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('WooCommerce Integration', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php if (class_exists('WooCommerce')): ?>
                            <span class="wpcal-badge wpcal-badge-success"><?php esc_html_e('Detected (HPOS & Blocks Compatible)', 'wpcalibrate-popups-lead-capture'); ?></span>
                        <?php else: ?>
                            <span style="color: #64748b;"><?php esc_html_e('WooCommerce not active (Core operates standalone)', 'wpcalibrate-popups-lead-capture'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Dedicated Database Tables', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <code><?php global $wpdb; echo esc_html($wpdb->prefix . 'wpcal_popup_leads'); ?></code><br>
                        <code><?php echo esc_html($wpdb->prefix . 'wpcal_popup_stats_daily'); ?></code>
                        <span class="wpcal-badge wpcal-badge-success" style="margin-left: 8px;"><?php esc_html_e('Verified', 'wpcalibrate-popups-lead-capture'); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

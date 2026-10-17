<?php

declare(strict_types=1);

/**
 * Admin Campaigns Page Template.
 *
 * @var \WPCalibrate\Popups\Admin\CampaignListTable $table
 * @var array $stats
 */

if (!defined('ABSPATH')) {
    exit;
}

$activeTab = 'campaigns';
?>
<div class="wrap wpcal-admin-wrap">
    <?php include WPCAL_POPUPS_PATH . 'templates/admin/header.php'; ?>

    <!-- Quick Overview Stats -->
    <div class="wpcal-stats-grid">
        <div class="wpcal-stat-card">
            <span class="wpcal-stat-label"><?php esc_html_e('30-Day Impressions', 'wpcalibrate-popups-lead-capture'); ?></span>
            <div class="wpcal-stat-value"><?php echo number_format_i18n($stats['impressions'] ?? 0); ?></div>
        </div>
        <div class="wpcal-stat-card">
            <span class="wpcal-stat-label"><?php esc_html_e('30-Day Conversions', 'wpcalibrate-popups-lead-capture'); ?></span>
            <div class="wpcal-stat-value is-conversions"><?php echo number_format_i18n($stats['conversions'] ?? 0); ?></div>
        </div>
        <div class="wpcal-stat-card">
            <span class="wpcal-stat-label"><?php esc_html_e('Avg Conversion Rate', 'wpcalibrate-popups-lead-capture'); ?></span>
            <div class="wpcal-stat-value is-rate"><?php echo esc_html((string) ($stats['conversion_rate'] ?? 0)); ?>%</div>
        </div>
    </div>

    <!-- Feedback Notices -->
    <?php if (!empty($_GET['trashed'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf(esc_html(_n('%d campaign moved to trash.', '%d campaigns moved to trash.', (int) $_GET['trashed'], 'wpcalibrate-popups-lead-capture')), (int) $_GET['trashed']); ?></p>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'duplicated'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Campaign duplicated successfully.', 'wpcalibrate-popups-lead-capture'); ?></p>
        </div>
    <?php endif; ?>

    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
        <input type="hidden" name="page" value="wpcal-popups" />
        <?php
        wp_nonce_field('bulk-campaigns');
        $table->views();
        $table->search_box(__('Search Campaigns', 'wpcalibrate-popups-lead-capture'), 'wpcal-campaign-search');
        $table->display();
        ?>
    </form>
</div>



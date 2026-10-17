<?php

declare(strict_types=1);

/**
 * Admin Analytics Dashboard Template.
 *
 * @var array $summary
 * @var array $topCampaigns
 * @var int $selectedDays
 * @var int $selectedPopupId
 * @var array<int, string> $campaignOptions
 */

if (!defined('ABSPATH')) {
    exit;
}
$activeTab = 'analytics';
?>
<div class="wrap wpcal-admin-wrap">
    <?php include WPCAL_POPUPS_PATH . 'templates/admin/header.php'; ?>

    <!-- Filter Form -->
    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin-bottom: 24px; display: flex; gap: 12px; align-items: center; background: #ffffff; padding: 14px 18px; border: 1px solid #cbd5e1; border-radius: 8px;">
        <input type="hidden" name="page" value="wpcal-analytics" />

        <label for="wpcal-days" style="font-weight: 600; font-size: 13px; color: #334155;"><?php esc_html_e('Date Range:', 'wpcalibrate-popups-lead-capture'); ?></label>
        <select id="wpcal-days" name="days" style="height: 36px;">
            <option value="7" <?php selected($selectedDays, 7); ?>><?php esc_html_e('Last 7 Days', 'wpcalibrate-popups-lead-capture'); ?></option>
            <option value="30" <?php selected($selectedDays, 30); ?>><?php esc_html_e('Last 30 Days', 'wpcalibrate-popups-lead-capture'); ?></option>
            <option value="90" <?php selected($selectedDays, 90); ?>><?php esc_html_e('Last 90 Days', 'wpcalibrate-popups-lead-capture'); ?></option>
        </select>

        <label for="wpcal-popup" style="font-weight: 600; font-size: 13px; color: #334155; margin-left: 10px;"><?php esc_html_e('Campaign:', 'wpcalibrate-popups-lead-capture'); ?></label>
        <select id="wpcal-popup" name="popup_id" style="height: 36px;">
            <option value="0"><?php esc_html_e('All Active Campaigns', 'wpcalibrate-popups-lead-capture'); ?></option>
            <?php foreach ($campaignOptions as $cId => $cTitle): ?>
                <option value="<?php echo esc_attr((string) $cId); ?>" <?php selected($selectedPopupId, $cId); ?>>
                    <?php echo esc_html($cTitle); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="button button-secondary" style="height: 36px;"><?php esc_html_e('Filter Report', 'wpcalibrate-popups-lead-capture'); ?></button>
    </form>

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px;">
        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700;"><?php esc_html_e('Total Impressions', 'wpcalibrate-popups-lead-capture'); ?></div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a; margin-top: 6px;"><?php echo number_format_i18n($summary['impressions'] ?? 0); ?></div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php esc_html_e('Popups actually displayed to visitors', 'wpcalibrate-popups-lead-capture'); ?></div>
        </div>

        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700;"><?php esc_html_e('Total Conversions', 'wpcalibrate-popups-lead-capture'); ?></div>
            <div style="font-size: 32px; font-weight: 800; color: #16a34a; margin-top: 6px;"><?php echo number_format_i18n($summary['conversions'] ?? 0); ?></div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php esc_html_e('Successful lead submissions captured', 'wpcalibrate-popups-lead-capture'); ?></div>
        </div>

        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700;"><?php esc_html_e('Conversion Rate', 'wpcalibrate-popups-lead-capture'); ?></div>
            <div style="font-size: 32px; font-weight: 800; color: #2563eb; margin-top: 6px;"><?php echo esc_html((string) ($summary['conversion_rate'] ?? 0)); ?>%</div>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php esc_html_e('Conversions / Impressions', 'wpcalibrate-popups-lead-capture'); ?></div>
        </div>
    </div>

    <!-- Top Performing Campaigns -->
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin-bottom: 28px;">
        <h2 style="font-size: 18px; font-weight: 600; margin-top: 0; margin-bottom: 14px;"><?php esc_html_e('Top Performing Campaigns', 'wpcalibrate-popups-lead-capture'); ?></h2>
        <?php if (!empty($topCampaigns)): ?>
            <table class="widefat striped" style="border: none;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Campaign', 'wpcalibrate-popups-lead-capture'); ?></th>
                        <th><?php esc_html_e('Impressions', 'wpcalibrate-popups-lead-capture'); ?></th>
                        <th><?php esc_html_e('Conversions', 'wpcalibrate-popups-lead-capture'); ?></th>
                        <th><?php esc_html_e('Conversion Rate', 'wpcalibrate-popups-lead-capture'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCampaigns as $tc): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_edit_post_link($tc['popup_id']) ?: '#'); ?>">
                                        <?php echo esc_html($campaignOptions[$tc['popup_id']] ?? sprintf(__('Campaign #%d', 'wpcalibrate-popups-lead-capture'), $tc['popup_id'])); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo number_format_i18n($tc['impressions']); ?></td>
                            <td><strong><?php echo number_format_i18n($tc['conversions']); ?></strong></td>
                            <td><span style="color: #2563eb; font-weight: 600;"><?php echo esc_html((string) $tc['conversion_rate']); ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #64748b; font-style: italic; margin: 0;"><?php esc_html_e('No analytics recorded yet.', 'wpcalibrate-popups-lead-capture'); ?></p>
        <?php endif; ?>
    </div>
</div>

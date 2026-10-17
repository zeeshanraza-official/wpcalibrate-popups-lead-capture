<?php

declare(strict_types=1);

/**
 * Admin Leads Page Template.
 *
 * @var \WPCalibrate\Popups\Admin\LeadListTable|null $table
 * @var \WPCalibrate\Popups\Leads\LeadModel|null $singleLead
 * @var array<int, string> $campaignOptions
 */

if (!defined('ABSPATH')) {
    exit;
}

$activeTab = 'leads';
$exportUrl = wp_nonce_url(
    add_query_arg([
        'page'   => 'wpcal-leads',
        'action' => 'export_csv',
    ], admin_url('admin.php')),
    'wpcal_export_leads'
);
?>
<div class="wrap wpcal-admin-wrap">
    <?php include WPCAL_POPUPS_PATH . 'templates/admin/header.php'; ?>

    <?php if ($singleLead): ?>
        <!-- Single Lead Detail View -->
        <div style="margin-bottom: 20px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-leads')); ?>" class="button button-secondary">
                &larr; <?php esc_html_e('Back to All Leads', 'wpcalibrate-popups-lead-capture'); ?>
            </a>
        </div>


        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; max-width: 800px;">
            <h1 style="margin-top: 0; font-size: 22px; font-weight: 600; color: #0f172a;">
                <?php printf(esc_html__('Lead Details: %s', 'wpcalibrate-popups-lead-capture'), esc_html($singleLead->getFullName())); ?>
            </h1>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row" style="width: 180px;"><?php esc_html_e('Lead UUID', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><code><?php echo esc_html($singleLead->getUuid()); ?></code></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Date Captured', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><?php echo esc_html($singleLead->getCreatedAt()); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Email Address', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php if ($singleLead->getEmail()): ?>
                            <a href="mailto:<?php echo esc_attr($singleLead->getEmail()); ?>">
                                <?php echo esc_html($singleLead->getEmail()); ?>
                            </a>
                        <?php else: ?>
                            <em><?php esc_html_e('Not provided', 'wpcalibrate-popups-lead-capture'); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('First Name', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><?php echo esc_html($singleLead->getFirstName() ?: '—'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Last Name', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><?php echo esc_html($singleLead->getLastName() ?: '—'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Telephone', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><?php echo esc_html($singleLead->getPhone() ?: '—'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Associated Campaign', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <a href="<?php echo esc_url(get_edit_post_link($singleLead->getPopupId()) ?: '#'); ?>">
                            <?php printf(esc_html__('Campaign #%d', 'wpcalibrate-popups-lead-capture'), $singleLead->getPopupId()); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Source URL', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php if ($singleLead->getSourceUrl()): ?>
                            <a href="<?php echo esc_url($singleLead->getSourceUrl()); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($singleLead->getSourceUrl()); ?>
                            </a>
                        <?php else: ?>
                            <em><?php esc_html_e('Not recorded', 'wpcalibrate-popups-lead-capture'); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Notification Status', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td><span class="badge"><?php echo esc_html(ucfirst($singleLead->getNotificationStatus())); ?></span></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Submitted Custom Fields', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php
                        $customFields = $singleLead->getFields();
                        if (!empty($customFields)):
                        ?>
                            <table class="widefat striped" style="max-width: 500px;">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Field Key', 'wpcalibrate-popups-lead-capture'); ?></th>
                                        <th><?php esc_html_e('Value', 'wpcalibrate-popups-lead-capture'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customFields as $fk => $fv): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html((string) $fk); ?></strong></td>
                                            <td><?php echo esc_html(is_array($fv) ? implode(', ', $fv) : (string) $fv); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <em><?php esc_html_e('No custom fields submitted', 'wpcalibrate-popups-lead-capture'); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Consent History', 'wpcalibrate-popups-lead-capture'); ?></th>
                    <td>
                        <?php
                        $consent = $singleLead->getConsent();
                        if (!empty($consent)):
                            foreach ($consent as $ck => $cv):
                        ?>
                                <div>
                                    <strong><?php echo esc_html((string) $ck); ?>:</strong>
                                    <span><?php echo esc_html($cv['text'] ?? 'Consent agreed'); ?></span>
                                    <small style="color: #64748b;">(<?php echo esc_html($cv['time'] ?? ''); ?>)</small>
                                </div>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <em><?php esc_html_e('No explicit consent records', 'wpcalibrate-popups-lead-capture'); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

    <?php else: ?>
        <!-- Lead List Table -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="wp-heading-inline" style="font-size: 24px; font-weight: 600;">
                <?php esc_html_e('Captured Leads', 'wpcalibrate-popups-lead-capture'); ?>
            </h1>
            <a href="<?php echo esc_url($exportUrl); ?>" class="button button-primary">
                <?php esc_html_e('Export to CSV', 'wpcalibrate-popups-lead-capture'); ?>
            </a>
        </div>

        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="wpcal-leads" />
            <?php
            if ($table) {
                $table->views();
                $table->search_box(__('Search Leads', 'wpcalibrate-popups-lead-capture'), 'wpcal-lead-search');
                $table->display();
            }
            ?>
        </form>
    <?php endif; ?>
</div>

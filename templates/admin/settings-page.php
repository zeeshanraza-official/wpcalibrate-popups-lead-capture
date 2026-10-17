<?php

declare(strict_types=1);

/**
 * Admin Tabbed Settings Page Template.
 *
 * @var string $activeTab
 * @var array $settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$tabs = [
    'general'      => __('General', 'wpcalibrate-popups-lead-capture'),
    'leads'        => __('Lead Handling', 'wpcalibrate-popups-lead-capture'),
    'email'        => __('Email Notifications', 'wpcalibrate-popups-lead-capture'),
    'privacy'      => __('Data & Privacy', 'wpcalibrate-popups-lead-capture'),
    'performance'  => __('Performance', 'wpcalibrate-popups-lead-capture'),
    'integrations' => __('Integrations', 'wpcalibrate-popups-lead-capture'),
    'license'      => __('License', 'wpcalibrate-popups-lead-capture'),
    'tools'        => __('Tools', 'wpcalibrate-popups-lead-capture'),
    'contact'      => __('Contact', 'wpcalibrate-popups-lead-capture'),
];

$nonceField = wp_nonce_field('wpcal_save_settings', 'wpcal_settings_nonce', true, false);
$activeTabTop = 'settings';
?>
<div class="wrap wpcal-admin-wrap">
    <?php 
    $activeTab = $activeTabTop;
    include WPCAL_POPUPS_PATH . 'templates/admin/header.php'; 
    $activeTab = sanitize_key($_GET['tab'] ?? 'general');
    ?>

    <?php if (isset($_GET['updated'])): ?>

        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'wpcalibrate-popups-lead-capture'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e('Settings Sections', 'wpcalibrate-popups-lead-capture'); ?>">
        <?php foreach ($tabs as $slug => $label): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings&tab=' . $slug)); ?>"
               class="nav-tab <?php echo $activeTab === $slug ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div style="background: #ffffff; border: 1px solid #ccd0d4; border-top: none; padding: 24px; max-width: 820px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <?php if ($activeTab === 'contact'): ?>
            <?php include WPCAL_POPUPS_PATH . 'templates/admin/contact-section.php'; ?>
        <?php elseif ($activeTab === 'license'): ?>
            <div class="wpcal-license-box">
                <h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e('Pro License Key', 'wpcalibrate-popups-lead-capture'); ?></h2>
                <p style="color: #64748b; font-size: 14px;">
                    <?php esc_html_e('Enter your WPCalibrate Pro license key to unlock advanced targeting triggers, WooCommerce integration, scheduling, and outgoing webhooks.', 'wpcalibrate-popups-lead-capture'); ?>
                </p>

                <div class="notice notice-info inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Development Notice', 'wpcalibrate-popups-lead-capture'); ?>:</strong>
                        <?php esc_html_e('License provider configuration is not active in this development build. Live remote activation calls and simulated expiry dates are disabled.', 'wpcalibrate-popups-lead-capture'); ?>
                    </p>
                </div>

                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings&tab=license')); ?>">
                    <?php echo $nonceField; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <input type="hidden" name="wpcal_action" value="save_license" />

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="wpcal_license_key"><?php esc_html_e('License Key', 'wpcalibrate-popups-lead-capture'); ?></label></th>
                            <td>
                                <input type="password" id="wpcal_license_key" name="license_key" value="" class="regular-text" placeholder="WPCAL-PRO-XXXX-XXXX" autocomplete="off" />
                                <p class="description"><?php esc_html_e('Your license key is securely masked and never exposed in REST responses or error logs.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Status', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 4px; font-weight: 600;">
                                    <?php esc_html_e('Unconfigured (Dev Build)', 'wpcalibrate-popups-lead-capture'); ?>
                                </span>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Save License', 'wpcalibrate-popups-lead-capture')); ?>
                </form>
            </div>
        <?php elseif ($activeTab === 'integrations'): ?>
            <div class="wpcal-integrations-box">
                <h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e('Service Integrations', 'wpcalibrate-popups-lead-capture'); ?></h2>
                <p style="color: #64748b; font-size: 14px;">
                    <?php esc_html_e('Connect your lead capture forms with downstream marketing automation tools, generic webhooks, and CRM systems.', 'wpcalibrate-popups-lead-capture'); ?>
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 18px; margin-top: 15px;">
                    <h3 style="margin-top: 0; font-size: 15px;"><?php esc_html_e('Generic Outgoing Webhook (Pro)', 'wpcalibrate-popups-lead-capture'); ?></h3>
                    <p style="color: #64748b; font-size: 13px;">
                        <?php esc_html_e('Dispatches lead creation payloads via secure HTTP POST to Zapier, Make, n8n, or your custom API endpoint.', 'wpcalibrate-popups-lead-capture'); ?>
                    </p>
                    <span class="badge" style="background: #eff6ff; color: #2563eb; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                        <?php esc_html_e('Pro Feature Module', 'wpcalibrate-popups-lead-capture'); ?>
                    </span>
                </div>
            </div>
        <?php elseif ($activeTab === 'tools'): ?>
            <div class="wpcal-tools-box">
                <h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e('Maintenance & Diagnostic Tools', 'wpcalibrate-popups-lead-capture'); ?></h2>
                <p style="color: #64748b; font-size: 14px;">
                    <?php esc_html_e('Utilities for cache clearing, database migration checks, and diagnostics.', 'wpcalibrate-popups-lead-capture'); ?>
                </p>

                <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings&tab=tools')); ?>">
                    <?php echo $nonceField; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <input type="hidden" name="wpcal_action" value="clear_cache" />
                    <p>
                        <?php submit_button(__('Clear Popup Caches', 'wpcalibrate-popups-lead-capture'), 'secondary', 'submit', false); ?>
                        <span class="description" style="margin-left: 10px;"><?php esc_html_e('Flushes campaign object caches and resets compiled targeting state.', 'wpcalibrate-popups-lead-capture'); ?></span>
                    </p>
                </form>
            </div>
        <?php else: ?>
            <!-- Main Settings Form -->
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings&tab=' . $activeTab)); ?>">
                <?php echo $nonceField; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <input type="hidden" name="wpcal_action" value="save_settings" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($activeTab); ?>" />

                <?php if ($activeTab === 'general'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Runtime Caching', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <label for="disable_runtime_caching">
                                    <input type="checkbox" id="disable_runtime_caching" name="settings[disable_runtime_caching]" value="1" <?php checked(!empty($settings['disable_runtime_caching'])); ?> />
                                    <?php esc_html_e('Bypass internal campaign object cache', 'wpcalibrate-popups-lead-capture'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Enable only if testing real-time campaign modifications on staging environments.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                    </table>

                <?php elseif ($activeTab === 'leads'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Source URL Tracking', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <label for="collect_source_url">
                                    <input type="checkbox" id="collect_source_url" name="settings[collect_source_url]" value="1" <?php checked(!empty($settings['collect_source_url'])); ?> />
                                    <?php esc_html_e('Record the page URL where the lead submission occurred', 'wpcalibrate-popups-lead-capture'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Helps attribute leads to specific landing pages. Disabling this removes URL tracking for enhanced privacy.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                    </table>

                <?php elseif ($activeTab === 'email'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Email Notifications', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <label for="enable_notifications">
                                    <input type="checkbox" id="enable_notifications" name="settings[enable_notifications]" value="1" <?php checked(!empty($settings['enable_notifications'])); ?> />
                                    <?php esc_html_e('Send an email alert whenever a new lead is captured', 'wpcalibrate-popups-lead-capture'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="notification_recipient"><?php esc_html_e('Default Recipient Address', 'wpcalibrate-popups-lead-capture'); ?></label></th>
                            <td>
                                <input type="email" id="notification_recipient" name="settings[notification_recipient]" value="<?php echo esc_attr($settings['notification_recipient'] ?? get_option('admin_email')); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('Individual campaigns can override this recipient in their campaign lead settings.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                    </table>

                <?php elseif ($activeTab === 'privacy'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="retention_days"><?php esc_html_e('Lead Data Retention (Days)', 'wpcalibrate-popups-lead-capture'); ?></label></th>
                            <td>
                                <input type="number" id="retention_days" name="settings[retention_days]" value="<?php echo esc_attr((string) ($settings['retention_days'] ?? 0)); ?>" min="0" max="3650" class="small-text" />
                                <span class="description"><?php esc_html_e('days (Enter 0 to retain leads indefinitely until manually deleted).', 'wpcalibrate-popups-lead-capture'); ?></span>
                                <p class="description"><?php esc_html_e('When set to a positive number, a daily scheduled background task automatically removes leads older than this threshold.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Data Deletion on Uninstall', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <label for="delete_on_uninstall" style="color: #b91c1c; font-weight: 600;">
                                    <input type="checkbox" id="delete_on_uninstall" name="settings[delete_on_uninstall]" value="1" <?php checked(!empty($settings['delete_on_uninstall'])); ?> />
                                    <?php esc_html_e('Delete all WPCalibrate Popups data when uninstalled', 'wpcalibrate-popups-lead-capture'); ?>
                                </label>
                                <p class="description" style="color: #b91c1c;">
                                    <?php esc_html_e('CAUTION: If checked, uninstalling this plugin will permanently drop custom lead and stats tables, delete campaign posts, and remove settings. Other WPCalibrate plugins remain untouched.', 'wpcalibrate-popups-lead-capture'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                <?php elseif ($activeTab === 'performance'): ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Debug Logging', 'wpcalibrate-popups-lead-capture'); ?></th>
                            <td>
                                <label for="enable_debug_logging">
                                    <input type="checkbox" id="enable_debug_logging" name="settings[enable_debug_logging]" value="1" <?php checked(!empty($settings['enable_debug_logging'])); ?> />
                                    <?php esc_html_e('Enable verbose diagnostic logging', 'wpcalibrate-popups-lead-capture'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Logs non-sensitive operational notices to WordPress debug log. Never logs credentials or personal lead data.', 'wpcalibrate-popups-lead-capture'); ?></p>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
    </div>
</div>

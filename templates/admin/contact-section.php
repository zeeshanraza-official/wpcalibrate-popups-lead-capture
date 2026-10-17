<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$logoDark = WPCAL_POPUPS_URL . 'branding/icon-dark.png';
?>
<div class="wpcal-contact-card" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 24px; max-width: 650px; margin-top: 20px;">
    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
        <img src="<?php echo esc_url($logoDark); ?>" alt="<?php esc_attr_e('WPCalibrate', 'wpcalibrate-popups-lead-capture'); ?>" style="height: 36px; width: auto;" />
        <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #0f172a;"><?php esc_html_e('WPCalibrate Support & Inquiries', 'wpcalibrate-popups-lead-capture'); ?></h2>
    </div>

    <p style="color: #475569; font-size: 14px; line-height: 1.6;">
        <?php esc_html_e('Need assistance with your popup campaigns, custom feature implementation, or enterprise onboarding? Our engineering and support team is here to help.', 'wpcalibrate-popups-lead-capture'); ?>
    </p>

    <table class="form-table" role="presentation" style="margin-top: 15px;">
        <tr>
            <th scope="row" style="width: 140px; font-weight: 600; color: #334155;"><?php esc_html_e('Email Support', 'wpcalibrate-popups-lead-capture'); ?></th>
            <td>
                <a href="mailto:support@wpcalibrate.com" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                    support@wpcalibrate.com
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row" style="font-weight: 600; color: #334155;"><?php esc_html_e('Official Website', 'wpcalibrate-popups-lead-capture'); ?></th>
            <td>
                <a href="https://wpcalibrate.com" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                    https://wpcalibrate.com &#8599;
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row" style="font-weight: 600; color: #334155;"><?php esc_html_e('Marketplace', 'wpcalibrate-popups-lead-capture'); ?></th>
            <td>
                <a href="https://marketplace.wpcalibrate.com/" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                    https://marketplace.wpcalibrate.com/ &#8599;
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row" style="font-weight: 600; color: #334155;"><?php esc_html_e('Phone / WhatsApp', 'wpcalibrate-popups-lead-capture'); ?></th>
            <td>
                <a href="tel:+447474795976" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                    +44 7474 795976
                </a>
            </td>
        </tr>
    </table>
</div>

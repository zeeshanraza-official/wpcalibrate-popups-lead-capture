<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Notifications;

use WPCalibrate\Popups\Campaigns\CampaignModel;
use WPCalibrate\Popups\Leads\LeadModel;
use WPCalibrate\Popups\Leads\LeadRepository;
use WPCalibrate\Popups\Support\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles transactional lead notification emails with strict email-header injection protection.
 */
final class EmailNotifier
{
    private LeadRepository $leadRepo;

    public function __construct(LeadRepository $leadRepo)
    {
        $this->leadRepo = $leadRepo;
    }

    /**
     * Send lead alert email to configured recipient(s).
     */
    public function notify(LeadModel $lead, ?CampaignModel $campaign = null): bool
    {
        $globalSettings = get_option('wpcal_popups_settings', []);
        $emailEnabled   = !empty($globalSettings['enable_notifications'] ?? true);

        if (!$emailEnabled) {
            $lead->setNotificationStatus('disabled');
            $this->leadRepo->update($lead);
            return true;
        }

        // Determine recipient: Campaign override or global settings or admin email
        $recipient = '';
        if ($campaign) {
            $leadSettings = $campaign->getLeadSettings();
            $recipient = sanitize_email($leadSettings['notification_email'] ?? '');
        }

        if ($recipient === '') {
            $adminEmail = (string) get_option('admin_email', 'admin@example.com');
            $recipient = sanitize_email((string) ($globalSettings['notification_recipient'] ?? $adminEmail));
        }

        if (!is_email($recipient)) {
            $lead->setNotificationStatus('invalid_recipient');
            $this->leadRepo->update($lead);
            Logger::warning('Lead notification aborted: Invalid recipient email address.');
            return false;
        }

        $campaignTitle = $campaign ? $campaign->getTitle() : sprintf(__('Campaign #%d', 'wpcalibrate-popups-lead-capture'), $lead->getPopupId());

        // Header injection prevention: Strip all CR and LF characters
        $safeSubject = str_replace(["\r", "\n"], '', sprintf(
            /* translators: 1: Campaign title, 2: Site name */
            __('[New Lead] %1$s on %2$s', 'wpcalibrate-popups-lead-capture'),
            $campaignTitle,
            get_bloginfo('name')
        ));

        // Build message body
        $body  = sprintf(__("A new lead was submitted via WPCalibrate Popups:\n\n", 'wpcalibrate-popups-lead-capture'));
        $body .= sprintf(__("Campaign: %s\n", 'wpcalibrate-popups-lead-capture'), $campaignTitle);
        $body .= sprintf(__("Date: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getCreatedAt());

        if ($lead->getEmail()) {
            $body .= sprintf(__("Email: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getEmail());
        }
        if ($lead->getFirstName()) {
            $body .= sprintf(__("First Name: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getFirstName());
        }
        if ($lead->getLastName()) {
            $body .= sprintf(__("Last Name: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getLastName());
        }
        if ($lead->getPhone()) {
            $body .= sprintf(__("Phone: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getPhone());
        }
        if ($lead->getSourceUrl()) {
            $body .= sprintf(__("Source Page: %s\n", 'wpcalibrate-popups-lead-capture'), $lead->getSourceUrl());
        }

        $customFields = $lead->getFields();
        if (!empty($customFields)) {
            $body .= sprintf(__("\n--- Additional Submitted Fields ---\n", 'wpcalibrate-popups-lead-capture'));
            foreach ($customFields as $k => $v) {
                $valStr = is_array($v) ? implode(', ', $v) : (string) $v;
                $body .= sprintf("%s: %s\n", sanitize_text_field((string) $k), sanitize_text_field($valStr));
            }
        }

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        if ($lead->getEmail() && is_email($lead->getEmail())) {
            $cleanReplyTo = str_replace(["\r", "\n"], '', $lead->getEmail());
            $headers[] = 'Reply-To: ' . $cleanReplyTo;
        }

        /**
         * Filter email notification parameters before sending.
         */
        $filtered = apply_filters('wpcal_popups_notification_mail_args', [
            'to'      => $recipient,
            'subject' => $safeSubject,
            'message' => $body,
            'headers' => $headers,
        ], $lead, $campaign);

        try {
            $sent = wp_mail(
                $filtered['to'],
                $filtered['subject'],
                $filtered['message'],
                $filtered['headers']
            );

            $status = $sent ? 'sent' : 'failed';
            $lead->setNotificationStatus($status);
            $this->leadRepo->update($lead);

            if (!$sent) {
                Logger::warning('wp_mail() returned false for lead notification.', ['lead_id' => $lead->getId()]);
            }

            return $sent;
        } catch (\Throwable $e) {
            $lead->setNotificationStatus('error');
            $this->leadRepo->update($lead);
            Logger::error('Exception during lead notification dispatch: ' . $e->getMessage());
            return false;
        }
    }
}

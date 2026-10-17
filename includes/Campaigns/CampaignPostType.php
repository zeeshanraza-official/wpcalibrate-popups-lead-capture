<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Campaigns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the 'wpcal_popup' custom post type and associated meta fields.
 */
final class CampaignPostType
{
    public const POST_TYPE = 'wpcal_popup';
    public const META_KEY  = '_wpcal_popup_config';

    public static function register(): void
    {
        $labels = [
            'name'                  => _x('Popups & Campaigns', 'Post type general name', 'wpcalibrate-popups-lead-capture'),
            'singular_name'         => _x('Popup Campaign', 'Post type singular name', 'wpcalibrate-popups-lead-capture'),
            'menu_name'             => _x('Popups', 'Admin Menu text', 'wpcalibrate-popups-lead-capture'),
            'name_admin_bar'        => _x('Popup Campaign', 'Add New on Toolbar', 'wpcalibrate-popups-lead-capture'),
            'add_new'               => __('Add New', 'wpcalibrate-popups-lead-capture'),
            'add_new_item'          => __('Add New Popup Campaign', 'wpcalibrate-popups-lead-capture'),
            'new_item'              => __('New Popup Campaign', 'wpcalibrate-popups-lead-capture'),
            'edit_item'             => __('Edit Popup Campaign', 'wpcalibrate-popups-lead-capture'),
            'view_item'             => __('View Popup Campaign', 'wpcalibrate-popups-lead-capture'),
            'all_items'             => __('All Campaigns', 'wpcalibrate-popups-lead-capture'),
            'search_items'          => __('Search Campaigns', 'wpcalibrate-popups-lead-capture'),
            'not_found'             => __('No campaigns found.', 'wpcalibrate-popups-lead-capture'),
            'not_found_in_trash'    => __('No campaigns found in Trash.', 'wpcalibrate-popups-lead-capture'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Managed via WPCalibrate parent menu
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'editor'],
            'show_in_rest'       => true,
            'exclude_from_search'=> true,
        ];

        register_post_type(self::POST_TYPE, $args);

        self::registerMeta();
    }

    private static function registerMeta(): void
    {
        register_post_meta(self::POST_TYPE, self::META_KEY, [
            'show_in_rest'      => [
                'schema' => [
                    'type'                 => 'object',
                    'additionalProperties' => true,
                ],
            ],
            'single'            => true,
            'type'              => 'object',
            'sanitize_callback' => [self::class, 'sanitizeConfig'],
            'auth_callback'     => static function (): bool {
                return current_user_can('edit_posts');
            },
        ]);
    }

    /**
     * Sanitizes the campaign configuration array before saving to post meta.
     */
    public static function sanitizeConfig(mixed $config): array
    {
        if (!is_array($config)) {
            return [];
        }

        $clean = [];

        // Enabled flag
        $clean['enabled'] = !empty($config['enabled']);

        // Trigger settings
        $clean['trigger'] = [
            'type'              => sanitize_key($config['trigger']['type'] ?? 'time_delay'),
            'time_delay_ms'     => max(0, (int) ($config['trigger']['time_delay_ms'] ?? 3000)),
            'scroll_percentage' => min(100, max(0, absint($config['trigger']['scroll_percentage'] ?? 50))),
            'click_selector'    => sanitize_text_field($config['trigger']['click_selector'] ?? ''),
            'inactivity_seconds'=> max(1, absint($config['trigger']['inactivity_seconds'] ?? 15)),
            'page_views_count'  => max(1, absint($config['trigger']['page_views_count'] ?? 3)),
        ];

        // Targeting settings
        $clean['targeting'] = [
            'mode'       => in_array($config['targeting']['mode'] ?? '', ['all', 'specific', 'exclude'], true) ? $config['targeting']['mode'] : 'all',
            'page_ids'   => isset($config['targeting']['page_ids']) && is_array($config['targeting']['page_ids']) ? array_map('absint', $config['targeting']['page_ids']) : [],
            'post_types' => isset($config['targeting']['post_types']) && is_array($config['targeting']['post_types']) ? array_map('sanitize_key', $config['targeting']['post_types']) : ['page', 'post'],
            'auth_state' => in_array($config['targeting']['auth_state'] ?? '', ['all', 'logged_in', 'logged_out'], true) ? $config['targeting']['auth_state'] : 'all',
            'devices'    => isset($config['targeting']['devices']) && is_array($config['targeting']['devices']) ? array_map('sanitize_key', $config['targeting']['devices']) : ['desktop', 'tablet', 'mobile'],
            'groups'     => isset($config['targeting']['groups']) && is_array($config['targeting']['groups']) ? $config['targeting']['groups'] : [],
        ];

        // Frequency settings
        $clean['frequency'] = [
            'rule'                   => in_array($config['frequency']['rule'] ?? '', ['every_visit', 'once_per_session', 'once_per_days'], true) ? $config['frequency']['rule'] : 'every_visit',
            'days'                   => max(1, absint($config['frequency']['days'] ?? 7)),
            'suppress_on_conversion' => !empty($config['frequency']['suppress_on_conversion']),
            'max_impressions'        => max(0, absint($config['frequency']['max_impressions'] ?? 0)),
        ];

        // Appearance settings
        $clean['appearance'] = [
            'width'                   => sanitize_text_field($config['appearance']['width'] ?? '580px'),
            'max_width'               => sanitize_text_field($config['appearance']['max_width'] ?? '92%'),
            'padding'                 => sanitize_text_field($config['appearance']['padding'] ?? '32px'),
            'border_radius'           => sanitize_text_field($config['appearance']['border_radius'] ?? '12px'),
            'background_color'        => sanitize_text_field($config['appearance']['background_color'] ?? '#ffffff'),
            'text_color'              => sanitize_text_field($config['appearance']['text_color'] ?? '#1e293b'),
            'text_align'              => in_array($config['appearance']['text_align'] ?? '', ['left', 'center', 'right'], true) ? $config['appearance']['text_align'] : 'left',
            'overlay_color'           => sanitize_text_field($config['appearance']['overlay_color'] ?? 'rgba(15, 23, 42, 0.65)'),
            'close_button_position'   => in_array($config['appearance']['close_button_position'] ?? '', ['inside_top_right', 'outside_top_right', 'inside_top_left'], true) ? $config['appearance']['close_button_position'] : 'inside_top_right',
            'close_button_color'      => sanitize_text_field($config['appearance']['close_button_color'] ?? '#64748b'),
            'close_button_aria_label' => sanitize_text_field($config['appearance']['close_button_aria_label'] ?? 'Close popup'),
            'vertical_align'          => in_array($config['appearance']['vertical_align'] ?? '', ['center', 'top', 'bottom'], true) ? $config['appearance']['vertical_align'] : 'center',
            'entrance_animation'      => in_array($config['appearance']['entrance_animation'] ?? '', ['fade_in', 'zoom_in', 'slide_down', 'slide_up'], true) ? $config['appearance']['entrance_animation'] : 'zoom_in',
            'exit_animation'          => in_array($config['appearance']['exit_animation'] ?? '', ['fade_out', 'zoom_out', 'slide_down', 'slide_up'], true) ? $config['appearance']['exit_animation'] : 'fade_out',
            'close_on_overlay_click'  => !empty($config['appearance']['close_on_overlay_click']),
            'lock_scroll'             => !empty($config['appearance']['lock_scroll']),
        ];

        // Lead Settings
        $clean['lead_settings'] = [
            'close_on_submit'        => !empty($config['lead_settings']['close_on_submit']),
            'close_delay_ms'         => absint($config['lead_settings']['close_delay_ms'] ?? 2500),
            'custom_success_message' => sanitize_text_field($config['lead_settings']['custom_success_message'] ?? 'Thank you! Your submission has been received.'),
            'redirect_url'           => esc_url_raw($config['lead_settings']['redirect_url'] ?? ''),
            'notification_email'     => sanitize_email($config['lead_settings']['notification_email'] ?? ''),
        ];

        // Scheduling (Pro)
        $clean['scheduling'] = [
            'enabled'    => !empty($config['scheduling']['enabled']),
            'start_date' => sanitize_text_field($config['scheduling']['start_date'] ?? ''),
            'end_date'   => sanitize_text_field($config['scheduling']['end_date'] ?? ''),
        ];

        // Pro settings pass-through / hooks
        $clean['pro_settings'] = isset($config['pro_settings']) && is_array($config['pro_settings']) ? $config['pro_settings'] : [];

        /**
         * Filter sanitized campaign config before saving.
         */
        return apply_filters('wpcal_popups_sanitize_campaign_config', $clean, $config);
    }
}

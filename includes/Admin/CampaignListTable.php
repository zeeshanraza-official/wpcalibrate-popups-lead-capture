<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Admin;

use WPCalibrate\Popups\Analytics\AnalyticsManager;
use WPCalibrate\Popups\Campaigns\CampaignModel;
use WPCalibrate\Popups\Campaigns\CampaignPostType;
use WPCalibrate\Popups\Campaigns\CampaignRepository;


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\WP_List_Table') && file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * Admin list table for viewing, filtering, duplicating, and managing popup campaigns.
 */
final class CampaignListTable extends \WP_List_Table
{
    private CampaignRepository $campaignRepo;
    private AnalyticsManager $analytics;

    public function __construct(CampaignRepository $campaignRepo, AnalyticsManager $analytics)
    {
        parent::__construct([
            'singular' => 'campaign',
            'plural'   => 'campaigns',
            'ajax'     => false,
        ]);

        $this->campaignRepo = $campaignRepo;
        $this->analytics    = $analytics;
    }

    public function get_columns(): array
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'title'       => __('Campaign Name', 'wpcalibrate-popups-lead-capture'),
            'state'       => __('Status', 'wpcalibrate-popups-lead-capture'),
            'trigger'     => __('Trigger Mode', 'wpcalibrate-popups-lead-capture'),
            'performance' => __('Performance (30 Days)', 'wpcalibrate-popups-lead-capture'),
            'date'        => __('Date', 'wpcalibrate-popups-lead-capture'),
        ];
    }

    public function get_sortable_columns(): array
    {
        return [
            'title' => ['title', false],
            'date'  => ['date', true],
        ];
    }

    public function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'state'       => $this->column_state($item),
            'trigger'     => $this->column_trigger($item),
            'performance' => $this->column_performance($item),
            'date'        => $this->column_date($item),
            default       => '',
        };
    }

    public function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="campaign_ids[]" value="%d" />',
            absint($item->getId())
        );
    }

    public function column_title(CampaignModel $item): string
    {
        $id      = $item->getId();
        $title   = esc_html($item->getTitle() ?: __('(No title)', 'wpcalibrate-popups-lead-capture'));
        $editUrl = get_edit_post_link($id);

        $duplicateUrl = wp_nonce_url(
            add_query_arg([
                'page'        => 'wpcal-popups',
                'action'      => 'duplicate',
                'campaign_id' => $id,
            ], admin_url('admin.php')),
            'wpcal_duplicate_campaign_' . $id
        );

        $trashUrl = wp_nonce_url(
            add_query_arg([
                'page'        => 'wpcal-popups',
                'action'      => 'trash',
                'campaign_id' => $id,
            ], admin_url('admin.php')),
            'wpcal_trash_campaign_' . $id
        );

        $previewUrl = add_query_arg([
            'wpcal_preview' => $id,
            '_wpnonce'      => wp_create_nonce('wpcal_preview_' . $id),
        ], home_url('/'));

        $actions = [
            'edit'      => sprintf('<a href="%s">%s</a>', esc_url($editUrl ?: '#'), esc_html__('Edit Design', 'wpcalibrate-popups-lead-capture')),
            'preview'   => sprintf('<a href="%s" target="_blank">%s</a>', esc_url($previewUrl), esc_html__('Live Preview', 'wpcalibrate-popups-lead-capture')),
            'duplicate' => sprintf('<a href="%s">%s</a>', esc_url($duplicateUrl), esc_html__('Duplicate', 'wpcalibrate-popups-lead-capture')),
            'trash'     => sprintf('<a href="%s" style="color: #a00;">%s</a>', esc_url($trashUrl), esc_html__('Trash', 'wpcalibrate-popups-lead-capture')),
        ];

        return sprintf(
            '<strong><a class="row-title" href="%s">%s</a></strong>%s',
            esc_url($editUrl ?: '#'),
            $title,
            $this->row_actions($actions)
        );
    }

    public function column_state(CampaignModel $item): string
    {
        $isEnabled = $item->isEnabled();
        $postStatus = $item->getStatus();

        $badge = $isEnabled
            ? '<span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">ENABLED</span>'
            : '<span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">DISABLED</span>';

        $statusText = ucfirst($postStatus);
        return sprintf('%s <br><span style="color: #64748b; font-size: 12px;">%s</span>', $badge, esc_html($statusText));
    }

    public function column_trigger(CampaignModel $item): string
    {
        $trig = $item->getTrigger();
        $type = $trig['type'] ?? 'time_delay';

        return match ($type) {
            'time_delay' => sprintf(__('Time Delay: %ds', 'wpcalibrate-popups-lead-capture'), round(($trig['time_delay_ms'] ?? 3000) / 1000)),
            'scroll'     => sprintf(__('Scroll Depth: %d%%', 'wpcalibrate-popups-lead-capture'), $trig['scroll_percentage'] ?? 50),
            'click'      => sprintf(__('Click: %s', 'wpcalibrate-popups-lead-capture'), esc_html($trig['click_selector'] ?? '')),
            'manual'     => __('Manual API', 'wpcalibrate-popups-lead-capture'),
            'exit_intent'=> __('Exit Intent (Pro)', 'wpcalibrate-popups-lead-capture'),
            'inactivity' => sprintf(__('Inactivity: %ds (Pro)', 'wpcalibrate-popups-lead-capture'), $trig['inactivity_seconds'] ?? 15),
            'page_views' => sprintf(__('Page Views: %d (Pro)', 'wpcalibrate-popups-lead-capture'), $trig['page_views_count'] ?? 3),
            default      => esc_html(ucwords(str_replace('_', ' ', $type))),
        };
    }

    public function column_performance(CampaignModel $item): string
    {
        $stats = $this->analytics->getSummary(30, $item->getId());
        return sprintf(
            '<span><strong>%s</strong> %s</span><br><span style="color: #64748b; font-size: 12px;"><strong>%s</strong> %s (%s%%)</span>',
            number_format_i18n($stats['impressions']),
            esc_html__('impressions', 'wpcalibrate-popups-lead-capture'),
            number_format_i18n($stats['conversions']),
            esc_html__('leads', 'wpcalibrate-popups-lead-capture'),
            esc_html((string) $stats['conversion_rate'])
        );
    }

    public function column_date(CampaignModel $item): string
    {
        $post = get_post($item->getId());
        return $post ? get_the_date('', $post) : '—';
    }

    protected function get_views(): array
    {
        $statusCounts = function_exists('wp_count_posts')
            ? wp_count_posts(CampaignPostType::POST_TYPE)
            : (object) ['publish' => 0, 'draft' => 0, 'trash' => 0];

        $currentStatus = sanitize_key($_GET['status'] ?? 'all');
        $baseUrl = admin_url('admin.php?page=wpcal-popups');

        $publishCount = (int) ($statusCounts->publish ?? 0);
        $draftCount   = (int) ($statusCounts->draft ?? 0);
        $trashCount   = (int) ($statusCounts->trash ?? 0);
        $allCount     = $publishCount + $draftCount;

        $views = [
            'all' => sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url($baseUrl),
                $currentStatus === 'all' ? 'current' : '',
                esc_html__('All', 'wpcalibrate-popups-lead-capture'),
                $allCount
            ),
        ];

        if ($publishCount > 0) {
            $views['publish'] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url(add_query_arg('status', 'publish', $baseUrl)),
                $currentStatus === 'publish' ? 'current' : '',
                esc_html__('Published', 'wpcalibrate-popups-lead-capture'),
                $publishCount
            );
        }

        if ($draftCount > 0) {
            $views['draft'] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url(add_query_arg('status', 'draft', $baseUrl)),
                $currentStatus === 'draft' ? 'current' : '',
                esc_html__('Drafts', 'wpcalibrate-popups-lead-capture'),
                $draftCount
            );
        }

        if ($trashCount > 0) {
            $views['trash'] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url(add_query_arg('status', 'trash', $baseUrl)),
                $currentStatus === 'trash' ? 'current' : '',
                esc_html__('Trash', 'wpcalibrate-popups-lead-capture'),
                $trashCount
            );
        }

        return $views;
    }

    protected function get_bulk_actions(): array
    {
        return [
            'bulk_trash' => __('Move to Trash', 'wpcalibrate-popups-lead-capture'),
        ];
    }

    public function prepare_items(): void
    {
        $perPage = 20;
        $currentPage = $this->get_pagenum();

        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'date';
        $order   = isset($_GET['order']) && strtoupper((string) $_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
        $search  = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $status  = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

        // Critical: Set column headers so WP_List_Table can render the table <thead>, <tbody>, and columns.
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable, 'title'];

        $query = $this->campaignRepo->queryForAdmin([
            'page'     => $currentPage,
            'per_page' => $perPage,
            'orderby'  => $orderby,
            'order'    => $order,
            'search'   => $search,
            'status'   => $status,
        ]);

        $this->items = $query['campaigns'];

        $this->set_pagination_args([
            'total_items' => $query['total'],
            'per_page'    => $perPage,
            'total_pages' => ceil($query['total'] / $perPage),
        ]);
    }
}


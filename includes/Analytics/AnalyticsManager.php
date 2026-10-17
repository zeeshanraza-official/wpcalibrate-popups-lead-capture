<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Analytics;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages atomic recording and aggregation of popup impressions and conversions.
 */
final class AnalyticsManager
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'wpcal_popup_stats_daily';
    }

    /**
     * Atomically increment impressions for the current day.
     */
    public function recordImpression(int $popupId): void
    {
        global $wpdb;

        if ($popupId <= 0) {
            return;
        }

        $today = current_time('Y-m-d');

        $sql = "INSERT INTO {$this->tableName} (popup_id, stat_date, impressions, conversions)
                VALUES (%d, %s, 1, 0)
                ON DUPLICATE KEY UPDATE impressions = impressions + 1";

        $wpdb->query($wpdb->prepare($sql, $popupId, $today)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Atomically increment conversions for the current day.
     */
    public function recordConversion(int $popupId): void
    {
        global $wpdb;

        if ($popupId <= 0) {
            return;
        }

        $today = current_time('Y-m-d');

        $sql = "INSERT INTO {$this->tableName} (popup_id, stat_date, impressions, conversions)
                VALUES (%d, %s, 0, 1)
                ON DUPLICATE KEY UPDATE conversions = conversions + 1";

        $wpdb->query($wpdb->prepare($sql, $popupId, $today)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get aggregate statistics over a given date range.
     *
     * @return array{impressions: int, conversions: int, conversion_rate: float, daily: array}
     */
    public function getSummary(int $days = 30, int $popupId = 0): array
    {
        global $wpdb;

        $startDate = gmdate('Y-m-d', strtotime("-{$days} days"));

        $where = ['stat_date >= %s'];
        $params = [$startDate];

        if ($popupId > 0) {
            $where[] = 'popup_id = %d';
            $params[] = $popupId;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT SUM(impressions) as total_impressions, SUM(conversions) as total_conversions
                FROM {$this->tableName} WHERE {$whereClause}";

        $totals = $wpdb->get_row($wpdb->prepare($sql, $params), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $impressions = (int) ($totals['total_impressions'] ?? 0);
        $conversions = (int) ($totals['total_conversions'] ?? 0);

        // Safe conversion rate calculation preventing divide-by-zero
        $conversionRate = $impressions > 0 ? round(($conversions / $impressions) * 100, 2) : 0.0;

        // Daily trend data
        $dailySql = "SELECT stat_date, SUM(impressions) as impressions, SUM(conversions) as conversions
                     FROM {$this->tableName} WHERE {$whereClause}
                     GROUP BY stat_date ORDER BY stat_date ASC";

        $dailyRows = $wpdb->get_results($wpdb->prepare($dailySql, $params), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return [
            'impressions'     => $impressions,
            'conversions'     => $conversions,
            'conversion_rate' => $conversionRate,
            'daily'           => is_array($dailyRows) ? $dailyRows : [],
        ];
    }

    /**
     * Retrieve top campaigns by conversions.
     *
     * @return array<array{popup_id: int, impressions: int, conversions: int, conversion_rate: float}>
     */
    public function getTopCampaigns(int $limit = 5): array
    {
        global $wpdb;

        $sql = "SELECT popup_id, SUM(impressions) as total_impressions, SUM(conversions) as total_conversions
                FROM {$this->tableName}
                GROUP BY popup_id
                ORDER BY total_conversions DESC
                LIMIT %d";

        $rows = $wpdb->get_results($wpdb->prepare($sql, max(1, $limit)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $imp = (int) ($row['total_impressions'] ?? 0);
                $conv = (int) ($row['total_conversions'] ?? 0);
                $rate = $imp > 0 ? round(($conv / $imp) * 100, 2) : 0.0;

                $results[] = [
                    'popup_id'        => (int) $row['popup_id'],
                    'impressions'     => $imp,
                    'conversions'     => $conv,
                    'conversion_rate' => $rate,
                ];
            }
        }

        return $results;
    }
}

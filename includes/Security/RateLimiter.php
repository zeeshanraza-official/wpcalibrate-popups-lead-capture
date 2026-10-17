<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lightweight, privacy-preserving rate limiter using hashed identifiers and transients.
 * Does not store raw IP addresses.
 */
final class RateLimiter
{
    private const MAX_REQUESTS = 6;
    private const WINDOW_SECONDS = 60;

    /**
     * Check if client exceeded request rate limit.
     *
     * @param string $action Action key.
     * @return bool True if allowed, false if limit exceeded.
     */
    public static function checkRateLimit(string $action = 'lead_submission'): bool
    {
        $identifier = self::getClientIdentifier();
        $transientKey = 'wpcal_rl_' . hash('sha256', $action . '_' . $identifier);

        $currentCount = (int) get_transient($transientKey);

        if ($currentCount >= self::MAX_REQUESTS) {
            return false;
        }

        set_transient($transientKey, $currentCount + 1, self::WINDOW_SECONDS);
        return true;
    }

    /**
     * Generate an anonymous hash identifier for rate limiting without storing raw IP.
     */
    private static function getClientIdentifier(): string
    {
        $rawIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $salt = wp_salt('nonce');

        return hash('sha256', $rawIp . $userAgent . $salt);
    }
}

<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scoped, privacy-safe logger for WPCalibrate Popups.
 * Strictly prevents logging sensitive information like credentials, license keys, or personal lead data.
 */
final class Logger
{
    private const SENSITIVE_KEYS = [
        'license_key',
        'license',
        'key',
        'token',
        'secret',
        'password',
        'email',
        'phone',
        'first_name',
        'last_name',
        'authorization',
    ];

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }

    private static function log(string $level, string $message, array $context): void
    {
        $sanitizedContext = self::sanitizeContext($context);
        $contextStr = !empty($sanitizedContext) ? ' ' . wp_json_encode($sanitizedContext) : '';
        $logEntry = sprintf('[WPCalibrate Popups][%s] %s%s', $level, $message, $contextStr);

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($logEntry); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        }

        /**
         * Action hook for custom logger subscribers.
         *
         * @param string $level Log level.
         * @param string $message Log message.
         * @param array $sanitizedContext Sanitized context.
         */
        do_action('wpcal_popups_log_entry', $level, $message, $sanitizedContext);
    }

    private static function sanitizeContext(array $context): array
    {
        $clean = [];
        foreach ($context as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $clean[$key] = '***REDACTED***';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = self::sanitizeContext($value);
            } elseif (is_scalar($value) || null === $value) {
                $clean[$key] = $value;
            } else {
                $clean[$key] = gettype($value);
            }
        }
        return $clean;
    }
}

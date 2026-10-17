<?php

declare(strict_types=1);

namespace WPCalibrate\Popups;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PSR-4 Autoloader for WPCalibrate Popups Free Core.
 */
final class Autoloader
{
    private const NAMESPACE_PREFIX = 'WPCalibrate\\Popups\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $className): void
    {
        if (!str_starts_with($className, self::NAMESPACE_PREFIX)) {
            return;
        }

        $relativeClass = substr($className, strlen(self::NAMESPACE_PREFIX));
        $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}

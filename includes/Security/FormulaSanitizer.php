<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Formula Sanitizer to prevent CSV / Spreadsheet formula injection vulnerabilities.
 * Disarms values starting with '=', '+', '-', '@', '\t', '\r', '%'.
 */
final class FormulaSanitizer
{
    private const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r", '%'];

    /**
     * Neutralize dangerous formula injection prefixes from scalar strings.
     */
    public static function sanitizeValue(mixed $value): string
    {
        if (!is_scalar($value) && null !== $value) {
            return '';
        }

        $str = (string) $value;
        if ($str === '') {
            return '';
        }

        $firstChar = substr($str, 0, 1);
        if (in_array($firstChar, self::DANGEROUS_PREFIXES, true)) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Sanitize an entire row array for safe CSV export.
     *
     * @param array<mixed> $row
     * @return array<string>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitizeValue'], $row);
    }
}

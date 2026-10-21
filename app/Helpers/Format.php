<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTime;

class Format
{
    public static function date(?string $dateStr, string $format = 'd/m/Y'): string
    {
        if (empty($dateStr)) {
            return '-';
        }
        try {
            $dt = new DateTime($dateStr);
            return $dt->format($format);
        } catch (\Throwable) {
            return $dateStr;
        }
    }

    public static function dateTime(?string $dateStr, string $format = 'd/m/Y H:i'): string
    {
        return self::date($dateStr, $format);
    }

    public static function duration(int $seconds): string
    {
        if ($seconds < 60) {
            return sprintf("0:%02d", $seconds);
        }
        $minutes = floor($seconds / 60);
        $remSec = $seconds % 60;
        if ($minutes < 60) {
            return sprintf("%d:%02d min", $minutes, $remSec);
        }
        $hours = floor($minutes / 60);
        $remMin = $minutes % 60;
        return sprintf("%dh %02dm", $hours, $remMin);
    }

    /**
     * Converte representações de duração para segundos.
     * Suporta:
     * - Formato Google Drive / Mídia: '00:11', '05:30', '01:15:20'
     * - Segundos diretos: 11, '11', 300, '300'
     * - Texto descritivo: '11s', '5 min 30 s', '1h 20m'
     */
    public static function parseDuration(mixed $input): int
    {
        if ($input === null || $input === '') {
            return 0;
        }

        if (is_int($input)) {
            return max(0, $input);
        }

        if (is_float($input)) {
            return max(0, (int)round($input));
        }

        $str = trim((string)$input);
        if ($str === '') {
            return 0;
        }

        // Se for puramente numérico (ex: "11", "300")
        if (ctype_digit($str)) {
            return (int)$str;
        }

        // Formato HH:MM:SS (ex: "01:15:20")
        if (preg_match('/^(\d+):(\d{1,2}):(\d{1,2})$/', $str, $matches)) {
            return ((int)$matches[1] * 3600) + ((int)$matches[2] * 60) + (int)$matches[3];
        }

        // Formato MM:SS (ex: "00:11", "05:30", "5:30")
        if (preg_match('/^(\d+):(\d{1,2})$/', $str, $matches)) {
            return ((int)$matches[1] * 60) + (int)$matches[2];
        }

        // Formatos por extenso (ex: "1h 30m", "5 min 30 s", "11s")
        $total = 0;
        $found = false;
        if (preg_match('/(\d+)\s*h(?:oras?)?/i', $str, $matches)) {
            $total += (int)$matches[1] * 3600;
            $found = true;
        }
        if (preg_match('/(\d+)\s*m(?:in(?:utos?)?)?/i', $str, $matches)) {
            $total += (int)$matches[1] * 60;
            $found = true;
        }
        if (preg_match('/(\d+)\s*s(?:eg(?:undos?)?)?/i', $str, $matches)) {
            $total += (int)$matches[1];
            $found = true;
        }

        if ($found) {
            return $total;
        }

        return max(0, (int)floatval($str));
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        if (empty($words)) return 'DC';
        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }
        return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
    }
}

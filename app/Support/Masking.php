<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

class Masking
{
    public static function nik($value): string
    {
        return self::forViewer($value, 'nik');
    }

    public static function email($value): string
    {
        return self::forViewer($value, 'email');
    }

    public static function phone($value): string
    {
        return self::forViewer($value, 'phone');
    }

    public static function text($value): string
    {
        return self::forViewer($value, 'text');
    }

    public static function date($value): string
    {
        return self::forViewer($value, 'date');
    }

    public static function forViewer($value, string $type = 'text'): string
    {
        $stringValue = is_scalar($value) ? (string) $value : '';

        if ($stringValue === '') {
            return '-';
        }

        $user = auth()->user();
        if (!$user instanceof User || !$user->hasRole('viewer')) {
            return $stringValue;
        }

        if ($type === 'email') {
            return self::maskEmail($stringValue);
        }

        if ($type === 'nik' || $type === 'phone') {
            return self::maskPartial($stringValue, 4, 2);
        }

        if ($type === 'date') {
            // Tampilkan tahun saja, sembunyikan bulan & hari
            return self::maskPartial($stringValue, 4, 0);
        }

        return self::maskPartial($stringValue, 2, 1);
    }

    private static function maskEmail(string $value): string
    {
        $parts = explode('@', $value, 2);
        if (count($parts) !== 2) {
            return self::maskPartial($value, 2, 2);
        }

        $local = self::maskPartial($parts[0], 2, 1);

        return $local . '@' . $parts[1];
    }

    private static function maskPartial(string $value, int $prefix, int $suffix): string
    {
        $length = strlen($value);
        if ($length <= ($prefix + $suffix)) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $prefix)
            . str_repeat('*', $length - $prefix - $suffix)
            . substr($value, -$suffix);
    }
}

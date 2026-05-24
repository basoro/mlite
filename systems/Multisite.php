<?php

namespace Systems;

class Multisite
{
    public static function enabled(): bool
    {
        return strtolower((string) \env('MULTISITE_ENABLE', '')) === 'true';
    }

    public static function baseDomain(): string
    {
        return strtolower(trim((string) \env('MULTISITE_DOMAIN', '')));
    }

    public static function host(): string
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host);
        return (string) $host;
    }

    public static function subdomain(): string
    {
        if (!self::enabled()) {
            return '';
        }
        $base = self::baseDomain();
        if ($base === '') {
            return '';
        }
        $host = self::host();
        if ($host === '' || $host === $base || $host === 'www.' . $base) {
            return '';
        }
        $suffix = '.' . $base;
        if (!str_ends_with($host, $suffix)) {
            return '';
        }
        $sub = substr($host, 0, -strlen($suffix));
        if ($sub === '' || strpos($sub, '.') !== false) {
            return '';
        }
        if (!preg_match('/^[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', $sub)) {
            return '';
        }
        $reserved = array_filter(array_map('trim', explode(',', (string) \env('MULTISITE_RESERVED_SUBDOMAINS', 'www,admin,api,static,assets,cdn,mail'))));
        if (in_array($sub, $reserved, true)) {
            return '';
        }
        return $sub;
    }

    public static function isPlatformHost(): bool
    {
        if (!self::enabled()) {
            return false;
        }
        $base = self::baseDomain();
        if ($base === '') {
            return false;
        }
        $host = self::host();
        return $host === $base || $host === 'www.' . $base;
    }

    public static function tenantDbName(string $defaultDbName): string
    {
        $sub = self::subdomain();
        if ($sub === '') {
            return $defaultDbName;
        }
        return $sub . '_' . $defaultDbName;
    }
}


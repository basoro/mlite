<?php

namespace Systems;

class Multisite
{
    public static function enabled(): bool
    {
        return strtolower((string) \env('MULTISITE_ENABLE', '')) === 'true';
    }

    public static function baseDomains(): array
    {
        $raw = strtolower(trim((string) \env('MULTISITE_DOMAIN', '')));
        if ($raw === '') {
            return [];
        }
        $parts = array_filter(array_map('trim', preg_split('/[,\s]+/', $raw)));
        $domains = [];
        foreach ($parts as $d) {
            if ($d !== '') {
                $domains[] = $d;
            }
        }
        return array_values(array_unique($domains));
    }

    public static function baseDomain(): string
    {
        $domains = self::baseDomains();
        return $domains[0] ?? '';
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
        $host = self::host();
        if ($host === '') {
            return '';
        }

        $base = self::matchedBaseDomain();
        if ($base === '') {
            return '';
        }
        if ($host === $base || $host === 'www.' . $base) {
            return '';
        }
        $suffix = '.' . $base;
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

    public static function matchedBaseDomain(): string
    {
        if (!self::enabled()) {
            return '';
        }
        $host = self::host();
        foreach (self::baseDomains() as $base) {
            if ($base === '') {
                continue;
            }
            if ($host === $base || $host === 'www.' . $base) {
                return $base;
            }
            $suffix = '.' . $base;
            if (str_ends_with($host, $suffix)) {
                return $base;
            }
        }
        return '';
    }

    public static function isPlatformHost(): bool
    {
        if (!self::enabled()) {
            return false;
        }
        $host = self::host();
        foreach (self::baseDomains() as $base) {
            if ($base === '') {
                continue;
            }
            if ($host === $base || $host === 'www.' . $base) {
                return true;
            }
        }
        return false;
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

<?php
// Centralized cache helper for admin/CRM and other cached endpoints.
// Provides small helpers to invalidate cache files and Redis keys in one place.

/**
 * Minimal, local stub for the Redis class so static analyzers (and
 * environments without the redis extension) won't produce "undefined type"
 * errors; the real Redis extension will not be overridden because this
 * declaration is guarded by class_exists().
 *
 * Methods implemented here are intentionally minimal/no-op because the
 * rest of this file treats Redis failures as non-fatal.
 */
if (!class_exists('Redis')) {
    class Redis
    {
        public function connect(string $host, int $port = 6379, float $timeout = 0.0): bool
        {
            return false;
        }

        public function del($key): int
        {
            return 0;
        }
    }
}

/**
 * Invalidate a cache file and optionally a Redis key.
 * Safe to call even when Redis or the file are missing.
 *
 * @param string $cacheKey Redis key to delete (optional)
 * @param string|null $filePath File path to unlink (optional)
 */
function invalidate_cache(string $cacheKey = null, string $filePath = null): void
{
    if (!empty($filePath)) {
        @unlink($filePath);
    }

    if (!empty($cacheKey) && class_exists('Redis')) {
        try {
            $r = new Redis();
            @$r->connect('127.0.0.1', 6379, 1);
            @$r->del($cacheKey);
        } catch (Exception $e) {
            // ignore Redis failures
        }
    }
}

/**
 * Convenience wrapper for invalidating the CRM dashboard cache.
 */
function invalidate_crm_cache(): void
{
    $cacheFile = __DIR__ . '/../cache/crm_dashboard.json';
    $cacheKey = 'crm_dashboard_v1';
    invalidate_cache($cacheKey, $cacheFile);
}

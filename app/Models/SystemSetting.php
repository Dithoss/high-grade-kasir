<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasUuids;
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'string',
    ];

    // Cache TTL dalam detik (1 jam)
    const CACHE_TTL = 3600;
    const CACHE_KEY = 'system_settings_all';

    /**
     * Ambil nilai setting berdasarkan key.
     * Jika tidak ada, kembalikan default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllCached();
        return $settings[$key] ?? $default;
    }

    /**
     * Set nilai setting, update cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::clearCache();
    }

    /**
     * Ambil semua settings dari cache.
     */
    public static function getAllCached(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Hapus cache (dipanggil setelah update).
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    // ─── Helpers Typed ────────────────────────────────────────────────────────

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default ? '1' : '0');
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) static::get($key, $default);
    }
}
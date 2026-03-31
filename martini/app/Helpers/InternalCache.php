<?php
namespace App\Helpers;

use App\Models\Cache;
use Carbon\Carbon;

class InternalCache
{
    public static function put($key, $value, int|Carbon|null $expiration)
    {
        self::cleanExpired();
        if ($expiration === null) {
            $expiration = Carbon::now()->addHours(1);
        } else
        if (is_int($expiration)) {
            $expiration = Carbon::now()->addSeconds($expiration);
        }
        $c = Cache::where('key', $key)->firstOrNew();
        $c->key = $key;
        $c->value = json_encode($value);
        $c->expiration = $expiration;
        $c->save();
    }
    public static function get($key)
    {
        self::cleanExpired();
        if (self::has($key)) {
            $cacheEntry = Cache::where('key', $key)->first();
            return json_decode($cacheEntry->value, true);
        }
        return null;
    }
    public static function has($key)
    {
        self::cleanExpired();
        $cacheEntry = Cache::where('key', $key)->first();
        $ret = $cacheEntry && (!$cacheEntry->expiration || $cacheEntry->expiration > now());
        if (!$ret) {
            self::forget($key);
        }
        return $ret;
    }
    public static function forget($key)
    {
        self::cleanExpired();
        Cache::where('key', $key)->delete();
    }
    private static $_hasRunCleanExpired = false;
    private static function cleanExpired()
    {
        if (self::$_hasRunCleanExpired) {
            return;
        }
        Cache::where('expiration', '<', now())->delete();
        self::$_hasRunCleanExpired = true;
    }
}
?>

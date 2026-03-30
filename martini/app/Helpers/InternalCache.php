<?php
namespace App\Helpers;

use App\Models\Cache;
use Carbon\Carbon;

class InternalCache
{
    public static function put($key, $value, int|Carbon|null $expiration)
    {
        if ($expiration === null) {
            $expiration = Carbon::now()->addHours(1);
        } else
        if (is_int($expiration)) {
            $expiration = Carbon::now()->addSeconds($expiration);
        }
        $c = Cache::where('key', $key)->firstOrNew();
        $c->key = $key;
        $c->value = serialize($value);
        $c->expiration = $expiration;
        $c->save();
    }
    public static function get($key)
    {
        if (self::has($key)) {
            $cacheEntry = Cache::where('key', $key)->first();
            return unserialize($cacheEntry->value);
        }
        return null;
    }
    public static function has($key)
    {
        $cacheEntry = Cache::where('key', $key)->first();
        $ret = $cacheEntry && (!$cacheEntry->expiration || $cacheEntry->expiration > now());
        if (!$ret) {
            self::forget($key);
        }
        return $ret;
    }
    public static function forget($key)
    {
        Cache::where('key', $key)->delete();
    }
}
?>

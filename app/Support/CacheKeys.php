<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class CacheKeys
{
    // versioned “namespaces” so we can cheaply invalidate groups on file cache
    public static function version(string $name): int
    {
        $key = "ver:{$name}";
        if (! Cache::has($key)) {
            Cache::forever($key, 1);
        }
        return (int) Cache::get($key, 1);
    }

    public static function bump(string $name): void
    {
        $key = "ver:{$name}";
        if (! Cache::has($key)) {
            Cache::forever($key, 1);
        } else {
            Cache::forever($key, (int) Cache::get($key, 1) + 1);
        }
    }

    public static function courses(string $suffix = ''): string
    {
        return 'courses:v' . self::version('courses') . ":{$suffix}";
    }

    public static function lessons(string $suffix = ''): string
    {
        return 'lessons:v' . self::version('lessons') . ":{$suffix}";
    }
}

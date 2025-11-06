<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\{Course, Lesson, Comment};

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Whenever a course, lesson, or comment changes, clear dashboard caches
        $flushDashboard = fn() => Cache::flush();

        Course::saved($flushDashboard);
        Course::deleted($flushDashboard);

        Lesson::saved($flushDashboard);
        Lesson::deleted($flushDashboard);

        Comment::saved($flushDashboard);
        Comment::deleted($flushDashboard);
    }
}

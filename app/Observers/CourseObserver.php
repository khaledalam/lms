<?php

namespace App\Observers;

use App\Models\Course;
use Illuminate\Support\Str;
use App\Support\CacheKeys;

class CourseObserver
{
    /**
     * Handle the Course "creating" event (before saving).
     */
    public function creating(Course $course): void
    {
        if (empty($course->slug)) {
            $base = Str::slug($course->title);
            $slug = $base;
            $i = 1;
            while (Course::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $course->slug = $slug;
        }
    }

    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        CacheKeys::bump('courses');
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        CacheKeys::bump('courses');
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        CacheKeys::bump('courses');
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        CacheKeys::bump('courses');
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        CacheKeys::bump('courses');
    }
}

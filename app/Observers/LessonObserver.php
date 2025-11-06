<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Support\CacheKeys;

class LessonObserver
{
    public function created(Lesson $lesson): void
    {
        CacheKeys::bump('lessons');
    }

    public function updated(Lesson $lesson): void
    {
        CacheKeys::bump('lessons');
    }

    public function deleted(Lesson $lesson): void
    {
        CacheKeys::bump('lessons');
    }

    public function restored(Lesson $lesson): void
    {
        CacheKeys::bump('lessons');
    }
    
    public function forceDeleted(Lesson $lesson): void
    {
        CacheKeys::bump('lessons');
    }
}

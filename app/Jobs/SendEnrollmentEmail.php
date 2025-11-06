<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Notifications\EnrollmentConfirmed;

class SendEnrollmentEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $student, public Course $course) {}

    public function handle(): void
    {
        $user   = User::findOrFail($this->student->id);
        $course = Course::findOrFail($this->course->id);

        $user->notify(new EnrollmentConfirmed($course));
    }

    public function tags(): array
    {
        return [
            'enrollment',
            'course:' . $this->course->id,
            'user:' . $this->student->id,
        ];
    }
}

<?php

namespace App\Listeners;

use App\Events\NewCommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\NewCommentMail;
use Illuminate\Support\Facades\Mail;

class SendCommentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewCommentCreated $event): void
    {
        $lesson = $event->comment->lesson;
        $instructor = $lesson->course->instructor;

        Mail::to($instructor->email)->queue(
            new NewCommentMail($event->comment)
        );
    }
}

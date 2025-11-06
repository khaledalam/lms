<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\Comment;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment->load('user', 'lesson.course');
    }

    public function build()
    {
        return $this->subject('New Comment on Your Course')
            ->markdown('emails.comments.new');
    }
}

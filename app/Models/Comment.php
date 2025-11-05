<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Comment model representing comments left by users on lessons.
 *
 * Stores the relationship to the lesson being commented on and the user
 * who authored the comment.
 */
class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['lesson_id', 'user_id', 'body'];
    
    /**
     * Get the lesson that this comment belongs to.
     *
     * Defines a BelongsTo relationship to the Lesson model. Use this to
     * eager load the parent lesson or to access lesson attributes
     * from a Comment instance.
     *
     * Example:
     * $comment->lesson->title
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lesson()
    {
        // Returns the parent Lesson model instance for this comment.
        // Useful when retrieving the lesson or when eager loading:
        // Comment::with('lesson')->get()
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the user that authored the comment.
     *
     * Defines a BelongsTo relationship to the User model. Use this to
     * retrieve the comment author or to eager load author data.
     *
     * Example:
     * $comment->user->name
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        // Returns the User model instance that authored this comment.
        // Can be used to check author attributes or permissions.
        return $this->belongsTo(User::class);
    }
}

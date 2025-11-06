<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Lesson model representing a lesson within a course.
 *
 * Contains relations to the parent course and any comments left on the lesson.
 */
class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['course_id', 'order', 'title', 'content'];

    /**
     * Get the course that this lesson belongs to.
     *
     * Defines a BelongsTo relationship to the Course model. Use this to
     * access the parent course or to eager load course data.
     *
     * Example:
     * $lesson->course->title
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        // Return the parent Course model instance for this lesson.
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the comments for the lesson.
     *
     * Defines a HasMany relationship to the Comment model. Use this to
     * retrieve all comments left on the lesson, optionally eager loaded.
     *
     * Example:
     * $lesson->comments()->create([...]);
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        // Return related Comment models for this lesson.
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');  
    }

    /** 
     * Check if Lesson has Attachment or not
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }
}

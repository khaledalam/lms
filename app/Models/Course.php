<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Course model representing a course in the LMS.
 *
 * Contains relations to instructor, lessons and enrolled students,
 * and a query scope for filtering published courses.
 */
class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['title', 'slug', 'description', 'published', 'instructor_id'];

    /**
     * Get the instructor (User) that owns the course.
     *
     * This defines a BelongsTo relationship to the User model using the
     * instructor_id foreign key on the courses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instructor()
    {
        // Return the User model representing the instructor of this course.
        // Example: $course->instructor->name
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get all lessons for the course ordered by their 'order' column.
     *
     * Defines a HasMany relationship to Lesson and ensures returned lessons
     * are ordered by the 'order' column so lesson sequencing is preserved.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lessons()
    {
        // Return related Lesson models ordered by their sequence/order.
        // Useful for rendering lessons in the intended order:
        // $course->lessons->each(...)
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * The users enrolled in the course.
     *
     * Defines a many-to-many relationship between Course and User.
     * withTimestamps() ensures pivot timestamps are maintained.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        // Return the many-to-many relation to User (enrolled students).
        // Example: $course->students()->attach($userId)
        return $this->belongsToMany(User::class)->withTimestamps();
    }


    /**
     * Scope a query to only include published courses.
     *
     * Usage: Course::published()->get();
     *
     * @param \Illuminate\Database\Eloquent\Builder $q
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($q)
    {
        // Constrain the query to courses where the 'published' flag is true.
        // Example: Course::published()->where('title', 'like', '%Laravel%')->get();
        return $q->where('published', true);
    }
}

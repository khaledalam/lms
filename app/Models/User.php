<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Application User model.
 *
 * Represents both instructors and students in the LMS and provides
 * relations to courses and comments along with role helper methods.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get all courses taught by the instructor.
     *
     * Defines a one-to-many relationship where the foreign key on the
     * courses table is 'instructor_id'. Use this to retrieve or eager-load
     * courses an instructor teaches.
     *
     * Example:
     * $user->coursesTaught()->where('published', true)->get();
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coursesTaught()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Get all courses the student is enrolled in.
     *
     * Defines a many-to-many relationship between users and courses.
     * withTimestamps() ensures pivot created_at/updated_at are maintained.
     *
     * Example:
     * $user->coursesEnrolled()->attach($courseId);
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coursesEnrolled()
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    /**
     * Get all comments made by the user.
     *
     * Defines a one-to-many relationship to the Comment model. Use this
     * to retrieve a user's comments or to eager-load comment data.
     *
     * Example:
     * $user->comments()->latest()->get();
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Check if the user has the instructor role.
     *
     * Compares the user's role attribute with the INSTRUCTOR enum value.
     * Useful for authorization checks and UI conditional logic.
     *
     * Example:
     * if ($user->isInstructor()) { ... }
     *
     * @return bool
     */
    public function isInstructor(): bool
    {
        return $this->role === UserRoles::INSTRUCTOR;
    }

    /**
     * Check if the user has the student role.
     *
     * Compares the user's role attribute with the STUDENT enum value.
     * Useful for authorization checks and UI conditional logic.
     *
     * Example:
     * if ($user->isStudent()) { ... }
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->role === UserRoles::STUDENT;
    }

    /**
     * Get the attributes that should be cast.
     *
     * Returns the model casts array used by Eloquent to cast attributes
     * to specific types (e.g. datetime, hashed password).
     *
     * Note: If your application uses the $casts property instead of a method,
     * keep this consistent with the rest of the codebase.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

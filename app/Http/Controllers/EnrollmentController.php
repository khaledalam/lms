<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoles;

class EnrollmentController extends Controller
{
    /**
     * Enroll the authenticated user into a course.
     * Rules:
     *  - Only students may enroll.
     *  - Prevent duplicate enrollments.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // Must be authenticated
        if (! $user) {
            abort(401, 'Unauthorized');
        }

        // Instructors cannot enroll as students
        if ($user->role !== UserRoles::STUDENT) {
            return back()->with('error', 'Only students can enroll in courses.');
        }

        // Prevent enrolling in unpublished courses (optional but sensible)
        if (! $course->published) {
            return back()->with('error', 'This course is not available for enrollment yet.');
        }

        // Already enrolled?
        if ($course->students()->where('users.id', $user->id)->exists()) {
            return back()->with('info', 'You are already enrolled in this course.');
        }

        // Enroll (pivot attach; syncWithoutDetaching is also fine)
        $course->students()->attach($user->id);

        return back()->with('success', 'Enrolled successfully!');
    }

    /**
     * Show the list of students to the instructor who owns the course.
     */
    public function index(Course $course)
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        // Only the course instructor can view the roster
        if ((int) $course->instructor_id !== (int) $user->id) {
            abort(403, 'You are not allowed to view this roster.');
        }

        $students = $course->students()
            ->withPivot('created_at')
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->simplePaginate(20);

        return view('courses.students', compact('course', 'students'));
    }
}

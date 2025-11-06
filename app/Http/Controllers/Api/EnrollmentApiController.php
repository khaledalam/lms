<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Return enrolled courses for students; instructors see their taught courses too
        return response()->json([
            'enrolled'   => $user->coursesEnrolled()->select('courses.id', 'title', 'slug', 'published')->get(),
            'instructed' => $user->coursesTaught()->select('id', 'title', 'slug', 'published')->get(),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // Must be authenticated (already ensured by auth:sanctum)
        // Basic business rules (adjust to your enums if any)
        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can enroll in courses.'
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $course->published) {
            return response()->json([
                'message' => 'This course is not available for enrollment yet.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Already enrolled?
        $already = $course->students()->whereKey($user->id)->exists();
        if ($already) {
            return response()->json([
                'message' => 'You are already enrolled in this course.'
            ], Response::HTTP_OK);
        }

        $course->students()->attach($user->id);

        return response()->json([
            'message' => 'Enrolled successfully.',
            'course'  => [
                'id'    => $course->id,
                'title' => $course->title,
                'slug'  => $course->slug,
            ],
        ], Response::HTTP_CREATED);
    }
}

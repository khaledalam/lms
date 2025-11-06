<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}

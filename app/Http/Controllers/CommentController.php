<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Only enrolled students or the instructor may comment
        $isInstructor = $lesson->course->instructor_id === $user->id;
        $isEnrolled = $lesson->course->students()->where('users.id', $user->id)->exists();

        if (! ($isInstructor || $isEnrolled)) {
            abort(403);
        }

        $data = $request->validate(['body' => 'required|string|max:2000']);
        $lesson->comments()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, Lesson $lesson)
    {
        $user = Auth::user();

        $isInstructor = $lesson->course->instructor_id === $user->id;
        $isEnrolled = $lesson->course
            ->students()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isInstructor || $isEnrolled, 403);

        $data = $request->validated();

        $lesson->comments()->create([
            'user_id' => $user->id,
            'body'    => $data['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    /**
     * GET /me/comments
     * View comments made by the authenticated user.
     */
    public function myComments()
    {
        $user = Auth::user();

        $comments = Comment::with([
            'lesson:id,course_id,title',
            'lesson.course:id,title'
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return view('comments.my', compact('comments'));
    }
}

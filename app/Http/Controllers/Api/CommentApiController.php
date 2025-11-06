<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentApiController extends Controller
{
    /**
     * GET /api/lessons/{lesson}/comments?per_page=20
     * Visible to course instructor or enrolled students.
     */
    public function index(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        abort_unless($this->canParticipate($user, $lesson), 403);

        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        $comments = $lesson->comments()
            ->with(['user:id,name'])
            ->latest()
            ->paginate($perPage, ['id', 'user_id', 'lesson_id', 'body', 'created_at']);

        // Normalize shape
        $comments->getCollection()->transform(fn($c) => $this->serialize($c));

        return response()->json($comments);
    }

    /**
     * POST /api/lessons/{lesson}/comments
     * Body: { body: string }
     * Allowed to instructor owner or enrolled students.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        abort_unless($this->canParticipate($user, $lesson), 403);

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = Comment::create([
            'lesson_id' => $lesson->id,
            'user_id'   => $user->id,
            'body'      => $data['body'],
        ])->loadMissing('user:id,name');

        return response()->json($this->serialize($comment), 201);
    }

    /**
     * GET /api/comments/{comment}
     * Visible if instructor owner or enrolled in the comment's course.
     */
    public function show(Comment $comment)
    {
        $user = Auth::user();
        abort_unless($this->canParticipate($user, $comment->lesson), 403);

        $comment->loadMissing('user:id,name');

        return response()->json($this->serialize($comment));
    }

    /**
     * PATCH /api/comments/{comment}
     * Body: { body?: string }
     * Allowed to comment author or the course instructor.
     */
    public function update(Request $request, Comment $comment)
    {
        $user = Auth::user();
        abort_unless($this->canModerate($user, $comment), 403);

        $data = $request->validate([
            'body' => 'sometimes|required|string|max:2000',
        ]);

        if (array_key_exists('body', $data)) {
            $comment->body = $data['body'];
            $comment->save();
        }

        $comment->loadMissing('user:id,name');

        return response()->json($this->serialize($comment));
    }

    /**
     * DELETE /api/comments/{comment}
     * Allowed to comment author or the course instructor.
     */
    public function destroy(Comment $comment)
    {
        $user = Auth::user();
        abort_unless($this->canModerate($user, $comment), 403);

        $comment->delete();

        return response()->noContent();
    }

    /* ------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------ */

    private function canParticipate($user, Lesson $lesson): bool
    {
        if (!$user) return false;

        $course = $lesson->course;
        // Instructor owner?
        if ($user->id === $course->instructor_id) return true;

        // Enrolled student?
        return $course->students()
            ->where('users.id', $user->id)
            ->exists();
    }

    private function canModerate($user, Comment $comment): bool
    {
        if (!$user) return false;

        // Author can edit/delete own comment
        if ($comment->user_id === $user->id) return true;

        // Course instructor can moderate
        return $comment->lesson->course->instructor_id === $user->id;
    }

    private function serialize(Comment $c): array
    {
        return [
            'id'         => $c->id,
            'lesson_id'  => $c->lesson_id,
            'user'       => [
                'id'   => $c->user->id ?? $c->user_id,
                'name' => $c->user->name ?? null,
            ],
            'body'       => $c->body,
            'created_at' => $c->created_at,
        ];
    }
}

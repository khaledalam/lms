<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonApiController extends Controller
{
    /**
     * GET /api/courses/{course}/lessons?sort=order|created_at|attachment&direction=asc|desc
     * Default: sort=order&direction=asc
     */
    public function index(Request $request, Course $course)
    {
        // Only instructor owner or enrolled students can view lessons of a course
        $this->authorize('view', $course);

        $sort = $request->query('sort', 'order');
        $dir  = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $q = $course->lessons()->select(['id', 'course_id', 'order', 'title', 'content', 'attachment_path', 'created_at']);

        if ($sort === 'created_at') {
            $q->orderBy('created_at', $dir);
        } elseif ($sort === 'attachment') {
            // Sort by "has attachment": non-null first/last, then order
            // asc  -> no attachment first, desc -> with attachment first
            $q->orderByRaw('attachment_path IS NULL ' . ($dir === 'asc' ? 'ASC' : 'DESC'))
                ->orderBy('order', 'asc');
        } else {
            // default by order
            $q->orderBy('order', $dir);
        }

        $lessons = $q->get()->map(fn($l) => $this->serializeLesson($l));

        return response()->json($lessons);
    }

    /**
     * POST /api/courses/{course}/lessons
     * Body (multipart/form-data): title, content?, attachment?
     * Only instructor (owner) can create.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title'      => 'required|string|max:150',
            'content'    => 'nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $nextOrder = (int) $course->lessons()->max('order') + 1;

        $lesson = $course->lessons()->create([
            'title'   => $data['title'],
            'content' => $data['content'] ?? null,
            'order'   => $nextOrder,
        ]);

        if ($request->hasFile('attachment')) {
            $lesson->attachment_path = Storage::putFile('lesson-attachments', $request->file('attachment'));
            $lesson->save();
        }

        return response()->json($this->serializeLesson($lesson), 201);
    }

    /**
     * GET /api/lessons/{lesson}
     */
    public function show(Lesson $lesson)
    {
        $this->authorize('view', $lesson); // LessonPolicy@view or CoursePolicy via controller elsewhere
        return response()->json($this->serializeLesson($lesson));
    }

    /**
     * PATCH /api/lessons/{lesson}
     * Body (multipart/form-data or JSON):
     *   title?, content?, order?, attachment? (file), remove_attachment? (bool)
     * Only instructor owner can update.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $course = $lesson->course;
        $this->authorize('update', $course);

        $data = $request->validate([
            'title'             => 'sometimes|required|string|max:150',
            'content'           => 'sometimes|nullable|string',
            'order'             => 'sometimes|integer|min:1',
            'attachment'        => 'sometimes|nullable|file|max:10240',
            'remove_attachment' => 'sometimes|boolean',
        ]);

        if (array_key_exists('title', $data)) {
            $lesson->title = $data['title'];
        }
        if (array_key_exists('content', $data)) {
            $lesson->content = $data['content'];
        }

        // Reorder by swapping with any lesson at target order
        if (array_key_exists('order', $data) && $data['order'] !== $lesson->order) {
            $targetOrder = (int) $data['order'];
            $other = $course->lessons()->where('order', $targetOrder)->first();
            if ($other) {
                $other->order = $lesson->order;
                $other->save();
            }
            $lesson->order = $targetOrder;
        }

        // Remove existing attachment if requested
        if ($request->boolean('remove_attachment') && $lesson->attachment_path) {
            Storage::delete($lesson->attachment_path);
            $lesson->attachment_path = null;
        }

        // Replace / set new attachment if uploaded
        if ($request->hasFile('attachment')) {
            if ($lesson->attachment_path) {
                Storage::delete($lesson->attachment_path);
            }
            $lesson->attachment_path = Storage::putFile('lesson-attachments', $request->file('attachment'));
        }

        $lesson->save();

        return response()->json($this->serializeLesson($lesson));
    }

    /**
     * DELETE /api/lessons/{lesson}
     * Only instructor owner can delete.
     */
    public function destroy(Lesson $lesson)
    {
        $course = $lesson->course;
        $this->authorize('delete', $course);

        $deletedOrder = $lesson->order;

        if ($lesson->attachment_path) {
            Storage::delete($lesson->attachment_path);
        }

        $lesson->delete();

        // Compact orders after deletion
        $course->lessons()->where('order', '>', $deletedOrder)->decrement('order');

        return response()->noContent();
    }

    /**
     * GET /api/lessons/{lesson}/attachment
     * Protected download (instructor owner or enrolled student).
     */
    public function attachment(Lesson $lesson)
    {
        $this->authorize('view', $lesson);
        if (!$lesson->attachment_path) {
            abort(404);
        }

        $ext = pathinfo($lesson->attachment_path, PATHINFO_EXTENSION);
        $name = \Illuminate\Support\Str::slug($lesson->title ?: 'lesson') . ($ext ? ".{$ext}" : '');

        return Storage::download($lesson->attachment_path, $name);
    }

    /**
     * Unified shape for lesson JSON responses, including attachment flags/URL.
     */
    private function serializeLesson(Lesson $lesson): array
    {
        return [
            'id'              => $lesson->id,
            'course_id'       => $lesson->course_id,
            'order'           => $lesson->order,
            'title'           => $lesson->title,
            'content'         => $lesson->content,
            'has_attachment'  => !empty($lesson->attachment_path),
            // Protected API download endpoint; make sure route exists (see below)
            'attachment_url'  => $lesson->attachment_path
                ? route('api.lessons.attachment', $lesson)
                : null,
            'created_at'      => $lesson->created_at,
        ];
    }
}

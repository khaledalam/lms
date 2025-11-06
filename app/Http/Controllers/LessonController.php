<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\StoreLessonRequest;

class LessonController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('lessons.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validated();

        $maxOrder = (int) $course->lessons()->max('order');

        $lesson = $course->lessons()->create([
            'title'   => $validated['title'],
            'content' => $validated['content'] ?? null,
            'order'   => $maxOrder + 1,
        ]);

        if ($request->hasFile('attachment')) {
            $lesson->attachment_path = Storage::putFile('lesson-attachments', $request->file('attachment'));
            $lesson->save();
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Lesson created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $this->authorize('view', $lesson);

        $lesson->load([
            'course:id,title,instructor_id,published',
            'comments' => fn($q) => $q->select(['id', 'lesson_id', 'user_id', 'body', 'created_at'])
                ->latest(),
            'comments.user:id,name',
        ]);

        $lesson->setRelation('course', $lesson->course->only(['id', 'title', 'instructor_id', 'published']));

        $course = $lesson->course;
        $comments = $lesson->comments;
        $lesson = $lesson->only(['id', 'course_id', 'title', 'content', 'attachment_path']);

        return view('lessons.show', compact('lesson',  'course', 'comments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $lesson->load('course');
        $course = $lesson->course;

        return view('lessons.edit', compact('course', 'lesson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreLessonRequest $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $validated = $request->validated();

        if (isset($validated['title'])) {
            $lesson->title = $validated['title'];
        }

        if (isset($validated['content'])) {
            $lesson->content = $validated['content'];
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

        return redirect()->route('courses.show', $lesson->course)->with('success', 'Lesson updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('delete', $lesson);

        $course = $lesson->course;
        $lesson->delete();

        return redirect()->route('courses.show', $course)->with('success', 'Lesson deleted.');
    }

    /**
     * Move the lesson up in order.
     */
    public function moveUp(Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $prev = $lesson->course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        if ($prev) {
            [$lesson->order, $prev->order] = [$prev->order, $lesson->order];
            $lesson->save();
            $prev->save();
        }

        return back();
    }

    /**
     * Move the lesson down in order.
     */
    public function moveDown(Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $next = $lesson->course->lessons()->where('order', '>', $lesson->order)->orderBy('order')->first();
        if ($next) {
            [$lesson->order, $next->order] = [$next->order, $lesson->order];
            $lesson->save();
            $next->save();
        }

        return back();
    }

    public function attachment(Lesson $lesson)
    {
        $this->authorize('view', $lesson);

        if (!$lesson->attachment_path) abort(404);

        $filename = Str::slug($lesson->title) . '.' .
            pathinfo($lesson->attachment_path, PATHINFO_EXTENSION);

        return Storage::download($lesson->attachment_path, $filename);
    }
}
